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

        return $this->surSaPartDuPta($user, $activite);
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

        return $this->surSaPartDuPta($user, $activite);
    }

    public function submit(User $user, Activite $activite): bool
    {
        if ($user->hasRole('dbcgoq')) {
            return $activite->peutEtreSoumis();
        }

        if ($user->isServiceControleGestion()) {
            return $activite->peutEtreSoumis();
        }

        if (! $activite->peutEtreSoumis()) {
            return false;
        }

        // Le responsable de programme soumet les activités de son entité et des
        // services qu'elle chapeaute, dont celles saisies par ses chefs de service.
        if ($user->hasRole('responsable-programme')) {
            return VisibiliteActivites::dansPerimetre($user, $activite);
        }

        // Un service rattaché directement à la DG envoie ses activités en son nom :
        // son chef de service les soumet.
        // Ailleurs, le chef de service et la cellule planification saisissent et
        // modifient seulement : la soumission revient au responsable de programme.
        return $user->hasRole('chef-service')
            && (int) $user->departement_id === (int) $activite->departement_id
            && (bool) $user->departement?->estServiceRattacheDg();
    }

    /**
     * Modifier / supprimer une activité de son périmètre de saisie :
     * - le responsable de programme, sur son entité et ses sous-entités, jusqu'à
     *   l'arbitrage (brouillon, rejetée ou en attente de la décision de la DBCGOQ) ;
     * - le chef de service et la cellule planification, sur leur entité, tant que
     *   l'activité n'est pas soumise (brouillon ou rejetée).
     */
    private function surSaPartDuPta(User $user, Activite $activite): bool
    {
        if ($user->hasRole('responsable-programme')) {
            return $activite->estModifiableAvantArbitrage()
                && VisibiliteActivites::dansPerimetre($user, $activite);
        }

        return $user->hasAnyRole(['chef-service', 'agent-planification'])
            && (int) $user->departement_id === (int) $activite->departement_id
            && $activite->peutEtreModifie();
    }
}
