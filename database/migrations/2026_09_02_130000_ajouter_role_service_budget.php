<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Ajoute le rôle « service-budget » : le chargé des missions, qui n'accède qu'au
 * module Missions. Nécessaire pour les bases déjà alimentées. Idempotente.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'view_missions',
        'create_missions',
        'edit_missions',
        'delete_missions',
        'generate_missions_pdf',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        $role = Role::firstOrCreate(['name' => 'service-budget', 'guard_name' => $guard]);

        // Les permissions absentes de la base (installation partielle) sont ignorées
        // plutôt que de faire échouer la migration.
        $role->syncPermissions(
            Permission::whereIn('name', self::PERMISSIONS)->where('guard_name', $guard)->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::where('name', 'service-budget')
            ->where('guard_name', config('auth.defaults.guard', 'web'))
            ->get()
            ->each
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
