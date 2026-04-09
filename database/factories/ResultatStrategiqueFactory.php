<?php

namespace Database\Factories;

use App\Models\ObjectifStrategique;
use App\Models\ResultatStrategique;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultatStrategiqueFactory extends Factory
{
    protected $model = ResultatStrategique::class;

    private static $counter = 0;
    private static $resultatsPredefinis = [];

    public function definition(): array
    {
        self::$counter++;

        // Si pas encore chargés, initialiser les résultats prédéfinis
        if (empty(self::$resultatsPredefinis)) {
            self::$resultatsPredefinis = $this->getPredefinedResults();
        }

        $objectif = ObjectifStrategique::inRandomOrder()->first()
            ?? ObjectifStrategique::factory()->create();

        // Utiliser un résultat prédéfini aléatoire ou en générer un nouveau
        $predefini = $this->faker->boolean(30) ? $this->faker->randomElement(self::$resultatsPredefinis) : null;

        return [
            'objectif_strategique_id' => $objectif->id,
            'code' => $this->generateCode($objectif->code, self::$counter),
            'libelle' => $predefini['libelle'] ?? $this->generateRandomLibelle(),
            'description' => $predefini['description'] ?? $this->generateRandomDescription(),
            'ordre' => self::$counter,
            'is_active' => $this->faker->boolean(85),
        ];
    }

    /**
     * Résultats prédéfinis réalistes
     */
    private function getPredefinedResults(): array
    {
        return [
            [
                'libelle' => 'Mettre en place un système de suivi des indicateurs clés',
                'description' => 'Déployer un tableau de bord permettant le suivi en temps réel des indicateurs de performance.'
            ],
            [
                'libelle' => 'Former 100% du personnel aux nouveaux processus',
                'description' => 'Organiser des sessions de formation pour l\'ensemble du personnel sur les nouveaux processus.'
            ],
            [
                'libelle' => 'Digitaliser l\'ensemble des procédures administratives',
                'description' => 'Convertir toutes les procédures papier en formats numériques pour plus d\'efficacité.'
            ],
            [
                'libelle' => 'Réduire les délais de traitement de 30%',
                'description' => 'Optimiser les processus pour réduire significativement les délais de traitement des dossiers.'
            ],
            [
                'libelle' => 'Atteindre un taux de satisfaction client de 90%',
                'description' => 'Mettre en place des enquêtes de satisfaction et améliorer la qualité du service client.'
            ],
            [
                'libelle' => 'Certifier l\'organisme selon la norme ISO 9001',
                'description' => 'Préparer et obtenir la certification qualité ISO 9001.'
            ],
            [
                'libelle' => 'Développer une application mobile pour les clients',
                'description' => 'Créer et déployer une application mobile permettant aux clients d\'accéder aux services.'
            ],
            [
                'libelle' => 'Réaliser des économies de 15% sur le budget',
                'description' => 'Identifier et mettre en œuvre des actions d\'optimisation budgétaire.'
            ],
            [
                'libelle' => 'Recruter 10 nouveaux collaborateurs qualifiés',
                'description' => 'Lancer un processus de recrutement pour renforcer les équipes.'
            ],
            [
                'libelle' => 'Lancer un nouveau service innovant',
                'description' => 'Étudier, développer et lancer un nouveau service répondant aux besoins des clients.'
            ],
        ];
    }

    private function generateRandomLibelle(): string
    {
        $actions = [
            'Développer',
            'Améliorer',
            'Optimiser',
            'Renforcer',
            'Accélérer',
            'Simplifier',
            'Digitaliser',
            'Automatiser',
            'Sécuriser',
            'Moderniser'
        ];

        $objects = [
            'le processus de validation',
            'le système d\'information',
            'la communication interne',
            'la gestion des ressources',
            'le contrôle qualité',
            'le reporting',
            'la base de données',
            'l\'interface utilisateur',
            'le workflow',
            'la chaîne de traitement'
        ];

        return $this->faker->randomElement($actions) . ' ' . $this->faker->randomElement($objects);
    }

    private function generateRandomDescription(): string
    {
        return "Ce résultat vise à améliorer la performance globale de l'organisation en " .
            strtolower($this->faker->sentence(5)) . ".";
    }

    private function generateCode(string $objectifCode, int $counter): string
    {
        $objectifNum = substr($objectifCode, 3);
        return 'RS_' . $objectifNum . '_' . str_pad($counter, 3, '0', STR_PAD_LEFT);
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

    public function forObjectif(ObjectifStrategique $objectif): static
    {
        return $this->state(fn(array $attributes) => [
            'objectif_strategique_id' => $objectif->id,
        ]);
    }
}
