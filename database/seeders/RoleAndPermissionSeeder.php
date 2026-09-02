<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Idempotent : firstOrCreate + syncPermissions, pour coexister avec la
        // migration de réconciliation des rôles et pouvoir être rejoué sans erreur.
        $permissions = [
            // Objectifs
            'view_objectifs', 'create_objectifs', 'edit_objectifs', 'delete_objectifs', 'validate_objectifs',
            // Extrants
            'view_extrants', 'create_extrants', 'edit_extrants', 'delete_extrants',
            // Activités
            'view_activites', 'create_activites', 'edit_activites', 'delete_activites', 'submit_activites', 'validate_activites', 'evaluate_activites',
            // Départements
            'view_departements', 'create_departements', 'edit_departements', 'delete_departements',
            // Utilisateurs
            'view_users', 'create_users', 'edit_users', 'delete_users',
            // Exercices
            'view_exercices', 'manage_exercices',
            // Indicateurs
            'view_indicateurs', 'create_indicateurs', 'edit_indicateurs', 'delete_indicateurs', 'validate_indicateurs',
            // Missions
            'view_missions', 'create_missions', 'edit_missions', 'delete_missions', 'generate_missions_pdf',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Création des rôles (idempotente)
        // superadmin : équipe IT, accès technique complet
        $roleSuperadmin = Role::firstOrCreate(['name' => 'superadmin']);
        // dbcgoq : gère toutes les données métier de l'application
        $roleDbcgoq = Role::firstOrCreate(['name' => 'dbcgoq']);
        // chef : responsable d'une entité (direction, département ou service) ;
        // le niveau découle du type de l'entité rattachée à l'utilisateur
        $roleChef = Role::firstOrCreate(['name' => 'chef']);
        $roleAgent = Role::firstOrCreate(['name' => 'agent']);
        // agent-planification : saisit et soumet la programmation de son entité.
        // Il ne voit ni l'évaluation, ni l'arbitrage, ni l'administration.
        $roleAgentPlanification = Role::firstOrCreate(['name' => 'agent-planification']);
        // suivi-evaluation : renseigne le suivi d'exécution et les évaluations
        // (mi-parcours / fin d'année) de son entité, en lecture seule sur le PTA.
        $roleSuiviEvaluation = Role::firstOrCreate(['name' => 'suivi-evaluation']);
        // service-budget : chargé des missions. Il ne voit que le module Missions.
        $roleServiceBudget = Role::firstOrCreate(['name' => 'service-budget']);

        // Attribution des permissions
        $roleSuperadmin->syncPermissions(Permission::all());
        $roleDbcgoq->syncPermissions(Permission::all());

        // Le chef valide les soumissions de ses entités enfants via le flux
        // montant (middleware role:chef + ValidationController). La permission
        // « validate_activites » reste réservée au validateur central (dbcgoq),
        // car elle conditionne aussi l'accès permanent au suivi d'exécution.
        $roleChef->syncPermissions([
            'view_activites',
            'create_activites',
            'edit_activites',
            'submit_activites',
            'evaluate_activites',
            'view_departements',
            'view_users',
            'view_exercices'
        ]);

        $roleAgent->syncPermissions(['view_activites']);

        // Cellule planification : chaîne « cadre logique » en lecture, activités en
        // écriture jusqu'à la soumission. Pas d'évaluation, pas de validation.
        $roleAgentPlanification->syncPermissions([
            'view_objectifs',
            'view_extrants',
            'view_activites',
            'create_activites',
            'edit_activites',
            'submit_activites',
            'view_exercices',
        ]);

        // Chargé des missions : le module Missions de bout en bout, et rien d'autre.
        // Les barèmes restent du ressort de la DBCGOQ.
        $roleServiceBudget->syncPermissions([
            'view_missions',
            'create_missions',
            'edit_missions',
            'delete_missions',
            'generate_missions_pdf',
        ]);

        // Cellule suivi & évaluation : lecture du PTA, écriture sur les évaluations
        // et les indicateurs uniquement.
        $roleSuiviEvaluation->syncPermissions([
            'view_objectifs',
            'view_extrants',
            'view_activites',
            'evaluate_activites',
            'view_indicateurs',
            'edit_indicateurs',
            'view_exercices',
        ]);
    }
}
