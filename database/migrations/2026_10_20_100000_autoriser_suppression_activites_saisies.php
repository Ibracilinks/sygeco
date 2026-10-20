<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Les profils qui saisissent les activités peuvent supprimer un brouillon (ou une
 * activité rejetée) de leur entité. La policy borne toujours la suppression à
 * leur entité et aux activités encore modifiables.
 */
return new class extends Migration
{
    private const ROLES = ['responsable-programme', 'chef-service', 'agent-planification', 'service-controle-gestion'];

    public function up(): void
    {
        $permission = Permission::findOrCreate('delete_activites', 'web');

        Role::query()->whereIn('name', self::ROLES)->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Role::query()->whereIn('name', self::ROLES)->get()
            ->each(fn (Role $role) => $role->revokePermissionTo('delete_activites'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
