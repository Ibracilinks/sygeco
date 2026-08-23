<?php

namespace Database\Seeders;

use App\Models\Departement;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    /**
     * Construit l'organigramme réel :
     * Direction Générale (unique, ne formule pas d'activités)
     *   ├── Directions Centrales → Services
     *   ├── Services rattachés (directement sous la DG)
     *   ├── Agence Comptable (indépendante, soumet à la DG)
     *   └── Bureaux Régionaux (rattachés à la DG, sans sous-entités)
     * Le chef de chaque entité soumet ses éléments au chef de l'entité parente ;
     * les entités rattachées à la DG soumettent à l'arbitrage central.
     */
    public function run(): void
    {
        $directionsCentrales = [
            'DC_FIN' => ['nom' => 'Direction Centrale des Finances', 'services' => [
                'SRV_COMPTA_GEN' => 'Service Comptabilité Générale',
                'SRV_BUDGET_PREP' => 'Service Préparation Budgétaire',
                'SRV_BUDGET_EXEC' => 'Service Exécution Budgétaire',
            ]],
            'DC_RH' => ['nom' => 'Direction Centrale des Ressources Humaines', 'services' => [
                'SRV_PAIE_TRAIT' => 'Service Traitement de la Paie',
                'SRV_FORMATION' => 'Service Formation',
                'SRV_RECRUT' => 'Service Recrutement',
            ]],
            'DC_SI' => ['nom' => 'Direction Centrale des Systèmes d\'Information', 'services' => [
                'SRV_DEV' => 'Service Développement',
                'SRV_RESEAU' => 'Service Réseau et Infrastructure',
                'SRV_SUPPORT' => 'Service Support Utilisateurs',
            ]],
        ];

        // Les deux seuls services qui ne dépendent pas d'une Direction Centrale.
        $servicesRattaches = [
            'SRV_AUDIT' => 'Service Audit Interne',
            'SRV_COM' => 'Service Communication',
        ];

        $bureauxRegionaux = [
            'BR_BKO' => 'Bureau Régional de Bamako',
            'BR_SIK' => 'Bureau Régional de Sikasso',
            'BR_MOP' => 'Bureau Régional de Mopti',
        ];

        $ordre = 0;

        $directionGenerale = Departement::create([
            'code' => 'DG',
            'nom' => 'Direction Générale',
            'type' => Departement::TYPE_DIRECTION,
            'parent_id' => null,
            'ordre' => ++$ordre,
        ]);

        foreach ($directionsCentrales as $code => $directionCentrale) {
            $entite = Departement::create([
                'code' => $code,
                'nom' => $directionCentrale['nom'],
                'type' => Departement::TYPE_DEPARTEMENT,
                'parent_id' => $directionGenerale->id,
                'ordre' => ++$ordre,
            ]);

            foreach ($directionCentrale['services'] as $codeService => $nomService) {
                Departement::create([
                    'code' => $codeService,
                    'nom' => $nomService,
                    'type' => Departement::TYPE_SERVICE,
                    'parent_id' => $entite->id,
                    'ordre' => ++$ordre,
                ]);
            }
        }

        foreach ($servicesRattaches as $code => $nom) {
            Departement::create([
                'code' => $code,
                'nom' => $nom,
                'type' => Departement::TYPE_SERVICE,
                'parent_id' => $directionGenerale->id,
                'ordre' => ++$ordre,
            ]);
        }

        Departement::create([
            'code' => 'AC',
            'nom' => 'Agence Comptable',
            'type' => Departement::TYPE_AGENCE_COMPTABLE,
            'parent_id' => $directionGenerale->id,
            'ordre' => ++$ordre,
        ]);

        foreach ($bureauxRegionaux as $code => $nom) {
            Departement::create([
                'code' => $code,
                'nom' => $nom,
                'type' => Departement::TYPE_BUREAU_REGIONAL,
                'parent_id' => $directionGenerale->id,
                'ordre' => ++$ordre,
            ]);
        }
    }
}
