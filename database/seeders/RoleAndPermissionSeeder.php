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

        // Permissions pour Objectifs
        Permission::create(['name' => 'view_objectifs']);
        Permission::create(['name' => 'create_objectifs']);
        Permission::create(['name' => 'edit_objectifs']);
        Permission::create(['name' => 'delete_objectifs']);
        Permission::create(['name' => 'validate_objectifs']);

        // Permissions pour Extrants
        Permission::create(['name' => 'view_extrants']);
        Permission::create(['name' => 'create_extrants']);
        Permission::create(['name' => 'edit_extrants']);
        Permission::create(['name' => 'delete_extrants']);

        // Permissions pour Activités
        Permission::create(['name' => 'view_activites']);
        Permission::create(['name' => 'create_activites']);
        Permission::create(['name' => 'edit_activites']);
        Permission::create(['name' => 'delete_activites']);
        Permission::create(['name' => 'submit_activites']);
        Permission::create(['name' => 'validate_activites']);

        // Permissions pour Départements
        Permission::create(['name' => 'view_departements']);
        Permission::create(['name' => 'create_departements']);
        Permission::create(['name' => 'edit_departements']);
        Permission::create(['name' => 'delete_departements']);

        // Permissions pour Utilisateurs
        Permission::create(['name' => 'view_users']);
        Permission::create(['name' => 'create_users']);
        Permission::create(['name' => 'edit_users']);
        Permission::create(['name' => 'delete_users']);

        // Permissions pour Exercices
        Permission::create(['name' => 'view_exercices']);
        Permission::create(['name' => 'manage_exercices']);

        // Permissions pour Indicateurs
        Permission::create(['name' => 'view_indicateurs']);
        Permission::create(['name' => 'create_indicateurs']);
        Permission::create(['name' => 'edit_indicateurs']);
        Permission::create(['name' => 'delete_indicateurs']);
        Permission::create(['name' => 'validate_indicateurs']);

        // Création des rôles
        // superadmin : équipe IT, accès technique complet
        $roleSuperadmin = Role::create(['name' => 'superadmin']);
        // dbcgoq : gère toutes les données métier de l'application
        $roleDbcgoq = Role::create(['name' => 'dbcgoq']);
        // chef : responsable d'une entité (direction, département ou service) ;
        // le niveau découle du type de l'entité rattachée à l'utilisateur
        $roleChef = Role::create(['name' => 'chef']);
        $roleAgent = Role::create(['name' => 'agent']);

        // Attribution des permissions
        $roleSuperadmin->givePermissionTo(Permission::all());
        $roleDbcgoq->givePermissionTo(Permission::all());

        // Le chef valide les soumissions de ses entités enfants via le flux
        // montant (middleware role:chef + ValidationController). La permission
        // « validate_activites » reste réservée au validateur central (dbcgoq),
        // car elle conditionne aussi l'accès permanent au suivi d'exécution.
        $roleChef->givePermissionTo([
            'view_activites',
            'create_activites',
            'edit_activites',
            'submit_activites',
            'view_departements',
            'view_users',
            'view_exercices',
        ]);

        $roleAgent->givePermissionTo([
            'view_activites'
        ]);
    }
}
