<?php

namespace App\Policies;

use App\Models\Exercice;
use App\Models\User;

class ExercicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_exercices');
    }

    public function view(User $user, Exercice $exercice): bool
    {
        return $user->can('view_exercices');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_exercices');
    }

    public function update(User $user, Exercice $exercice): bool
    {
        return $user->can('manage_exercices');
    }

    public function delete(User $user, Exercice $exercice): bool
    {
        return $user->can('manage_exercices');
    }
}
