<?php

namespace Database\Factories;

use App\Models\Activite;
use App\Models\Extrant;
use App\Models\Departement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActiviteFactory extends Factory
{
    protected $model = Activite::class;

    public function definition(): array
    {
        $statuts = ['brouillon', 'soumis', 'valide'];
        $trimestres = ['non', 'oui'];

        // Générer des trimestres aléatoires (au moins 1 trimestre sélectionné)
        $trimestre1 = $this->faker->boolean(60) ? 'oui' : 'non';
        $trimestre2 = $this->faker->boolean(60) ? 'oui' : 'non';
        $trimestre3 = $this->faker->boolean(60) ? 'oui' : 'non';
        $trimestre4 = $this->faker->boolean(60) ? 'oui' : 'non';

        // S'assurer qu'au moins un trimestre est sélectionné
        if ($trimestre1 === 'non' && $trimestre2 === 'non' && $trimestre3 === 'non' && $trimestre4 === 'non') {
            $trimestre = rand(1, 4);
            ${"trimestre$trimestre"} = 'oui';
        }

        return [
            'extrant_id' => Extrant::factory(),
            'departement_id' => Departement::get()->random()->id,
            'nom_activite' => $this->generateNomActivite(),
            'indicateur_objectivement_verifiable' => $this->generateIndicateur(),
            'moyen_verification' => $this->generateMoyenVerification(),
            'cout' => $this->faker->randomFloat(2, 500000, 50000000),
            'trimestre_1' => $trimestre1,
            'trimestre_2' => $trimestre2,
            'trimestre_3' => $trimestre3,
            'trimestre_4' => $trimestre4,
            'statut' => $this->faker->randomElement($statuts),
            'saisi_par' => User::factory(),
            'date_saisie' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'commentaires' => $this->faker->optional(0.3)->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-5 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indiquer que l'activité est un brouillon.
     */
    public function brouillon(): static
    {
        return $this->state(fn(array $attributes) => [
            'statut' => 'brouillon',
        ]);
    }

    /**
     * Indiquer que l'activité est soumise.
     */
    public function soumis(): static
    {
        return $this->state(fn(array $attributes) => [
            'statut' => 'soumis',
        ]);
    }

    /**
     * Indiquer que l'activité est validée.
     */
    public function valide(): static
    {
        return $this->state(fn(array $attributes) => [
            'statut' => 'valide',
        ]);
    }

    /**
     * Indiquer que l'activité concerne un trimestre spécifique.
     */
    public function pourTrimestre(int $trimestre): static
    {
        $field = 'trimestre_' . $trimestre;
        return $this->state(fn(array $attributes) => [
            $field => 'oui',
        ]);
    }

    /**
     * Indiquer que l'activité concerne tous les trimestres.
     */
    public function tousTrimestres(): static
    {
        return $this->state(fn(array $attributes) => [
            'trimestre_1' => 'oui',
            'trimestre_2' => 'oui',
            'trimestre_3' => 'oui',
            'trimestre_4' => 'oui',
        ]);
    }

    /**
     * Indiquer que l'activité a un coût spécifique.
     */
    public function avecCout(float $cout): static
    {
        return $this->state(fn(array $attributes) => [
            'cout' => $cout,
        ]);
    }

    /**
     * Indiquer que l'activité a un coût élevé (> 10M).
     */
    public function coutEleve(): static
    {
        return $this->state(fn(array $attributes) => [
            'cout' => $this->faker->randomFloat(2, 10000000, 100000000),
        ]);
    }

    /**
     * Indiquer que l'activité a un coût faible (< 1M).
     */
    public function coutFaible(): static
    {
        return $this->state(fn(array $attributes) => [
            'cout' => $this->faker->randomFloat(2, 100000, 1000000),
        ]);
    }

    /**
     * Indiquer que l'activité est pour un extrant spécifique.
     */
    public function pourExtrant(Extrant $extrant): static
    {
        return $this->state(fn(array $attributes) => [
            'extrant_id' => $extrant->id,
        ]);
    }

    /**
     * Indiquer que l'activité est pour un département spécifique.
     */
    public function pourDepartement(Departement $departement): static
    {
        return $this->state(fn(array $attributes) => [
            'departement_id' => $departement->id,
        ]);
    }

    /**
     * Indiquer que l'activité a été saisie par un utilisateur spécifique.
     */
    public function saisiePar(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'saisi_par' => $user->id,
        ]);
    }

    /**
     * Générer un nom d'activité réaliste.
     */
    private function generateNomActivite(): string
    {
        $actions = [
            'Organiser',
            'Développer',
            'Mettre en place',
            'Former',
            'Auditer',
            'Produire',
            'Analyser',
            'Évaluer',
            'Planifier',
            'Coordonner',
            'Réaliser',
            'Concevoir',
            'Déployer',
            'Suivre',
            'Contrôler',
            'Accompagner',
            'Sensibiliser',
            'Former',
            'Recruter',
            'Équiper'
        ];

        $objects = [
            'un système de reporting',
            'une formation des agents',
            'un audit interne',
            'un rapport d\'activité',
            'un tableau de bord',
            'une enquête de satisfaction',
            'un guide de procédures',
            'une base de données',
            'une campagne de sensibilisation',
            'un atelier de travail',
            'une évaluation des performances',
            'un plan d\'action',
            'une réunion de coordination',
            'un comité de suivi',
            'une mission de contrôle',
            'une session de formation',
            'un atelier de renforcement',
            'une étude de faisabilité'
        ];

        return $this->faker->randomElement($actions) . ' ' . $this->faker->randomElement($objects);
    }

    /**
     * Générer un indicateur réaliste.
     */
    private function generateIndicateur(): string
    {
        $indicators = [
            'Taux de réalisation (%)',
            'Nombre de sessions réalisées',
            'Taux de satisfaction (%)',
            'Délai moyen de traitement (jours)',
            'Budget consommé (FCFA)',
            'Nombre de participants formés',
            'Taux de conformité (%)',
            'Nombre de rapports produits',
            'Taux d\'exécution (%)',
            'Nombre d\'audits réalisés',
            'Taux de couverture (%)',
            'Nombre de bénéficiaires touchés',
            'Qualité des livrables (note/10)',
            'Respect des délais (%)'
        ];

        return $this->faker->randomElement($indicators);
    }

    /**
     * Générer un moyen de vérification réaliste.
     */
    private function generateMoyenVerification(): string
    {
        $moyens = [
            'Rapport d\'activité',
            'Liste de présence',
            'PV de réunion',
            'Facture et justificatifs',
            'Compte-rendu',
            'Attestation de formation',
            'Rapport d\'audit',
            'Base de données',
            'Photos et vidéos',
            'Certificat de réalisation',
            'Tableau de bord',
            'Relevé de notes',
            'Procès-verbal',
            'État des lieux'
        ];

        return $this->faker->randomElement($moyens);
    }
}
