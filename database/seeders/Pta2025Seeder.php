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
use Illuminate\Support\Str;

class Pta2025Seeder extends Seeder
{
    /**
     * Importe le PTA 2025 depuis un fichier au format natif (tableau PTA),
     * tab/point-virgule séparé, déposé dans database/data/pta_2025.csv.
     *
     * Hiérarchie reconstruite : Objectif → Résultat (RS.x) → Extrant → Activités.
     * La colonne RESPONSABLES est mappée vers un département (créé au besoin).
     */
    public function run(): void
    {
        $path = database_path('data/pta_2025.csv');

        if (! is_file($path)) {
            $this->command->warn("Fichier introuvable : {$path}. Déposez le PTA 2025 (CSV tab-séparé) puis relancez.");

            return;
        }

        $exercice = Exercice::firstOrCreate(
            ['annee' => 2025],
            [
                'date_debut' => '2025-01-01',
                'date_fin' => '2025-12-31',
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
            $joined = trim(implode(' ', $cells));

            // Bannière objectif
            if (Str::startsWith($joined, 'Objectif stratégique')) {
                $description = trim(Str::after($joined, ':'));
                $objectif = Objectif::updateOrCreate(
                    ['exercice_id' => $exercice->id, 'code' => 'OG'],
                    [
                        'libelle' => 'Objectif global',
                        'description' => $description,
                        'annee' => $exercice->annee,
                        'statut' => 'actif',
                        'ordre' => 1,
                    ]
                );
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
                    ['objectif_id' => $objectif->id, 'code' => 'RS.' . $roman],
                    [
                        'libelle' => trim($m[2]) !== '' ? trim($m[2]) : 'RS. ' . $roman,
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

            // Ligne Activité : première cellule = numéro, et un extrant courant existe
            if ($extrant && preg_match('/^\d+$/', $first) && ($cells[1] ?? '') !== '') {
                $departement = $this->departementPour($cells[8] ?? '');

                Activite::firstOrCreate(
                    [
                        'extrant_id' => $extrant->id,
                        'nom_activite' => $cells[1],
                    ],
                    [
                        'departement_id' => $departement->id,
                        'indicateur_objectivement_verifiable' => $cells[2] ?? '',
                        'moyen_verification' => $cells[3] ?? '',
                        'cout' => $this->parseCout($cells[9] ?? ''),
                        'trimestre_1' => $this->coche($cells[4] ?? ''),
                        'trimestre_2' => $this->coche($cells[5] ?? ''),
                        'trimestre_3' => $this->coche($cells[6] ?? ''),
                        'trimestre_4' => $this->coche($cells[7] ?? ''),
                        'statut' => 'brouillon',
                        'saisi_par' => $user->id,
                        'date_saisie' => $exercice->date_debut,
                    ]
                );
                $nbActivites++;
            }

            // Tout le reste (en-têtes, TOTAL, SOUS TOTAL…) est ignoré.
        }

        $this->command->info("✅ PTA 2025 importé : {$nbActivites} activité(s) traitée(s).");
    }

    /**
     * Répare le double-encodage UTF-8 (mojibake : « cÃ©rÃ©monies » → « cérémonies »).
     * N'agit que si la chaîne porte la signature du mojibake, afin de ne pas
     * altérer un texte déjà correct.
     */
    private function fixEncoding(string $s): string
    {
        if ($s === '' || ! preg_match('/Ã.|Â.|â€|â‚/u', $s)) {
            return $s;
        }

        $converted = @mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');

        return ($converted !== false && $converted !== '') ? $converted : $s;
    }

    private function detectDelimiter(array $lines): string
    {
        foreach ($lines as $line) {
            if (str_contains($line, "\t")) {
                return "\t";
            }
            if (str_contains($line, ';')) {
                return ';';
            }
        }

        return ',';
    }

    private function resultatCell(array $cells): string
    {
        foreach ($cells as $c) {
            if (preg_match('/^RS\.?\s*(IV|III|II|I|V)\b/iu', $c)) {
                return $c;
            }
        }

        return '';
    }

    private function extrantCell(array $cells): ?string
    {
        foreach ($cells as $c) {
            if (Str::startsWith($c, 'Extrant ')) {
                return $c;
            }
        }

        return null;
    }

    private function departementPour(string $responsables): Departement
    {
        $nom = trim(preg_replace('/\s+/u', ' ', $responsables));
        if ($nom === '') {
            $nom = 'Non défini';
        }

        return Departement::firstOrCreate(
            ['nom' => $nom],
            [
                'code' => 'R' . strtoupper(substr(md5($nom), 0, 8)),
                'is_active' => true,
            ]
        );
    }

    private function parseCout(string $raw): float
    {
        $clean = preg_replace('/[^\d]/u', '', $raw);

        return $clean === '' ? 0.0 : (float) $clean;
    }

    private function coche(string $raw): string
    {
        return strtoupper(trim($raw)) === 'X' ? 'oui' : 'non';
    }

    private function romanToInt(string $roman): int
    {
        return ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5][$roman] ?? 0;
    }

    private function fallbackObjectif(Exercice $exercice): Objectif
    {
        return Objectif::firstOrCreate(
            ['exercice_id' => $exercice->id, 'code' => 'OG'],
            [
                'libelle' => 'Objectif global',
                'annee' => $exercice->annee,
                'statut' => 'actif',
                'ordre' => 1,
            ]
        );
    }
}
