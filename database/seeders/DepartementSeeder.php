<?php

namespace Database\Seeders;

use App\Models\Departement;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    /**
     * Construit l'organigramme à trois niveaux :
     * Direction → Départements → Services.
     * Chaque entité porte un `type` et un `parent_id` ; le chef de chaque
     * entité soumet ses éléments pour approbation au chef de l'entité parente.
     */
    public function run(): void
    {
        $organigramme = [
            'DIR_FIN' => [
                'nom' => 'Direction des Finances',
                'departements' => [
                    'DEP_COMPTA' => ['nom' => 'Département Comptabilité', 'services' => [
                        'SRV_COMPTA_GEN' => 'Service Comptabilité Générale',
                        'SRV_COMPTA_ANA' => 'Service Comptabilité Analytique',
                    ]],
                    'DEP_BUDGET' => ['nom' => 'Département Budget', 'services' => [
                        'SRV_BUDGET_PREP' => 'Service Préparation Budgétaire',
                        'SRV_BUDGET_EXEC' => 'Service Exécution Budgétaire',
                    ]],
                ],
            ],
            'DIR_RH' => [
                'nom' => 'Direction des Ressources Humaines',
                'departements' => [
                    'DEP_PAIE' => ['nom' => 'Département Paie', 'services' => [
                        'SRV_PAIE_TRAIT' => 'Service Traitement de la Paie',
                    ]],
                    'DEP_CARRIERE' => ['nom' => 'Département Gestion des Carrières', 'services' => [
                        'SRV_FORMATION' => 'Service Formation',
                        'SRV_RECRUT' => 'Service Recrutement',
                    ]],
                ],
            ],
            'DIR_SI' => [
                'nom' => 'Direction des Systèmes d\'Information',
                'departements' => [
                    'DEP_ETUDES' => ['nom' => 'Département Études et Développement', 'services' => [
                        'SRV_DEV' => 'Service Développement',
                    ]],
                    'DEP_EXPLOIT' => ['nom' => 'Département Exploitation', 'services' => [
                        'SRV_RESEAU' => 'Service Réseau et Infrastructure',
                        'SRV_SUPPORT' => 'Service Support Utilisateurs',
                    ]],
                ],
            ],
        ];

        $ordre = 0;
        foreach ($organigramme as $codeDir => $dir) {
            $direction = Departement::create([
                'code' => $codeDir,
                'nom' => $dir['nom'],
                'type' => Departement::TYPE_DIRECTION,
                'parent_id' => null,
                'ordre' => ++$ordre,
            ]);

            foreach ($dir['departements'] as $codeDep => $dep) {
                $departement = Departement::create([
                    'code' => $codeDep,
                    'nom' => $dep['nom'],
                    'type' => Departement::TYPE_DEPARTEMENT,
                    'parent_id' => $direction->id,
                    'ordre' => ++$ordre,
                ]);

                foreach ($dep['services'] as $codeSrv => $nomSrv) {
                    Departement::create([
                        'code' => $codeSrv,
                        'nom' => $nomSrv,
                        'type' => Departement::TYPE_SERVICE,
                        'parent_id' => $departement->id,
                        'ordre' => ++$ordre,
                    ]);
                }
            }
        }
    }
}
