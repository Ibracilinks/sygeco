<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Idempotent : firstOrCreate + syncPermissions, pour coexister avec la
        // migration de réconciliation des rôles et pouvoir être rejoué sans erreur.
        $permissions = [
            // Objectifs
            'view_objectifs', 'create_objectifs', 'edit_objectifs', 'delete_objectifs', 'validate_objectifs',
            // Extrants
            'view_extrants', 'create_extrants', 'edit_extrants', 'delete_extrants',
            // Activités
            'view_activites', 'create_activites', 'edit_activites', 'delete_activites', 'submit_activites', 'validate_activites',
            // Départements
            'view_departements', 'create_departements', 'edit_departements', 'delete_departements',
            // Utilisateurs
            'view_users', 'create_users', 'edit_users', 'delete_users',
            // Exercices
            'view_exercices', 'manage_exercices',
            // Indicateurs
            'view_indicateurs', 'create_indicateurs', 'edit_indicateurs', 'delete_indicateurs', 'validate_indicateurs',
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
            'view_departements',
            'view_users',
            'view_exercices',
        ]);

        $roleAgent->syncPermissions(['view_activites']);
    }
}
