<?php

namespace Database\Seeders;

use App\Models\Resultat;
use App\Models\Objectif;
use Illuminate\Database\Seeder;

class ResultatSeeder extends Seeder
{
    public function run(): void
    {
        $objectifs = Objectif::all();

        if ($objectifs->isEmpty()) {
            $this->command->error('Aucun objectif trouvé. Veuillez d\'abord exécuter ObjectifSeeder.');
            return;
        }

        $compteur = 0;

        foreach ($objectifs as $objectif) {
            // Générer entre 5 et 10 résultats par objectif
            $nbResultats = rand(5, 10);

            for ($i = 0; $i < $nbResultats; $i++) {
                Resultat::factory()
                    ->forObjectif($objectif)
                    ->actif()
                    ->create([
                        'ordre' => $i + 1,
                    ]);
                $compteur++;
            }

            $this->command->info("✅ {$nbResultats} résultats créés pour l'objectif {$objectif->code}");
        }

        $this->command->info("\n🎉 Total: {$compteur} résultats créés pour " . $objectifs->count() . " objectifs");
    }
}
