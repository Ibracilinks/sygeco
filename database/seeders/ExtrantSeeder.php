<?php

namespace Database\Seeders;

use App\Models\Extrant;
use App\Models\ResultatStrategique;
use Illuminate\Database\Seeder;

class ExtrantSeeder extends Seeder
{
    public function run(): void
    {
        $resultats = ResultatStrategique::all();

        if ($resultats->isEmpty()) {
            $this->command->error("Aucun résultat trouvé !");
            return;
        }

        foreach ($resultats as $resultat) {
            // Créer 2 à 5 extrants par résultat
            Extrant::factory()
                ->count(rand(2, 5))
                ->for($resultat)
                ->create();
        }

        $this->command->info("✅ Extrants générés !");
    }
}
