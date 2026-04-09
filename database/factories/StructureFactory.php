<?php

namespace Database\Factories;

use App\Models\Structure;
use Illuminate\Database\Eloquent\Factories\Factory;

class StructureFactory extends Factory
{
    protected $model = Structure::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('STR_????')),
            'libelle' => $this->faker->company(),
            'type' => 'departement',
            'parent_id' => null,
            'responsable_nom' => $this->faker->name(),
            'responsable_email' => $this->faker->unique()->safeEmail(),
            'is_active' => true,
        ];
    }

    /**
     * State: Direction
     */
    public function direction()
    {
        return $this->state(fn() => [
            'code' => 'DIR_CANAM',
            'libelle' => 'Direction Générale CANAM',
            'type' => 'direction',
            'responsable_nom' => 'Dr. Koné Ibrahim',
            'responsable_email' => 'direction@canam.ml',
        ]);
    }

    /**
     * State: Département
     */
    public function departement($parentId = null)
    {
        return $this->state(fn() => [
            'code' => 'DBCGOQ',
            'libelle' => 'Direction du Budget, Contrôle de Gestion et Organisation/Qualité',
            'type' => 'departement',
            'parent_id' => $parentId,
            'responsable_nom' => 'Mme. Diop Fatou',
            'responsable_email' => 'dbcgoq@canam.ml',
        ]);
    }

    /**
     * State: Bureau régional
     */
    public function bureauRegional($region, $parentId = null)
    {
        $responsables = [
            'BAMAKO' => 'M. Traoré Amadou',
            'KAYES' => 'Mme. Diallo Aissata',
            'SIKASSO' => 'M. Sanogo Mamadou',
            'SEGOU' => 'Mme. Coulibaly Fatoumata',
            'KOUTIALA' => 'M. Koné Drissa',
        ];

        return $this->state(fn() => [
            'code' => 'BR_' . $region,
            'libelle' => 'Bureau Régional de ' . $region,
            'type' => 'bureau_regional',
            'parent_id' => $parentId,
            'responsable_nom' => $responsables[$region] ?? 'M. Default',
            'responsable_email' => strtolower($region) . '@canam.ml',
        ]);
    }
}
