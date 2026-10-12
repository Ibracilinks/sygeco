<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Le chef de service programme les activités de son service et les soumet ; la
 * validation revient au responsable de programme de l'entité qui le chapeaute
 * (flux montant de ValidationController). Nécessaire pour les bases déjà alimentées.
 * Idempotente.
 */
return new class extends Migration
{
    private const PERMISSIONS = ['create_activites', 'edit_activites', 'submit_activites'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');
        $role = Role::where('name', 'chef-service')->where('guard_name', $guard)->first();

        if ($role) {
            $role->givePermissionTo(
                Permission::whereIn('name', self::PERMISSIONS)->where('guard_name', $guard)->get()
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');
        $role = Role::where('name', 'chef-service')->where('guard_name', $guard)->first();

        foreach (self::PERMISSIONS as $permission) {
            if ($role?->hasPermissionTo($permission)) {
                $role->revokePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
