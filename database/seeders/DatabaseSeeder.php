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

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@canam.ml',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            RoleAndPermissionSeeder::class,
            DepartementSeeder::class,
            ObjectifSeeder::class,
            ResultatSeeder::class,
            ExtrantSeeder::class,
            ActiviteSeeder::class,
            // IndicateurSeeder::class,
            // IndicateurValeurSeeder::class,
        ]);

        $user->assignRole('dbcgoq');
    }
}
