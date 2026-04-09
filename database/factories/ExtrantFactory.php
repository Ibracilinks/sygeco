<?php

namespace Database\Factories;

use App\Models\Extrant;
use App\Models\ResultatStrategique;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExtrantFactory extends Factory
{
    protected $model = Extrant::class;

    private static $counter = 0;

    public function definition(): array
    {
        self::$counter++;

        return [
            'resultat_strategique_id' => ResultatStrategique::factory(),
            'code' => 'EXT_' . str_pad(self::$counter, 5, '0', STR_PAD_LEFT),
            'libelle' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(),
            'ordre' => $this->faker->numberBetween(1, 5),
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

    public function forResultat(ResultatStrategique $resultat): static
    {
        return $this->state(fn(array $attributes) => [
            'resultat_strategique_id' => $resultat->id,
        ]);
    }
}
