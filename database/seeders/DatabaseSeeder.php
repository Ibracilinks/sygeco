<?php

namespace Database\Seeders;

use App\Models\Exercice;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Les exercices 2024, 2025 et 2026 sont alimentés à partir des PTA réels
     * (database/data/pta_2024.csv, pta_2025.csv, pta_2026.csv) via PtaImportSeeder,
     * qui crée la hiérarchie Objectif → Résultat → Extrant → Activités ainsi que les
     * départements responsables. Les anciens générateurs de données aléatoires ont été
     * supprimés au profit de ces imports réels.
     */
    public function run(): void
    {
        // Compte requis pour renseigner « saisi_par » lors de l'import des activités.
        $admin = User::firstOrCreate(
            ['email' => 'admin@canam.ml'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        $this->call([
            RoleAndPermissionSeeder::class,
            DepartementSeeder::class,

            // Import des PTA réels par exercice (Exercice → Objectif → Résultat → Extrant → Activités).
            Pta2024Seeder::class,
            Pta2025Seeder::class,
            Pta2026Seeder::class,
        ]);

        $admin->assignRole('dbcgoq');

        // Statuts des exercices : l'année en cours (2026) est active, les antérieures sont clôturées.
        Exercice::where('annee', 2026)->update(['statut' => 'actif']);
        Exercice::whereIn('annee', [2024, 2025])->update(['statut' => 'cloture']);
    }
}
