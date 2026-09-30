<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Amorçage minimal : rôles & permissions, puis les deux comptes d'administration.
     * Les données métier (départements, PTA…) se chargent via leurs seeders dédiés.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        $this->creerCompte('superadmin@canam.ml', 'Super Admin', 'superadmin');
        $this->creerCompte('admin@canam.ml', 'Admin DBCGOQ', 'dbcgoq');
    }

    /**
     * Crée (ou retrouve) un compte vérifié et lui attribue un rôle unique.
     */
    private function creerCompte(string $email, string $nom, string $role): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $nom,
                'password' => Hash::make('password'),
            ]
        );

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->syncRoles([$role]);
    }
}
