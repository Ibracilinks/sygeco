<?php

namespace Database\Factories;

use App\Models\ObjectifStrategique;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObjectifStrategiqueFactory extends Factory
{
    protected $model = ObjectifStrategique::class;

    // Prégénérer les combinaisons pour plus de rapidité
    private static $libelles = [];
    private static $descriptions = [];
    private static $counter = 0;

    public function definition(): array
    {
        self::$counter++;

        $actions = [
            'Améliorer',
            'Renforcer',
            'Optimiser',
            'Développer',
            'Former',
            'Digitaliser',
            'Moderniser',
            'Accélérer',
            'Simplifier',
            'Sécuriser'
        ];

        $domaines = [
            'la qualité',
            'la couverture',
            'la gestion',
            'les infrastructures',
            'le personnel',
            'les processus',
            'la gouvernance',
            'le contrôle'
        ];

        $action = $this->faker->randomElement($actions);
        $domaine = $this->faker->randomElement($domaines);

        return [
            'code' => 'OS_' . str_pad(self::$counter, 4, '0', STR_PAD_LEFT),
            'libelle' => $action . ' ' . $domaine,
            'description' => "Objectif stratégique visant à " . strtolower($action) . " " . $domaine . " de manière efficace et durable.",
            'ordre' => self::$counter,
            'is_active' => $this->faker->boolean(85),
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
