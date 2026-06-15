<?php

namespace Database\Seeders;

use App\Models\Exercice;
use App\Models\Objectif;
use Illuminate\Database\Seeder;

class ObjectifSeeder extends Seeder
{
    public function run(): void
    {
        $annee = 2024;

        $exercice = Exercice::where('annee', $annee)->first();

        if (! $exercice) {
            $this->command->error("Aucun exercice {$annee} trouvé. Veuillez d'abord exécuter ExerciceSeeder.");

            return;
        }

        $objectif = Objectif::updateOrCreate(
            ['exercice_id' => $exercice->id, 'code' => 'OG'],
            [
                'libelle' => 'Objectif global',
                'description' => "Développer un système de financement permettant une meilleure mobilisation et utilisation des ressources financières pour la santé, une meilleure accessibilité aux services de santé, une gestion transparente et qui incite les prestataires et les utilisateurs à être plus efficients",
                'annee' => $annee,
                'statut' => 'actif',
                'ordre' => 1,
            ]
        );

        $this->command->info("✅ Objectif {$objectif->code} créé pour l'exercice {$annee}");
    }
}
