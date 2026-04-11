<?php

namespace Database\Factories;

use App\Models\Objectif;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObjectifFactory extends Factory
{
    protected $model = Objectif::class;

    public function definition(): array
    {
        $statuts = ['actif', 'inactif'];
        $annee = $this->faker->numberBetween(2025, 2026);

        // Liste des objectifs stratégiques réalistes
        $objectifsList = [
            [
                'code' => 'PS_PERF_' . mt_rand(1000000000,9999999999),
                'libelle' => 'Améliorer la performance opérationnelle de la CANAM',
                'description' => 'Optimiser les processus internes pour une meilleure efficacité et réduction des délais de traitement'
            ],
            [
                'code' => 'PS_QUAL_' . mt_rand(1000000000,9999999999),
                'libelle' => 'Renforcer la qualité des services aux assurés',
                'description' => 'Améliorer la satisfaction des assurés par des services de qualité et un suivi personnalisé'
            ],
            [
                'code' => 'PS_NUM_' . mt_rand(1000000000,9999999999),
                'libelle' => 'Accélérer la transformation digitale',
                'description' => 'Digitaliser les processus métier pour plus d\'efficacité et de transparence'
            ],
            [
                'code' => 'PS_GOUV_' . mt_rand(1000000000,9999999999),
                'libelle' => 'Renforcer la gouvernance et la transparence',
                'description' => 'Mettre en place des mécanismes de gouvernance performants et transparents'
            ],
            [
                'code' => 'PS_SOC_' . mt_rand(1000000000,9999999999),
                'libelle' => 'Étendre la couverture sanitaire universelle',
                'description' => 'Permettre à un plus grand nombre d\'accéder aux soins de qualité'
            ],
            [
                'code' => 'PS_FIN_' . mt_rand(1000000000,9999999999),
                'libelle' => 'Optimiser la gestion financière',
                'description' => 'Améliorer la gestion budgétaire et le contrôle financier'
            ],
            [
                'code' => 'PS_RH_' . mt_rand(1000000000,9999999999),
                'libelle' => 'Développer les compétences du personnel',
                'description' => 'Former et accompagner les agents pour une meilleure performance'
            ],
            [
                'code' => 'PS_COMM_' . mt_rand(1000000000,9999999999),
                'libelle' => 'Renforcer la communication interne et externe',
                'description' => 'Améliorer la diffusion de l\'information et la coordination'
            ],
        ];

        // Choisir un objectif aléatoire ou générer un nouveau
        if ($this->faker->boolean(80)) {
            $objectif = $this->faker->randomElement($objectifsList);
            $code = $objectif['code'];
            $libelle = $objectif['libelle'];
            $description = $objectif['description'];
        } else {
            $code = strtoupper($this->faker->unique()->bothify('PS_###'));
            $libelle = $this->faker->sentence(6);
            $description = $this->faker->paragraph();
        }

        return [
            'code' => $code,
            'libelle' => $libelle,
            'description' => $description,
            'annee' => $annee,
            'statut' => $this->faker->randomElement($statuts),
            'ordre' => $this->faker->numberBetween(1, 10),
        ];
    }

    /**
     * Indiquer que l'objectif est actif.
     */
    public function actif(): static
    {
        return $this->state(fn(array $attributes) => [
            'statut' => 'actif',
        ]);
    }

    /**
     * Indiquer que l'objectif est inactif.
     */
    public function inactif(): static
    {
        return $this->state(fn(array $attributes) => [
            'statut' => 'inactif',
        ]);
    }

    /**
     * Indiquer que l'objectif est pour une année spécifique.
     */
    public function pourAnnee(int $annee): static
    {
        return $this->state(fn(array $attributes) => [
            'annee' => $annee,
        ]);
    }

    /**
     * Indiquer que l'objectif a un ordre spécifique.
     */
    public function avecOrdre(int $ordre): static
    {
        return $this->state(fn(array $attributes) => [
            'ordre' => $ordre,
        ]);
    }

    /**
     * Créer un objectif avec un code spécifique.
     */
    public function avecCode(string $code): static
    {
        return $this->state(fn(array $attributes) => [
            'code' => $code,
        ]);
    }

    /**
     * Créer un objectif pour l'année en cours.
     */
    public function anneeEnCours(): static
    {
        return $this->state(fn(array $attributes) => [
            'annee' => date('Y'),
        ]);
    }

    /**
     * Créer un objectif pour l'année prochaine.
     */
    public function anneeProchaine(): static
    {
        return $this->state(fn(array $attributes) => [
            'annee' => date('Y') + 1,
        ]);
    }

    /**
     * Créer un objectif avec un libellé long.
     */
    public function avecLibelleLong(): static
    {
        return $this->state(fn(array $attributes) => [
            'libelle' => $this->faker->paragraph(3),
            'description' => $this->faker->paragraph(5),
        ]);
    }
}
