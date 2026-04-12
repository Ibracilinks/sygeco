<?php

namespace Database\Seeders;

use App\Models\Exercice;
use Illuminate\Database\Seeder;

class ExerciceSeeder extends Seeder
{
    public function run(): void
    {
        $y = (int) date('Y');

        Exercice::query()->firstOrCreate(
            ['annee' => $y],
            [
                'date_debut' => sprintf('%d-01-01', $y),
                'date_fin' => sprintf('%d-12-31', $y),
                'statut' => 'actif',
            ]
        );
    }
}
