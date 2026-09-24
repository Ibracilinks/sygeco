<?php

namespace App\Policies;

use App\Models\Activite;
use App\Models\User;
use App\Support\VisibiliteActivites;

class ActivitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_activites');
    }

    public function view(User $user, Activite $activite): bool
    {
        return VisibiliteActivites::peutVoir($user, $activite);
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

        // Le service contrôle de gestion met à jour le PTA de toutes les entités.
        if ($user->isServiceControleGestion()) {
            return $activite->peutEtreModifie();
        }

        return $user->hasAnyRole(['responsable-programme', 'agent-planification'])
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

        // Le service contrôle de gestion met à jour le PTA de toutes les entités.
        if ($user->isServiceControleGestion()) {
            return $activite->peutEtreModifie();
        }

        return $user->hasAnyRole(['responsable-programme', 'agent-planification'])
            && (int) $user->departement_id === (int) $activite->departement_id
            && $activite->peutEtreModifie();
    }

    public function submit(User $user, Activite $activite): bool
    {
        if ($user->hasRole('dbcgoq')) {
            return $activite->peutEtreSoumis();
        }

        if ($user->isServiceControleGestion()) {
            return $activite->peutEtreSoumis();
        }

        return $user->hasAnyRole(['responsable-programme', 'agent-planification'])
            && (int) $user->departement_id === (int) $activite->departement_id
            && $activite->peutEtreSoumis();
    }
}
