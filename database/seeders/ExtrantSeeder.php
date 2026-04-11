<?php

namespace Database\Seeders;

use App\Models\Extrant;
use App\Models\Resultat;
use Illuminate\Database\Seeder;

class ExtrantSeeder extends Seeder
{
    public function run(): void
    {
        $resultats = Resultat::all();

        if ($resultats->isEmpty()) {
            $this->command->error('Aucun résultat trouvé. Veuillez d\'abord exécuter ResultatSeeder.');
            return;
        }

        $compteur = 0;

        foreach ($resultats as $resultat) {
            // Générer entre 5 et 10 extrants par résultat
            $nbExtrants = rand(5, 10);

            for ($i = 0; $i < $nbExtrants; $i++) {
                Extrant::factory()
                    ->forResultat($resultat)
                    ->actif()
                    ->create([
                        'ordre' => $i + 1,
                    ]);
                $compteur++;
            }

            $this->command->info("✅ {$nbExtrants} extrants créés pour le résultat {$resultat->code}");
        }

        $this->command->info("\n🎉 Total: {$compteur} extrants créés pour " . $resultats->count() . " résultats");
    }
}
