<?php

namespace Database\Seeders;

use App\Models\Structure;
use Illuminate\Database\Seeder;

class StructureSeeder extends Seeder
{
    public function run(): void
    {
        // Créer des directions
        $direction = Structure::create([
            'code' => 'DIR_CANAM',
            'libelle' => 'Direction Générale CANAM',
            'type' => 'direction',
            'responsable_nom' => 'Dr. Koné Ibrahim',
            'responsable_email' => 'direction@canam.ci',
            'is_active' => true,
        ]);

        // Créer des départements
        $dbcgoq = Structure::create([
            'code' => 'DBCGOQ',
            'libelle' => 'Direction du Budget, Contrôle de Gestion et Organisation/Qualité',
            'type' => 'departement',
            'parent_id' => $direction->id,
            'responsable_nom' => 'Mme. Diop Fatou',
            'responsable_email' => 'dbcgoq@canam.ci',
            'is_active' => true,
        ]);

        // Créer des bureaux régionaux
        $regions = ['BAMAKO', 'KAYES', 'SIKASSO', 'SEGOU', 'KOUTIALA'];
        foreach ($regions as $region) {
            Structure::create([
                'code' => 'BR_' . $region,
                'libelle' => 'Bureau Régional de ' . $region,
                'type' => 'bureau_regional',
                'parent_id' => $dbcgoq->id,
                'responsable_nom' => $this->getResponsableForRegion($region),
                'responsable_email' => strtolower($region) . '@canam.ci',
                'is_active' => true,
            ]);
        }
    }

    private function getResponsableForRegion($region)
    {
        $responsables = [
            'BAMAKO' => 'M. Traoré Amadou',
            'KAYES' => 'Mme. Diallo Aissata',
            'SIKASSO' => 'M. Sanogo Mamadou',
            'SEGOU' => 'Mme. Coulibaly Fatoumata',
            'KOUTIALA' => 'M. Koné Drissa',
        ];

        return $responsables[$region] ?? 'M. Default';
    }
}   
