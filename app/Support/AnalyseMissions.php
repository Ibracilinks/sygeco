<?php

namespace App\Support;

use App\Models\Mission;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Statistiques des missions d'une période et détection des doublons potentiels.
 *
 * Trois signaux, du plus fort au plus faible :
 *  - même personne dans deux missions dont les périodes se chevauchent (elle ne
 *    peut pas être à deux endroits à la fois) ;
 *  - même objet, même type et même date de départ (saisie en double) ;
 *  - même type, périodes qui se chevauchent et participants en commun ou objets
 *    très proches (mission probablement dupliquée).
 */
class AnalyseMissions
{
    public const NIVEAU_FORT = 'fort';

    public const NIVEAU_MOYEN = 'moyen';

    /** Ressemblance minimale (en %) entre deux objets pour les dire « très proches ». */
    private const SEUIL_OBJETS_PROCHES = 85;

    /**
     * @param  Collection<int, Mission>  $missions  avec participants et departements chargés
     */
    public function __construct(private readonly Collection $missions) {}

    /**
     * @return array<string, mixed>
     */
    public function statistiques(): array
    {
        $missions = $this->missions;

        $parType = collect(Mission::TYPES)->map(fn ($libelle, $type) => [
            'libelle' => $libelle,
            'nombre' => $missions->where('type', $type)->count(),
            'budget' => (float) $missions->where('type', $type)->sum('montant_total'),
        ]);

        $parStructure = $missions
            ->flatMap(fn (Mission $mission) => ($mission->departements->isNotEmpty() ? $mission->departements : collect([$mission->departement])->filter())
                ->map(fn ($structure) => ['structure' => $structure->code ?: $structure->nom, 'mission' => $mission]))
            ->groupBy('structure')
            ->map(fn ($lignes, $structure) => [
                'structure' => $structure,
                'nombre' => $lignes->count(),
                'budget' => (float) $lignes->sum(fn ($ligne) => $ligne['mission']->montant_total),
            ])
            ->sortByDesc('budget')
            ->values();

        $parMois = collect(range(1, 12))->mapWithKeys(fn ($mois) => [
            $mois => $missions->filter(fn (Mission $mission) => (int) $mission->date_depart?->month === $mois)->count(),
        ]);

        $interieur = $missions->where('type', Mission::TYPE_REGION);

        // Destinations à l'intérieur : les localités des étapes (pas les régions
        // saisies pour les statistiques), avec le nombre de jours passés sur place.
        $destinationsInterieur = $interieur
            ->flatMap(fn (Mission $mission) => $mission->etapes->map(fn ($etape) => [
                'localite' => trim((string) $etape->localite),
                'type' => Mission::TYPES_ETAPES_REGIONALES[$etape->type_etape] ?? (string) $etape->type_etape,
                'jours' => (int) $etape->nombre_jours,
                'mission' => $mission->id,
            ]))
            ->filter(fn ($etape) => $etape['localite'] !== '')
            ->groupBy(fn ($etape) => mb_strtoupper($etape['localite']))
            ->map(fn ($etapes) => [
                'localite' => $etapes->first()['localite'],
                'type' => $etapes->first()['type'],
                'missions' => $etapes->pluck('mission')->unique()->count(),
                'jours' => $etapes->sum('jours'),
            ])
            ->sortByDesc('missions')
            ->values();

        $destinationsEtranger = $missions->where('type', Mission::TYPE_EXTERIEURE)
            ->groupBy(fn (Mission $mission) => trim((string) $mission->destination) ?: 'Non précisée')
            ->map(fn ($groupe, $destination) => [
                'destination' => $destination,
                'zone' => (string) $groupe->first()->zone_label,
                'missions' => $groupe->count(),
                'budget' => (float) $groupe->sum('montant_total'),
            ])
            ->sortByDesc('budget')
            ->values();

        $somme = fn (string $colonne) => (float) $missions->sum(fn (Mission $mission) => (float) $mission->{$colonne});

        // Dépenses par poste budgétaire (les sous-totaux « autres frais » ne sont pas
        // repris : ils regroupent des postes déjà listés).
        $depenses = collect([
            'Frais de mission et indemnités' => $somme('montant_indemnites'),
            'Majoration (étranger)' => $somme('montant_majoration'),
            'Carburant' => $somme('montant_carburant'),
            'Location de véhicules' => $somme('montant_location_vehicule'),
            "Billets d'avion" => $somme('montant_billets'),
            'Péages' => $somme('montant_peages'),
            'Frais de participation' => $somme('frais_participation_total'),
            'Frais de visa' => $somme('frais_visa_total'),
        ])->filter(fn ($montant) => $montant > 0)->sortDesc();

        $joursPersonnes = $missions->sum(fn (Mission $mission) => (int) $mission->nombre_jours * max(1, $mission->participants->count()));
        $budget = (float) $missions->sum('montant_total');

        return [
            'nombre' => $missions->count(),
            // Engagé = missions finalisées ; les brouillons ne sont encore que prévus.
            'budget_engage' => (float) $missions->where('statut', 'finalise')->sum('montant_total'),
            'budget_prevu' => (float) $missions->where('statut', 'brouillon')->sum('montant_total'),
            'cout_moyen_mission' => $missions->isNotEmpty() ? $budget / $missions->count() : 0.0,
            'cout_jour_personne' => $joursPersonnes > 0 ? $budget / $joursPersonnes : 0.0,
            'tickets_carburant' => (int) $missions->where('type', Mission::TYPE_MEME_VILLE)->sum('nombre_tickets_carburant'),
            'carburant' => [
                'litres_trajet' => $interieur->sum(fn (Mission $mission) => $mission->litresTrajet()),
                'montant_trajet' => (float) $interieur->sum(fn (Mission $mission) => (float) $mission->montant_carburant_trajet),
                'litres_ville' => $interieur->sum(fn (Mission $mission) => $mission->litresVille()),
                'montant_ville' => (float) $interieur->sum(fn (Mission $mission) => (float) $mission->montant_carburant_ville),
                'distance' => (float) $interieur->sum(fn (Mission $mission) => (float) $mission->distance_totale_km),
            ],
            'depenses' => $depenses,
            'destinations_interieur' => $destinationsInterieur,
            'destinations_etranger' => $destinationsEtranger,
            'budget' => (float) $missions->sum('montant_total'),
            'participants' => $missions->sum(fn (Mission $mission) => $mission->participants->count()),
            'jours_personnes' => $joursPersonnes,
            'finalisees' => $missions->where('statut', 'finalise')->count(),
            'brouillons' => $missions->where('statut', 'brouillon')->count(),
            'par_type' => $parType,
            'par_structure' => $parStructure,
            'par_mois' => $parMois,
        ];
    }

    /**
     * Doublons potentiels, du plus fort au plus faible.
     *
     * @return Collection<int, array{niveau: string, motif: string, detail: string, missions: array<int, Mission>}>
     */
    public function doublons(): Collection
    {
        $alertes = collect();
        $liste = $this->missions->values();

        for ($i = 0; $i < $liste->count(); $i++) {
            for ($j = $i + 1; $j < $liste->count(); $j++) {
                $a = $liste[$i];
                $b = $liste[$j];

                if ($alerte = $this->comparer($a, $b)) {
                    $alertes->push($alerte + ['missions' => [$a, $b]]);
                }
            }
        }

        return $alertes
            ->sortBy(fn ($alerte) => $alerte['niveau'] === self::NIVEAU_FORT ? 0 : 1)
            ->values();
    }

    /**
     * @return array{niveau: string, motif: string, detail: string}|null
     */
    private function comparer(Mission $a, Mission $b): ?array
    {
        $chevauchement = $this->periodesSeChevauchent($a, $b);
        $communs = $chevauchement ? $this->participantsCommuns($a, $b) : collect();

        if ($communs->isNotEmpty()) {
            return [
                'niveau' => self::NIVEAU_FORT,
                'motif' => 'Même personne sur deux missions simultanées',
                'detail' => $communs->join(', '),
            ];
        }

        $memeObjet = $this->normaliser($a->objet) !== '' && $this->normaliser($a->objet) === $this->normaliser($b->objet);

        if ($memeObjet && $a->type === $b->type && $a->date_depart?->isSameDay($b->date_depart)) {
            return [
                'niveau' => self::NIVEAU_FORT,
                'motif' => 'Même objet, même type, même date de départ',
                'detail' => (string) $a->objet,
            ];
        }

        if ($chevauchement && $a->type === $b->type && $this->objetsProches($a, $b)) {
            return [
                'niveau' => self::NIVEAU_MOYEN,
                'motif' => 'Objets très proches sur des périodes qui se chevauchent',
                'detail' => $a->objet.' / '.$b->objet,
            ];
        }

        return null;
    }

    private function periodesSeChevauchent(Mission $a, Mission $b): bool
    {
        if (! $a->date_depart || ! $b->date_depart) {
            return false;
        }

        $finA = $a->date_retour ?? $a->date_depart;
        $finB = $b->date_retour ?? $b->date_depart;

        return $a->date_depart->startOfDay() <= $finB->startOfDay()
            && $b->date_depart->startOfDay() <= $finA->startOfDay();
    }

    /**
     * @return Collection<int, string>
     */
    private function participantsCommuns(Mission $a, Mission $b): Collection
    {
        $nomsB = $b->participants->map(fn ($p) => $this->normaliser($p->nom_complet))->filter()->all();

        return $a->participants
            ->filter(fn ($p) => in_array($this->normaliser($p->nom_complet), $nomsB, true) && $this->normaliser($p->nom_complet) !== '')
            ->pluck('nom_complet')
            ->unique()
            ->values();
    }

    private function objetsProches(Mission $a, Mission $b): bool
    {
        $objetA = $this->normaliser($a->objet);
        $objetB = $this->normaliser($b->objet);

        if ($objetA === '' || $objetB === '') {
            return false;
        }

        similar_text($objetA, $objetB, $pourcentage);

        return $pourcentage >= self::SEUIL_OBJETS_PROCHES;
    }

    /** Majuscules, sans accents ni espaces superflus : « Moussa  Traoré » = « MOUSSA TRAORE ». */
    private function normaliser(?string $texte): string
    {
        return (string) Str::of((string) $texte)->ascii()->upper()->squish();
    }
}
