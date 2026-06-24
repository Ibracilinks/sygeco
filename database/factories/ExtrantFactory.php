<?php

namespace Database\Factories;

use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExtrantFactory extends Factory
{
    protected $model = Extrant::class;

    public function definition(): array
    {
        $extrantsList = [
            [
                'code_prefix' => 'EXT',
                'libelle_prefix' => 'Mise en place',
            ],
            [
                'code_prefix' => 'EXT',
                'libelle_prefix' => 'Développement',
            ],
            [
                'code_prefix' => 'EXT',
                'libelle_prefix' => 'Formation',
            ],
            [
                'code_prefix' => 'EXT',
                'libelle_prefix' => 'Audit',
            ],
            [
                'code_prefix' => 'EXT',
                'libelle_prefix' => 'Rapport',
            ],
        ];

        $extrant = $this->faker->randomElement($extrantsList);
        $numero = mt_rand(1000000000, 9999999999);
        $code = $extrant['code_prefix'] . '_' . $numero;

        return [
            // Par défaut un extrant appartient à un objectif (FK non nullable).
            // Surchargé par les états forResultat()/forObjectif() et les seeders.
            'objectif_id' => Objectif::factory(),
            'code' => $code,
            'libelle' => $extrant['libelle_prefix'] . ' ' . $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'ordre' => $this->faker->numberBetween(1, 10),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    public function forResultat(Resultat $resultat): static
    {
        return $this->state(fn(array $attributes) => [
            'resultat_id' => $resultat->id,
            'objectif_id' => $resultat->objectif_id,
        ]);
    }

    public function actif(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactif(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
