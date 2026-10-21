<?php

namespace App\Support;

use App\Models\Activite;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Indicateurs de la programmation d'un exercice et doublons potentiels.
 *
 * Doublons, du plus fort au plus faible :
 *  - même intitulé une fois normalisé (accents, casse, ponctuation, espaces),
 *    dans tout l'exercice : la même activité saisie deux fois, souvent par deux
 *    entités différentes ;
 *  - intitulés très proches sous le même extrant (mots en commun) : reformulation
 *    probable de la même activité.
 * La comparaison approchée reste limitée à un même extrant : comparer toutes les
 * activités deux à deux serait trop lent sur un exercice complet.
 */
class AnalyseActivites
{
    public const NIVEAU_FORT = 'fort';

    public const NIVEAU_MOYEN = 'moyen';

    /** Part minimale de mots communs (indice de Jaccard) pour deux intitulés « très proches ». */
    private const SEUIL_INTITULES_PROCHES = 0.7;

    /** Mots trop courants pour distinguer deux activités. */
    private const MOTS_VIDES = ['DE', 'DU', 'DES', 'LA', 'LE', 'LES', 'ET', 'A', 'AU', 'AUX', 'EN', 'POUR', 'SUR', 'PAR', 'D', 'L', 'UN', 'UNE', 'DANS'];

    /**
     * @param  Collection<int, Activite>  $activites  avec extrant et departement chargés
     */
    public function __construct(private readonly Collection $activites) {}

    /**
     * @return array<string, mixed>
     */
    public function indicateurs(): array
    {
        $activites = $this->activites;
        $budget = fn (Collection $lot) => (float) $lot->sum(fn (Activite $a) => $a->pour_memoire ? 0 : (float) $a->cout);

        $soumises = $activites->where('statut', '!=', 'brouillon');
        $validees = $activites->where('statut', 'valide');

        $parEntite = $activites
            ->groupBy(fn (Activite $a) => $a->departement?->code ?: ($a->departement?->nom ?? 'Non affectée'))
            ->map(fn (Collection $lot, $entite) => [
                'entite' => $entite,
                'nombre' => $lot->count(),
                'budget' => $budget($lot),
                'brouillons' => $lot->where('statut', 'brouillon')->count(),
                'en_attente' => $lot->where('statut', 'en_attente')->count(),
                'validees' => $lot->where('statut', 'valide')->count(),
            ])
            ->sortByDesc('budget')
            ->values();

        return [
            'nombre' => $activites->count(),
            'par_statut' => collect(['brouillon', 'rejete', 'en_attente', 'valide'])
                ->mapWithKeys(fn ($statut) => [$statut => $activites->where('statut', $statut)->count()]),
            'budget_programme' => $budget($activites),
            'budget_valide' => $budget($validees),
            // Part des activités soumises qui ont été validées.
            'taux_validation' => $soumises->isNotEmpty() ? round($validees->count() / $soumises->count() * 100, 1) : 0.0,
            'cout_moyen' => $activites->isNotEmpty() ? $budget($activites) / max(1, $activites->where('pour_memoire', false)->count()) : 0.0,
            'pour_memoire' => $activites->where('pour_memoire', true)->count(),
            'non_programmees' => $activites->where('non_programmee', true)->count(),
            'sans_extrant' => $activites->whereNull('extrant_id')->count(),
            'entites' => $parEntite->count(),
            'par_entite' => $parEntite,
            'par_trimestre' => collect([1, 2, 3, 4])->mapWithKeys(fn ($t) => [
                $t => $activites->filter(fn (Activite $a) => $a->{'trimestre_'.$t} === 'oui')->count(),
            ]),
        ];
    }

    /**
     * @return Collection<int, array{niveau: string, motif: string, activites: array<int, Activite>}>
     */
    public function doublons(): Collection
    {
        $alertes = collect();

        // 1. Même intitulé normalisé, dans tout l'exercice.
        $this->activites
            ->groupBy(fn (Activite $a) => $this->normaliser($a->nom_activite))
            ->filter(fn (Collection $lot, $cle) => $cle !== '' && $lot->count() > 1)
            ->each(fn (Collection $lot) => $alertes->push([
                'niveau' => self::NIVEAU_FORT,
                'motif' => $lot->pluck('departement_id')->unique()->count() > 1
                    ? 'Même intitulé dans plusieurs entités'
                    : 'Même intitulé saisi plusieurs fois',
                'activites' => $lot->values()->all(),
            ]));

        $dejaSignalees = $alertes->flatMap(fn ($alerte) => collect($alerte['activites'])->pluck('id'))->flip();

        // 2. Intitulés très proches sous un même extrant.
        $this->activites
            ->whereNotNull('extrant_id')
            ->groupBy('extrant_id')
            ->each(function (Collection $lot) use ($alertes, $dejaSignalees) {
                $liste = $lot->values();

                for ($i = 0; $i < $liste->count(); $i++) {
                    for ($j = $i + 1; $j < $liste->count(); $j++) {
                        [$a, $b] = [$liste[$i], $liste[$j]];

                        if (isset($dejaSignalees[$a->id], $dejaSignalees[$b->id])) {
                            continue;
                        }

                        if ($this->ressemblance($a->nom_activite, $b->nom_activite) >= self::SEUIL_INTITULES_PROCHES) {
                            $alertes->push([
                                'niveau' => self::NIVEAU_MOYEN,
                                'motif' => 'Intitulés très proches sous le même extrant',
                                'activites' => [$a, $b],
                            ]);
                        }
                    }
                }
            });

        return $alertes->sortBy(fn ($alerte) => $alerte['niveau'] === self::NIVEAU_FORT ? 0 : 1)->values();
    }

    /** Part de mots significatifs communs aux deux intitulés (0 à 1). */
    private function ressemblance(?string $a, ?string $b): float
    {
        $motsA = $this->mots($a);
        $motsB = $this->mots($b);

        if ($motsA->isEmpty() || $motsB->isEmpty()) {
            return 0.0;
        }

        return $motsA->intersect($motsB)->count() / $motsA->merge($motsB)->unique()->count();
    }

    /**
     * @return Collection<int, string>
     */
    private function mots(?string $texte): Collection
    {
        return collect(explode(' ', $this->normaliser($texte)))
            ->reject(fn ($mot) => $mot === '' || in_array($mot, self::MOTS_VIDES, true))
            ->unique()
            ->values();
    }

    /** Majuscules, sans accents, ponctuation ni espaces superflus. */
    private function normaliser(?string $texte): string
    {
        return (string) Str::of((string) $texte)->ascii()->upper()->replaceMatches('/[^A-Z0-9]+/', ' ')->squish();
    }
}
