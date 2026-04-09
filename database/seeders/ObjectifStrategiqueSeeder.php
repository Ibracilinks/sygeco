<?php

namespace Database\Seeders;

use App\Models\ObjectifStrategique;
use Illuminate\Database\Seeder;

class ObjectifStrategiqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Option 1: Générer 500 objectifs avec la factory
        $this->command->info('Génération des objectifs stratégiques...');

        $nombre = 50; // Changez ce nombre selon vos besoins (100, 200, 500, 1000)

        // Vider la table (optionnel)
        // ObjectifStrategique::truncate();

        // Générer les objectifs
        ObjectifStrategique::factory()
            ->count($nombre)
            ->create();

        $this->command->info("✅ {$nombre} objectifs stratégiques créés avec succès !");

        // Option 2: Générer avec des statuts mixtes
        $this->generateMixedStatuses();
    }

    /**
     * Générer des objectifs avec des statuts mixtes
     */
    private function generateMixedStatuses(): void
    {
        // 80% actifs, 20% inactifs
        $actifs = 400;
        $inactifs = 100;

        ObjectifStrategique::factory()
            ->count($actifs)
            ->active()
            ->create();

        ObjectifStrategique::factory()
            ->count($inactifs)
            ->inactive()
            ->create();

        $this->command->info("✅ {$actifs} objectifs actifs et {$inactifs} objectifs inactifs créés !");
    }

    /**
     * Générer des objectifs par lots (pour éviter les problèmes de mémoire)
     */
    private function generateInBatches(): void
    {
        $total = 1000;
        $batchSize = 100;

        for ($i = 0; $i < $total; $i += $batchSize) {
            $count = min($batchSize, $total - $i);
            ObjectifStrategique::factory()
                ->count($count)
                ->create();

            $this->command->info("Lot " . ($i / $batchSize + 1) . " : {$count} objectifs créés");
        }

        $this->command->info("✅ Total de {$total} objectifs stratégiques créés !");
    }
}
