<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Ajoute les rôles « agent-planification » (saisie du PTA de son entité) et
 * « suivi-evaluation » (suivi d'exécution + évaluations), ainsi que la permission
 * « evaluate_activites » qui découple la saisie d'évaluation de « edit_activites ».
 *
 * Nécessaire pour les bases déjà alimentées. Idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        Permission::firstOrCreate(['name' => 'evaluate_activites', 'guard_name' => $guard]);

        // Les profils qui évaluaient jusqu'ici via « edit_activites » conservent l'accès.
        foreach (['superadmin', 'dbcgoq', 'chef'] as $nom) {
            Role::where('name', $nom)->where('guard_name', $guard)->first()
                ?->givePermissionTo('evaluate_activites');
        }

        $roles = [
            'agent-planification' => [
                'view_objectifs',
                'view_extrants',
                'view_activites',
                'create_activites',
                'edit_activites',
                'submit_activites',
                'view_exercices',
            ],
            'suivi-evaluation' => [
                'view_objectifs',
                'view_extrants',
                'view_activites',
                'evaluate_activites',
                'view_indicateurs',
                'edit_indicateurs',
                'view_exercices',
            ],
        ];

        foreach ($roles as $nom => $permissions) {
            $role = Role::firstOrCreate(['name' => $nom, 'guard_name' => $guard]);

            // Les permissions absentes de la base (installation partielle) sont ignorées
            // plutôt que de faire échouer la migration.
            $role->syncPermissions(
                Permission::whereIn('name', $permissions)->where('guard_name', $guard)->get()
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        Role::whereIn('name', ['agent-planification', 'suivi-evaluation'])
            ->where('guard_name', $guard)
            ->get()
            ->each
            ->delete();

        Permission::where('name', 'evaluate_activites')->where('guard_name', $guard)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
