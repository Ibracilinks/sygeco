<?php

namespace Database\Factories;

use App\Models\Exercice;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciceFactory extends Factory
{
    protected $model = Exercice::class;

    public function definition(): array
    {
        $annee = $this->faker->unique()->numberBetween(2020, 2030);

        return [
            'annee' => $annee,
            'date_debut' => sprintf('%d-01-01', $annee),
            'date_fin' => sprintf('%d-12-31', $annee),
            'statut' => 'brouillon',
        ];
    }

    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'actif',
        ]);
    }
}
