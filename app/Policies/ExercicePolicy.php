<?php

namespace App\Policies;

use App\Models\Exercice;
use App\Models\User;

class ExercicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_exercices') || $user->can('manage_exercices');
    }

    public function view(User $user, Exercice $exercice): bool
    {
        return $user->can('view_exercices') || $user->can('manage_exercices');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_exercices');
    }

    public function update(User $user, Exercice $exercice): bool
    {
        return $user->can('manage_exercices');
    }

    /** Le contrôle de gestion gère les exercices sans pouvoir les supprimer. */
    public function delete(User $user, Exercice $exercice): bool
    {
        return $user->can('delete_exercices');
    }

    /**
     * Annoncer l'ouverture de la saisie (mail à tous les comptes) : réservé au
     * superadmin et à la DBCGOQ (Gate::before).
     */
    public function annoncer(User $user, Exercice $exercice): bool
    {
        return $user->hasRole('superadmin');
    }
}
