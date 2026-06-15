<?php

namespace Database\Seeders;

use App\Models\Extrant;
use App\Models\Resultat;
use Illuminate\Database\Seeder;

class ExtrantSeeder extends Seeder
{
    public function run(): void
    {
        $resultats = Resultat::whereIn('code', ['RS.I', 'RS.II', 'RS.III', 'RS.IV'])
            ->get()
            ->keyBy('code');

        if ($resultats->isEmpty()) {
            $this->command->error("Aucun résultat trouvé. Veuillez d'abord exécuter ResultatSeeder.");

            return;
        }

        // Extrants rattachés à leur résultat stratégique.
        $extrants = [
            ['code' => 'Extrant 1.1', 'resultat' => 'RS.I', 'libelle' => "Le régime de l'assurance maladie universelle (RAMU) est opérationnel"],
            ['code' => 'Extrant 2.1', 'resultat' => 'RS.II', 'libelle' => "Les allocations judicieuses de ressources financières ont permis d'améliorer la qualité des services offerts par la CANAM"],
            ['code' => 'Extrant 3.1', 'resultat' => 'RS.III', 'libelle' => 'Des prestations de soins de qualité sont fournies aux assurés'],
            ['code' => 'Extrant 3.2', 'resultat' => 'RS.III', 'libelle' => 'La gestion de la CANAM est améliorée'],
            ['code' => 'Extrant 3.3', 'resultat' => 'RS.III', 'libelle' => "La gestion de l'assurance maladie est améliorée"],
            ['code' => 'Extrant 3.4', 'resultat' => 'RS.III', 'libelle' => "La communication sur l'assurance maladie est renforcée"],
            ['code' => 'Extrant 4.1', 'resultat' => 'RS.IV', 'libelle' => 'Les capacités du personnel et des Administrateurs de la CANAM sont renforcées'],
            ['code' => 'Extrant 4.2', 'resultat' => 'RS.IV', 'libelle' => 'Les conditions de travail sont améliorées'],
        ];

        $compteur = 0;
        $ordreParResultat = [];

        foreach ($extrants as $data) {
            $resultat = $resultats->get($data['resultat']);

            if (! $resultat) {
                $this->command->warn("Résultat {$data['resultat']} introuvable pour {$data['code']}, ignoré.");

                continue;
            }

            $ordreParResultat[$resultat->code] = ($ordreParResultat[$resultat->code] ?? 0) + 1;

            Extrant::updateOrCreate(
                ['resultat_id' => $resultat->id, 'code' => $data['code']],
                [
                    'objectif_id' => $resultat->objectif_id,
                    'libelle' => $data['libelle'],
                    'ordre' => $ordreParResultat[$resultat->code],
                    'is_active' => true,
                ]
            );

            $compteur++;
        }

        $this->command->info("✅ {$compteur} extrants créés pour " . $resultats->count() . ' résultats');
    }
}
