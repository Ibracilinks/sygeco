<?php

namespace Database\Factories;

use App\Models\Activite;
use App\Models\Extrant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActiviteFactory extends Factory
{
    protected $model = Activite::class;

    private static $counter = 0;

    public function definition(): array
    {
        self::$counter++;

        $dateDebut = $this->faker->dateTimeBetween('-6 months', '+3 months');
        $dateFin = $this->faker->dateTimeBetween($dateDebut, '+6 months');

        return [
            'extrant_id' => Extrant::factory(),
            'code' => 'ACT_' . str_pad(self::$counter, 6, '0', STR_PAD_LEFT),
            'libelle' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(),
            'budget_previsionnel_global' => $this->faker->randomFloat(2, 100000, 10000000),
            'date_debut_prevue' => $dateDebut,
            'date_fin_prevue' => $dateFin,
            'ordre' => $this->faker->numberBetween(1, 10),
            'is_active' => $this->faker->boolean(80),
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

    public function withBudget(float $budget): static
    {
        return $this->state(fn(array $attributes) => [
            'budget_previsionnel_global' => $budget,
        ]);
    }

    public function forExtrant(Extrant $extrant): static
    {
        return $this->state(fn(array $attributes) => [
            'extrant_id' => $extrant->id,
        ]);
    }
}
