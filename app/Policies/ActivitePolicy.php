<?php

namespace App\Policies;

use App\Models\Activite;
use App\Models\User;

class ActivitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_activites');
    }

    public function view(User $user, Activite $activite): bool
    {
        if ($user->hasRole('dbcgoq')) {
            return true;
        }

        return ($user->hasRole('chef_departement') || $user->hasRole('agent'))
            && (int) $user->departement_id === (int) $activite->departement_id;
    }

    public function create(User $user): bool
    {
        return $user->can('create_activites');
    }

    public function update(User $user, Activite $activite): bool
    {
        if (! $user->can('edit_activites')) {
            return false;
        }

        if ($user->hasRole('dbcgoq')) {
            return true;
        }

        return $user->hasRole('chef_departement')
            && (int) $user->departement_id === (int) $activite->departement_id
            && $activite->peutEtreModifie();
    }

    public function delete(User $user, Activite $activite): bool
    {
        if (! $user->can('delete_activites')) {
            return false;
        }

        if ($user->hasRole('dbcgoq')) {
            return true;
        }

        return $user->hasRole('chef_departement')
            && (int) $user->departement_id === (int) $activite->departement_id
            && $activite->peutEtreModifie();
    }

    public function submit(User $user, Activite $activite): bool
    {
        if ($user->hasRole('dbcgoq')) {
            return $activite->peutEtreSoumis();
        }

        return $user->hasRole('chef_departement')
            && (int) $user->departement_id === (int) $activite->departement_id
            && $activite->peutEtreSoumis();
    }
}
