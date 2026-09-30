<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Ajoute le rôle « service-controle-gestion » : il cumule les permissions des
 * cellules planification et suivi & évaluation, sur l'ensemble des entités.
 * Nécessaire pour les bases déjà alimentées. Idempotente.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'view_objectifs',
        'view_extrants',
        'view_activites',
        'create_activites',
        'edit_activites',
        'submit_activites',
        'evaluate_activites',
        'view_indicateurs',
        'edit_indicateurs',
        'view_exercices',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        $role = Role::firstOrCreate(['name' => 'service-controle-gestion', 'guard_name' => $guard]);

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

        Role::where('name', 'service-controle-gestion')
            ->where('guard_name', config('auth.defaults.guard', 'web'))
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
