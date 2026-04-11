<?php

namespace Database\Factories;

use App\Models\Resultat;
use App\Models\Objectif;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultatFactory extends Factory
{
    protected $model = Resultat::class;

    public function definition(): array
    {
        // Liste des résultats stratégiques réalistes
        $resultatsList = [
            [
                'code_prefix' => 'RS_PERF',
                'libelle' => 'Amélioration de la performance opérationnelle',
                'description' => 'Optimisation des processus et des indicateurs de performance pour une meilleure efficacité'
            ],
            [
                'code_prefix' => 'RS_QUAL',
                'libelle' => 'Renforcement de la qualité des services',
                'description' => 'Mise en place de normes qualité et amélioration continue des services'
            ],
            [
                'code_prefix' => 'RS_GEST',
                'libelle' => 'Optimisation de la gestion budgétaire',
                'description' => 'Amélioration du suivi et du contrôle budgétaire'
            ],
            [
                'code_prefix' => 'RS_NUM',
                'libelle' => 'Accélération de la transformation digitale',
                'description' => 'Digitalisation des processus et dématérialisation des échanges'
            ],
            [
                'code_prefix' => 'RS_RH',
                'libelle' => 'Renforcement des compétences du personnel',
                'description' => 'Formation et développement des compétences des agents'
            ],
            [
                'code_prefix' => 'RS_COMM',
                'libelle' => 'Amélioration de la communication',
                'description' => 'Renforcement de la communication interne et externe'
            ],
            [
                'code_prefix' => 'RS_SAT',
                'libelle' => 'Amélioration de la satisfaction des assurés',
                'description' => 'Enquêtes et actions pour améliorer la satisfaction des usagers'
            ],
            [
                'code_prefix' => 'RS_CONF',
                'libelle' => 'Renforcement de la conformité',
                'description' => 'Mise en conformité avec les normes et réglementations'
            ],
        ];

        // Choisir un résultat aléatoire
        $resultat = $this->faker->randomElement($resultatsList);

        // Générer un numéro séquentiel unique
        $numero = rand(1000000000, 9999999999);
        $code = $resultat['code_prefix'] . '_' . $numero;

        return [
            'code' => $code,
            'libelle' => $resultat['libelle'],
            'description' => $resultat['description'],
            'ordre' => $this->faker->numberBetween(1, 10),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    /**
     * Configure le modèle factory pour utiliser l'objectif parent.
     */
    public function forObjectif(Objectif $objectif): static
    {
        return $this->state(fn(array $attributes) => [
            'objectif_id' => $objectif->id,
        ]);
    }

    /**
     * Indiquer que le résultat est actif.
     */
    public function actif(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indiquer que le résultat est inactif.
     */
    public function inactif(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indiquer que le résultat a un ordre spécifique.
     */
    public function avecOrdre(int $ordre): static
    {
        return $this->state(fn(array $attributes) => [
            'ordre' => $ordre,
        ]);
    }

    /**
     * Créer un résultat avec un code spécifique.
     */
    public function avecCode(string $code): static
    {
        return $this->state(fn(array $attributes) => [
            'code' => $code,
        ]);
    }
}
