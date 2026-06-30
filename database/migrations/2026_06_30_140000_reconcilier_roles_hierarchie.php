<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Réconcilie les rôles existants avec la structure hiérarchique :
 * - renomme « chef_departement » en « chef » (préserve les affectations) ;
 * - crée le rôle « superadmin » (équipe IT, toutes permissions) ;
 * - resynchronise les permissions du rôle « chef ».
 *
 * Nécessaire pour les bases déjà alimentées (impossible de migrate:fresh
 * sans perdre les données métier). Idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        // 1. chef_departement → chef (rename : conserve model_has_roles).
        $chef = Role::where('name', 'chef')->where('guard_name', $guard)->first();
        $ancien = Role::where('name', 'chef_departement')->where('guard_name', $guard)->first();

        if ($ancien && ! $chef) {
            $ancien->update(['name' => 'chef']);
            $chef = $ancien;
        } elseif ($ancien && $chef) {
            // Les deux existent : bascule les affectations vers « chef », puis supprime l'ancien.
            \DB::table('model_has_roles')
                ->where('role_id', $ancien->id)
                ->update(['role_id' => $chef->id]);
            $ancien->delete();
        }

        $chef = $chef ?: Role::firstOrCreate(['name' => 'chef', 'guard_name' => $guard]);

        // 2. superadmin (IT) : toutes les permissions.
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => $guard]);
        $superadmin->syncPermissions(Permission::where('guard_name', $guard)->get());

        // 3. Permissions du rôle chef (mêmes que le seeder de référence).
        $permsChef = [
            'view_activites',
            'create_activites',
            'edit_activites',
            'submit_activites',
            'view_departements',
            'view_users',
            'view_exercices',
        ];
        $chef->syncPermissions(
            Permission::whereIn('name', $permsChef)->where('guard_name', $guard)->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        // Renomme chef → chef_departement et retire superadmin.
        $chef = Role::where('name', 'chef')->where('guard_name', $guard)->first();
        if ($chef && ! Role::where('name', 'chef_departement')->where('guard_name', $guard)->exists()) {
            $chef->update(['name' => 'chef_departement']);
        }

        Role::where('name', 'superadmin')->where('guard_name', $guard)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
