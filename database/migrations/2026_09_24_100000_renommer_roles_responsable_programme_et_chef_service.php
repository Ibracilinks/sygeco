<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Renomme les rôles « chef » en « responsable-programme » et « agent » en
 * « chef-service » (préserve les affectations et les permissions). Nécessaire pour les bases déjà alimentées. Idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->renommer('chef', 'responsable-programme');
        $this->renommer('agent', 'chef-service');
    }

    public function down(): void
    {
        $this->renommer('chef-service', 'agent');
        $this->renommer('responsable-programme', 'chef');
    }

    private function renommer(string $ancienNom, string $nouveauNom): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        $ancien = Role::where('name', $ancienNom)->where('guard_name', $guard)->first();
        $nouveau = Role::where('name', $nouveauNom)->where('guard_name', $guard)->first();

        if ($ancien && ! $nouveau) {
            $ancien->update(['name' => $nouveauNom]);
        } elseif ($ancien && $nouveau) {
            // Les deux existent : bascule les affectations vers le nouveau rôle
            // (sans doublon), fusionne les permissions, puis supprime l'ancien.
            $dejaAffectes = DB::table('model_has_roles')->where('role_id', $nouveau->id)
                ->get(['model_type', 'model_id'])
                ->map(fn ($r) => $r->model_type.'#'.$r->model_id)
                ->all();

            foreach (DB::table('model_has_roles')->where('role_id', $ancien->id)->get() as $ligne) {
                if (! in_array($ligne->model_type.'#'.$ligne->model_id, $dejaAffectes, true)) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $nouveau->id,
                        'model_type' => $ligne->model_type,
                        'model_id' => $ligne->model_id,
                    ]);
                }
            }

            $nouveau->givePermissionTo($ancien->permissions);
            $ancien->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
