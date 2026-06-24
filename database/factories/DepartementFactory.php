<?php

namespace Database\Factories;

use App\Models\Departement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Departement>
 */
class DepartementFactory extends Factory
{
    protected $model = Departement::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('DEP_####')),
            'nom' => $this->faker->unique()->company(),
            'description' => $this->faker->optional()->sentence(),
            'responsable_id' => null,
            'responsable_nom' => $this->faker->name(),
            'responsable_email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->optional()->phoneNumber(),
            'is_active' => true,
            'ordre' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function avecCode(string $code): static
    {
        return $this->state(fn () => ['code' => $code]);
    }
}
