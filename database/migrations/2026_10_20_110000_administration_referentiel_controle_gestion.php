<?php

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Le service contrôle de gestion consulte, ajoute et modifie les entités, les
 * utilisateurs, les exercices et le cadre logique (sans suppression). Crée les
 * permissions manquantes (résultats, suppression d'exercice) et les lui accorde.
 */
return new class extends Migration
{
    private const NOUVELLES = ['view_resultats', 'create_resultats', 'edit_resultats', 'delete_resultats', 'delete_exercices'];

    public function up(): void
    {
        // Sur une base neuve, les permissions n'existent pas encore : on les crée au besoin.
        foreach ([...self::NOUVELLES, ...RoleAndPermissionSeeder::ADMINISTRATION_CONTROLE_GESTION] as $nom) {
            Permission::findOrCreate($nom, 'web');
        }

        // Le superadmin dispose de toutes les permissions.
        Role::query()->where('name', 'superadmin')->first()?->givePermissionTo(self::NOUVELLES);

        Role::query()->where('name', 'service-controle-gestion')->first()
            ?->givePermissionTo(RoleAndPermissionSeeder::ADMINISTRATION_CONTROLE_GESTION);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $controleGestion = Role::query()->where('name', 'service-controle-gestion')->first();

        foreach (['create_departements', 'edit_departements', 'view_departements', 'create_users', 'edit_users', 'view_users', 'manage_exercices', 'create_objectifs', 'edit_objectifs', 'view_resultats', 'create_resultats', 'edit_resultats', 'create_extrants', 'edit_extrants'] as $nom) {
            $controleGestion?->revokePermissionTo($nom);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
