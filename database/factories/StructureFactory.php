<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StructureFactory extends Factory
{
    protected $model = \App\Models\Structure::class;

    public function definition(): array
    {
        $types = ['departement', 'bureau_regional', 'direction'];

        return [
            'code' => strtoupper($this->faker->unique()->lexify('STR_???')),
            'libelle' => $this->faker->company,
            'type' => $this->faker->randomElement($types),
            'parent_id' => null,
            'responsable_nom' => $this->faker->name,
            'responsable_email' => $this->faker->email,
            'telephone' => $this->faker->phoneNumber,
            'adresse' => $this->faker->address,
            'is_active' => true,
        ];
    }
}
