<?php

namespace Database\Factories;

use App\Models\Departement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $postes = [
            'Chef de département',
            'Chef de service',
            'Agent de saisie',
            'Analyste',
            'Superviseur',
            'Coordinateur',
            'Assistant',
            'Responsable administratif',
            'Gestionnaire',
            'Contrôleur de gestion'
        ];

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'departement_id' => Departement::factory(),
            'poste' => fake()->randomElement($postes),
            'telephone' => fake()->optional(0.8)->phoneNumber(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn(array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the user belongs to a specific department.
     */
    public function dansDepartement(Departement $departement): static
    {
        return $this->state(fn(array $attributes) => [
            'departement_id' => $departement->id,
        ]);
    }

    /**
     * Indicate that the user has a specific poste.
     */
    public function avecPoste(string $poste): static
    {
        return $this->state(fn(array $attributes) => [
            'poste' => $poste,
        ]);
    }

    /**
     * Indicate that the user has the DBCGOQ role.
     */
    public function dbcgoq(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'dbcgoq']);
            $user->assignRole($role);
        });
    }

    /**
     * Indicate that the user has the Chef Département role.
     */
    public function chefDepartement(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'chef_departement']);
            $user->assignRole($role);
        });
    }

    /**
     * Indicate that the user has the Agent role.
     */
    public function agent(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'agent']);
            $user->assignRole($role);
        });
    }

    /**
     * Indicate that the user has a specific role.
     */
    public function avecRole(string $roleName): static
    {
        return $this->afterCreating(function (User $user) use ($roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $user->assignRole($role);
        });
    }

    /**
     * Indicate that the user has multiple roles.
     */
    public function avecRoles(array $roleNames): static
    {
        return $this->afterCreating(function (User $user) use ($roleNames) {
            foreach ($roleNames as $roleName) {
                $role = Role::firstOrCreate(['name' => $roleName]);
                $user->assignRole($role);
            }
        });
    }

    /**
     * Create an admin user (DBCGOQ).
     */
    public function admin(): static
    {
        return $this->dbcgoq();
    }
}
