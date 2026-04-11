<?php

namespace Database\Seeders;

use App\Models\Indicateur;
use App\Models\Objectif;
use Illuminate\Database\Seeder;

class IndicateurSeeder extends Seeder
{
    public function run(): void
    {
        $objectifs = Objectif::all();

        $indicateursData = [
            [
                'code' => 'IND_PERF_01',
                'libelle' => 'Taux de réalisation des activités',
                'description' => 'Pourcentage des activités réalisées par rapport au prévu',
                'type' => 'performance',
                'unite' => '%',
                'cible' => 95,
                'seuil_alerte' => 70,
                'periodicite' => 'trimestriel',
                'sens' => 'hausse',
                'ordre' => 1,
            ],
            [
                'code' => 'IND_GEST_01',
                'libelle' => 'Taux d\'exécution budgétaire',
                'description' => 'Pourcentage du budget consommé par rapport au budget prévu',
                'type' => 'gestion',
                'unite' => '%',
                'cible' => 90,
                'seuil_alerte' => 60,
                'periodicite' => 'trimestriel',
                'sens' => 'hausse',
                'ordre' => 2,
            ],
            [
                'code' => 'IND_QUAL_01',
                'libelle' => 'Taux de satisfaction des parties prenantes',
                'description' => 'Niveau de satisfaction des utilisateurs et partenaires',
                'type' => 'qualite',
                'unite' => '%',
                'cible' => 85,
                'seuil_alerte' => 60,
                'periodicite' => 'semestriel',
                'sens' => 'hausse',
                'ordre' => 3,
            ],
            [
                'code' => 'IND_EFF_01',
                'libelle' => 'Délai moyen de traitement',
                'description' => 'Délai moyen entre la saisie et la validation',
                'type' => 'efficacite',
                'unite' => 'Jours',
                'cible' => 5,
                'seuil_alerte' => 10,
                'periodicite' => 'mensuel',
                'sens' => 'baisse',
                'ordre' => 4,
            ],
        ];

        foreach ($objectifs as $objectif) {
            foreach ($indicateursData as $indicateur) {
                Indicateur::create(array_merge($indicateur, ['objectif_id' => $objectif->id]));
            }
        }
    }
}
