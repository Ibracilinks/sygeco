<?php

namespace Database\Seeders;

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Importeur générique du PTA au format natif (tableau Objectif → Résultat → Extrant → Activités),
 * CSV virgule / point-virgule / tabulation, avec réparation du mojibake et mapping
 * de la colonne RESPONSABLES vers un département.
 *
 * Les classes filles fournissent l'année et le fichier source.
 */
abstract class PtaImportSeeder extends Seeder
{
    abstract protected function annee(): int;

    abstract protected function fichier(): string;

    public function run(): void
    {
        $annee = $this->annee();
        $path = database_path('data/'.$this->fichier());

        if (! is_file($path)) {
            $this->command->warn("Fichier introuvable : {$path}. Déposez le PTA {$annee} puis relancez.");

            return;
        }

        $exercice = Exercice::firstOrCreate(
            ['annee' => $annee],
            [
                'date_debut' => sprintf('%d-01-01', $annee),
                'date_fin' => sprintf('%d-12-31', $annee),
                'statut' => 'brouillon',
            ]
        );

        $user = User::query()->orderBy('id')->first();
        if (! $user) {
            $this->command->error('Aucun utilisateur en base pour renseigner « saisi_par ».');

            return;
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) file_get_contents($path));
        $delimiter = $this->detectDelimiter($lines);

        $objectif = null;
        $resultat = null;
        $extrant = null;
        $nbActivites = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $cells = array_map(
                fn ($c) => trim($this->fixEncoding((string) ($c ?? ''))),
                str_getcsv($line, $delimiter, '"', '\\')
            );
            $first = $cells[0] ?? '';
            $nom = $cells[1] ?? '';
            $joined = trim(implode(' ', $cells));

            // Bannière objectif
            if (Str::startsWith($joined, 'Objectif stratégique')) {
                $description = trim(Str::after($joined, ':'));
                $objectif = $this->objectifGlobal($exercice, $description);
                $resultat = null;
                $extrant = null;

                continue;
            }

            // Ligne Résultat stratégique : un libellé « RS. I … / RS. II … »
            if (preg_match('/^RS\.?\s*(IV|III|II|I|V)\b\s*(.*)$/iu', $this->resultatCell($cells), $m)) {
                if (! $objectif) {
                    $objectif = $this->fallbackObjectif($exercice);
                }
                $roman = strtoupper($m[1]);
                $resultat = Resultat::updateOrCreate(
                    ['objectif_id' => $objectif->id, 'code' => 'RS.'.$roman],
                    [
                        'libelle' => trim($m[2]) !== '' ? trim($m[2]) : 'RS. '.$roman,
                        'ordre' => $this->romanToInt($roman),
                        'is_active' => true,
                    ]
                );
                $extrant = null;

                continue;
            }

            // Ligne Extrant
            if (($extrantCell = $this->extrantCell($cells)) !== null
                && preg_match('/^(Extrant\s+\d+(?:\.\d+)?)\s*(.*)$/u', $extrantCell, $m)) {
                if (! $resultat) {
                    continue;
                }
                $extrant = Extrant::updateOrCreate(
                    ['resultat_id' => $resultat->id, 'code' => $m[1]],
                    [
                        'objectif_id' => $resultat->objectif_id,
                        'libelle' => trim($m[2]) !== '' ? trim($m[2]) : $m[1],
                        'ordre' => (int) filter_var($m[1], FILTER_SANITIZE_NUMBER_INT),
                        'is_active' => true,
                    ]
                );

                continue;
            }

            // Ligne Activité : numéro en 1re cellule, OU activité sans numéro mais avec un coût,
            // sous un extrant courant. On exclut les en-têtes et lignes de totaux.
            $cout = $this->parseCout($cells[9] ?? '');
            $estTotal = (bool) preg_match('/^\s*(SOUS\s*)?TOTAL|TOTAUX/i', $first);

            if ($extrant && $nom !== '' && $nom !== 'ACTIVITES' && ! $estTotal
                && (preg_match('/^\d+$/', $first) || ($first === '' && $cout > 0))) {
                $departements = $this->departementsPour($cells[8] ?? '');

                $activite = Activite::firstOrCreate(
                    [
                        'extrant_id' => $extrant->id,
                        'nom_activite' => $nom,
                    ],
                    [
                        'exercice_id' => $exercice->id,
                        'departement_id' => $departements->first()?->id,
                        'indicateur_objectivement_verifiable' => $cells[2] ?? '',
                        'moyen_verification' => $cells[3] ?? '',
                        'cout' => $cout,
                        'trimestre_1' => $this->coche($cells[4] ?? ''),
                        'trimestre_2' => $this->coche($cells[5] ?? ''),
                        'trimestre_3' => $this->coche($cells[6] ?? ''),
                        'trimestre_4' => $this->coche($cells[7] ?? ''),
                        'statut' => 'brouillon',
                        'saisi_par' => $user->id,
                        'date_saisie' => $exercice->date_debut,
                    ]
                );

                // Rattacher tous les départements responsables (many-to-many).
                $activite->departements()->sync($departements->pluck('id')->all());

                $nbActivites++;
            }

            // Tout le reste (en-têtes, TOTAL, SOUS TOTAL…) est ignoré.
        }

        // Dériver les départements des niveaux supérieurs (extrant → résultat → objectif).
        $this->deriverDepartements($exercice);

        $this->command->info("✅ PTA {$annee} importé : {$nbActivites} activité(s) traitée(s).");
    }

    /**
     * Répare le double-encodage UTF-8 (mojibake : « cÃ©rÃ©monies » → « cérémonies »).
     */
    protected function fixEncoding(string $s): string
    {
        if ($s === '' || ! preg_match('/Ã|Â|Å|â€|â‚/u', $s)) {
            return $s;
        }

        $converted = @mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
        if ($converted === false || $converted === '') {
            return $s;
        }

        // Réparer les octets isolés (continuation perdue au copier-coller : nbsp→espace, â€™→â, etc.).
        $converted = preg_replace('/\xC3\x20/', "\xC3\xA0", $converted);          // « Ã » + espace → « à »
        $converted = preg_replace('/\xC3(?![\x80-\xBF])/', "\xC3\xA0", $converted); // « Ã » isolé → « à »
        $converted = preg_replace('/\xC5(?![\x80-\xBF])/', "\xC5\x93", $converted); // « Å » isolé → « œ »
        $converted = preg_replace('/\xE2(?![\x80-\xBF])/', "'", $converted);        // « â » isolé (’) → apostrophe
        $converted = preg_replace('/\xC2(?![\x80-\xBF])/', '', $converted);         // « Â » parasite → supprimé

        if (! mb_check_encoding($converted, 'UTF-8')) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $converted) ?: $s;
        }

        return $converted;
    }

    protected function detectDelimiter(array $lines): string
    {
        // Choisit le séparateur le plus fréquent (un « ; » isolé dans un champ
        // ne doit pas l'emporter sur des milliers de virgules).
        $counts = ["\t" => 0, ';' => 0, ',' => 0];
        foreach ($lines as $line) {
            $counts["\t"] += substr_count($line, "\t");
            $counts[';'] += substr_count($line, ';');
            $counts[','] += substr_count($line, ',');
        }
        arsort($counts);

        return array_key_first($counts) ?: ',';
    }

    protected function resultatCell(array $cells): string
    {
        foreach ($cells as $c) {
            if (preg_match('/^RS\.?\s*(IV|III|II|I|V)\b/iu', $c)) {
                return $c;
            }
        }

        return '';
    }

    protected function extrantCell(array $cells): ?string
    {
        foreach ($cells as $c) {
            if (Str::startsWith($c, 'Extrant ')) {
                return $c;
            }
        }

        return null;
    }

    /**
     * Découpe la colonne RESPONSABLES (« AC/DSI/DAGRH/ DBCGOQ ») en départements atomiques.
     *
     * @return Collection<int, Departement>
     */
    protected function departementsPour(string $responsables): Collection
    {
        $noms = collect(preg_split('#/#u', $responsables))
            ->map(fn ($c) => trim(preg_replace('/\s+/u', ' ', (string) $c)))
            ->filter()
            ->unique()
            ->values();

        if ($noms->isEmpty()) {
            $noms = collect(['Non défini']);
        }

        return $noms->map(fn ($nom) => $this->departementAtomique($nom));
    }

    protected function departementAtomique(string $nom): Departement
    {
        $code = mb_strlen($nom) <= 20 ? $nom : 'D'.strtoupper(substr(md5($nom), 0, 8));

        return Departement::firstOrCreate(
            ['nom' => $nom],
            ['code' => $code, 'is_active' => true]
        );
    }

    /**
     * Dérive les départements des extrants/résultats/objectifs depuis les activités.
     */
    protected function deriverDepartements(Exercice $exercice): void
    {
        $objectifs = Objectif::forExercice($exercice->id)
            ->with('resultats.extrants.activites.departements')
            ->get();

        foreach ($objectifs as $objectif) {
            $depsObjectif = collect();

            foreach ($objectif->resultats as $resultat) {
                $depsResultat = collect();

                foreach ($resultat->extrants as $extrant) {
                    $depsExtrant = $extrant->activites
                        ->flatMap(fn ($a) => $a->departements->pluck('id'))
                        ->unique()
                        ->values();

                    $extrant->departements()->sync($depsExtrant->all());
                    $depsResultat = $depsResultat->merge($depsExtrant);
                }

                $depsResultat = $depsResultat->unique()->values();
                $resultat->departements()->sync($depsResultat->all());
                $depsObjectif = $depsObjectif->merge($depsResultat);
            }

            $objectif->departements()->sync($depsObjectif->unique()->values()->all());
        }
    }

    protected function parseCout(string $raw): float
    {
        $clean = preg_replace('/[^\d]/u', '', $raw);

        return $clean === '' ? 0.0 : (float) $clean;
    }

    protected function coche(string $raw): string
    {
        return strtoupper(trim($raw)) === 'X' ? 'oui' : 'non';
    }

    protected function romanToInt(string $roman): int
    {
        return ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5][$roman] ?? 0;
    }

    protected function fallbackObjectif(Exercice $exercice): Objectif
    {
        return $this->objectifGlobal($exercice);
    }

    /**
     * Objectif global de l'exercice. Le rattachement passe par le pivot
     * `exercice_objectif` : un objectif peut couvrir plusieurs exercices, on ne
     * peut donc plus l'identifier par une colonne `exercice_id`.
     */
    protected function objectifGlobal(Exercice $exercice, ?string $description = null): Objectif
    {
        $objectif = Objectif::forExercice($exercice->id)->where('code', 'OG')->first();

        $attributs = [
            'libelle' => 'Objectif global',
            'annee' => $exercice->annee,
            'statut' => 'actif',
            'ordre' => 1,
        ];

        if ($description !== null) {
            $attributs['description'] = $description;
        }

        if ($objectif) {
            $objectif->update($attributs);

            return $objectif;
        }

        $objectif = Objectif::create($attributs + ['code' => 'OG']);
        $objectif->exercices()->syncWithoutDetaching([$exercice->id]);

        return $objectif;
    }
}
