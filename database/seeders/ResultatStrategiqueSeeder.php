<?php

namespace Database\Seeders;

use App\Models\ObjectifStrategique;
use App\Models\ResultatStrategique;
use Illuminate\Database\Seeder;

class ResultatStrategiqueSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer tous les objectifs
        $objectifs = ObjectifStrategique::all();

        if ($objectifs->isEmpty()) {
            $this->command->error("Aucun objectif trouvé ! Exécutez d'abord le seeder des objectifs.");
            return;
        }

        // Pour chaque objectif, créer 5 à 10 résultats
        foreach ($objectifs as $objectif) {
            $nombre = rand(5, 10);
            ResultatStrategique::factory()
                ->count($nombre)
                ->for($objectif)
                ->create();

            $this->command->info("✅ {$nombre} résultats créés pour l'objectif {$objectif->code}");
        }

        $this->command->info("🎉 Tous les résultats stratégiques ont été générés !");
    }
}
