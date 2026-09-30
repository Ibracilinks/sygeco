<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $departements = Departement::all();

        // 1. Admin DBCGOQ
        User::factory()
            ->dbcgoq()
            ->create([
                'name' => 'Admin DBCGOQ',
                'email' => 'dbcgoq@canam.ml',
                'poste' => 'Directeur',
            ]);

        $this->command->info('✅ Admin DBCGOQ créé');

        // 2. Chef de département pour chaque département
        foreach ($departements as $departement) {
            User::factory()
                ->responsableProgramme()
                ->dansDepartement($departement)
                ->create([
                    'name' => fake()->name(),
                    'email' => "chef.{$departement->code}@canam.ml",
                    'poste' => 'Chef de département',
                ]);
        }

        $this->command->info('✅ Chefs de département créés');

        // 3. Agents (3-5 par département)
        foreach ($departements as $departement) {
            $nbAgents = rand(3, 5);

            User::factory()
                ->count($nbAgents)
                ->chefService()
                ->dansDepartement($departement)
                ->create([
                    'poste' => 'Agent de saisie',
                ]);

            $this->command->info("✅ {$nbAgents} agents créés pour le département {$departement->code}");
        }

        // 4. Utilisateurs supplémentaires aléatoires
        User::factory()
            ->count(10)
            ->chefService()
            ->create();

        $this->command->info('✅ Utilisateurs supplémentaires créés');
        $this->command->info('📊 Total utilisateurs: ' . User::count());
    }
}
