<?php

namespace App\Support;

use App\Models\Activite;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Règles de visibilité des activités, en un seul endroit : la programmation, le
 * tableau de bord, l'évaluation et la policy doivent répondre la même chose.
 *
 * - superadmin et service contrôle de gestion : tout ;
 * - agent : ses seules saisies, et rien d'autre ;
 * - dbcgoq : tout sauf les brouillons d'autrui, qui ne le concernent pas tant
 *   qu'ils ne sont pas soumis ;
 * - chef : son entité et son sous-arbre ;
 * - cellules planification et suivi & évaluation : leur entité de rattachement.
 *
 * Les rôles cumulés sont départagés dans cet ordre : un utilisateur à la fois
 * dbcgoq et agent est traité en dbcgoq.
 */
class VisibiliteActivites
{
    /**
     * Restreint une requête aux activités visibles par l'utilisateur. Fonctionne
     * aussi bien sur un builder Eloquent que sur le Query Builder du tableau de
     * bord : les colonnes sont qualifiées pour rester valables dans une jointure.
     *
     * @param  Builder|\Illuminate\Database\Query\Builder  $query
     */
    public static function appliquer($query, ?User $user): void
    {
        // Hors requête HTTP authentifiée (tests, commandes), on ne restreint rien :
        // c'est l'appelant qui porte alors la responsabilité du périmètre.
        if ($user === null || $user->hasAnyRole(['superadmin', 'service-controle-gestion'])) {
            return;
        }

        if (self::estSimpleAgent($user)) {
            $query->where('activites.saisi_par', $user->getKey());

            return;
        }

        if ($user->hasRole('dbcgoq')) {
            $query->where(fn ($q) => $q
                ->where('activites.statut', '!=', 'brouillon')
                ->orWhere('activites.saisi_par', $user->getKey()));

            return;
        }

        if ($perimetre = $user->perimetreActivitesIds()) {
            $query->whereIn('activites.departement_id', $perimetre);
        }
    }

    /**
     * Pendant unitaire de `appliquer()`, pour la policy.
     */
    public static function peutVoir(User $user, Activite $activite): bool
    {
        if ($user->hasAnyRole(['superadmin', 'service-controle-gestion'])) {
            return true;
        }

        if (self::estSimpleAgent($user)) {
            return (int) $activite->saisi_par === (int) $user->getKey();
        }

        if ($user->hasRole('dbcgoq')) {
            return $activite->statut !== 'brouillon'
                || (int) $activite->saisi_par === (int) $user->getKey();
        }

        $perimetre = $user->perimetreActivitesIds();

        return $perimetre !== null
            && in_array((int) $activite->departement_id, $perimetre, true);
    }

    /**
     * Signature du périmètre de visibilité, pour les caches. Deux utilisateurs ne
     * partagent une entrée que s'ils voient exactement les mêmes activités : ceux
     * dont la vue dépend de leur identité (agent, dbcgoq) ont donc leur propre clé.
     */
    public static function signature(?User $user): string
    {
        if ($user === null || $user->hasAnyRole(['superadmin', 'service-controle-gestion'])) {
            return 'global';
        }

        if (self::estSimpleAgent($user) || $user->hasRole('dbcgoq')) {
            return 'u'.$user->getKey();
        }

        $perimetre = $user->perimetreActivitesIds();

        if ($perimetre === null) {
            return 'global';
        }

        sort($perimetre);

        return 'p'.substr(md5(implode(',', $perimetre)), 0, 12);
    }

    /**
     * Agent « simple » : ni dbcgoq ni chef par ailleurs. Le cumul de rôles est
     * courant en base, et le rôle le plus large doit l'emporter.
     */
    private static function estSimpleAgent(User $user): bool
    {
        return $user->hasRole('chef-service')
            && ! $user->hasRole('dbcgoq')
            && ! $user->hasRole('responsable-programme');
    }
}
