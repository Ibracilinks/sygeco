<?php

namespace Database\Seeders;

use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Database\Seeder;

class ResultatSeeder extends Seeder
{
    public function run(): void
    {
        $objectif = Objectif::where('code', 'OG')->first();

        if (! $objectif) {
            $this->command->error("Aucun objectif 'OG' trouvé. Veuillez d'abord exécuter ObjectifSeeder.");

            return;
        }

        $resultats = [
            'RS.I' => 'Des ressources financières plus importantes sont mobilisées et allouées en tenant compte des disparités',
            'RS.II' => 'La gestion financière du secteur est améliorée',
            'RS.III' => 'La couverture des populations par les systèmes de protection sociale a augmenté',
            'RS.IV' => "Les organisations de l'économie sociale et solidaire sont plus performantes",
        ];

        $ordre = 0;

        foreach ($resultats as $code => $libelle) {
            $ordre++;

            Resultat::updateOrCreate(
                ['objectif_id' => $objectif->id, 'code' => $code],
                [
                    'libelle' => $libelle,
                    'ordre' => $ordre,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ ' . count($resultats) . " résultats créés pour l'objectif {$objectif->code}");
    }
}
