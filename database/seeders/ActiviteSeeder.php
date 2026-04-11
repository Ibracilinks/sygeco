<?php

namespace Database\Seeders;

use App\Models\Activite;
use App\Models\Extrant;
use App\Models\Departement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActiviteSeeder extends Seeder
{
    public function run(): void
    {
        $extrants = Extrant::all();
        $departements = Departement::all();
        $user = User::first();

        if ($extrants->isEmpty()) {
            $this->command->error('Aucun extrant trouvé.');
            return;
        }

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Admin DBCGOQ',
                'email' => 'admin@dbcgoq.ci',
                'password' => bcrypt('password'),
            ]);
        }

        $compteur = 0;

        foreach ($extrants as $extrant) {
            // Générer entre 5 et 10 activités par extrant
            $nbActivites = rand(5, 10);

            // Créer les activités avec la factory
            Activite::factory()
                ->count($nbActivites)
                ->pourExtrant($extrant)
                ->saisiePar($user)
                ->create([
                    'departement_id' => $departements->random()->id,
                ]);

            $compteur += $nbActivites;
            $this->command->info("✅ {$nbActivites} activités créées pour l'extrant {$extrant->code}");
        }

        $this->command->info("\n🎉 Total: {$compteur} activités créées");
    }
}
