<?php

namespace Database\Seeders;

use App\Models\Exercice;
use Illuminate\Database\Seeder;

class ExerciceSeeder extends Seeder
{
    public function run(): void
    {
        $annee = 2024;

        Exercice::query()->firstOrCreate(
            ['annee' => $annee],
            [
                'date_debut' => sprintf('%d-01-01', $annee),
                'date_fin' => sprintf('%d-12-31', $annee),
                'statut' => 'actif',
            ]
        );
    }
}
