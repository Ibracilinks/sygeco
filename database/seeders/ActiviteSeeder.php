<?php

namespace Database\Seeders;

use App\Models\Activite;
use App\Models\Extrant;
use Illuminate\Database\Seeder;

class ActiviteSeeder extends Seeder
{
    public function run(): void
    {
        $extrants = Extrant::all();

        if ($extrants->isEmpty()) {
            $this->command->error("Aucun extrant trouvé !");
            return;
        }

        foreach ($extrants as $extrant) {
            // Créer 3 à 8 activités par extrant
            Activite::factory()
                ->count(rand(3, 8))
                ->for($extrant)
                ->create();
        }

        $this->command->info("✅ Activités générées !");
    }
}
