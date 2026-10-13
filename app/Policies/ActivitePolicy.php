<?php

namespace App\Policies;

use App\Models\Activite;
use App\Models\User;
use App\Support\ActiveExercice;
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

    /**
     * Évaluer : réservé au périmètre de travail (le dbcgoq passe par Gate::before).
     */
    public function evaluer(User $user, Activite $activite): bool
    {
        return VisibiliteActivites::dansPerimetre($user, $activite);
    }

    /**
     * Joindre un fichier : réservé au périmètre de travail, même si toute activité
     * validée est consultable par tous.
     */
    public function joindre(User $user, Activite $activite): bool
    {
        return $user->canAny(['edit_activites', 'validate_activites', 'evaluate_activites'])
            && VisibiliteActivites::dansPerimetre($user, $activite);
    }

    /**
     * Passé la date limite de saisie de l'exercice actif, seuls le dbcgoq (Gate::before)
     * et le service contrôle de gestion peuvent encore programmer une activité.
     */
    public function create(User $user): bool
    {
        if (! $user->can('create_activites')) {
            return false;
        }

        if ($user->isServiceControleGestion()) {
            return true;
        }

        return ! (ActiveExercice::model()?->saisieCloturee() ?? false);
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

        return $user->hasAnyRole(['responsable-programme', 'chef-service', 'agent-planification'])
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

        return $user->hasAnyRole(['responsable-programme', 'chef-service', 'agent-planification'])
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

        return $user->hasAnyRole(['responsable-programme', 'chef-service', 'agent-planification'])
            && (int) $user->departement_id === (int) $activite->departement_id
            && $activite->peutEtreSoumis();
    }
}
