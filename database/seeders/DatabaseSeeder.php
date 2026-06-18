<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $user = User::firstOrCreate(
            ['email' => 'admin@canam.ml'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        $this->call([
            RoleAndPermissionSeeder::class,
            DepartementSeeder::class,
            ExerciceSeeder::class,
            ObjectifSeeder::class,
            ResultatSeeder::class,
            ExtrantSeeder::class,
            ActiviteSeeder::class,
            Pta2025Seeder::class,
            Pta2026Seeder::class,
            // IndicateurSeeder::class,
            // IndicateurValeurSeeder::class,
        ]);

        $user->assignRole('dbcgoq');
    }
}
