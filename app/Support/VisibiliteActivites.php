<?php

namespace App\Support;

use App\Models\Activite;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Règles de visibilité des activités, en un seul endroit : la programmation, le
 * tableau de bord, l'évaluation et la policy doivent répondre la même chose.
 *
 * - superadmin, dbcgoq et service contrôle de gestion : tout, brouillons compris,
 *   pour toutes les entités ;
 * - cellule planification : voit tout elle aussi, mais ne travaille (pièces
 *   jointes, soumission) que sur son entité — cf. dansPerimetre() ;
 * - agent : ses seules saisies, et rien d'autre ;
 * - chef : son entité et son sous-arbre ;
 * - cellules planification et suivi & évaluation : leur entité de rattachement.
 *
 * Les rôles cumulés sont départagés dans cet ordre : un utilisateur à la fois
 * dbcgoq et agent est traité en dbcgoq.
 *
 * En consultation (liste de programmation, fiche), toute activité validée est en
 * plus visible de tous, en lecture seule : le périmètre ci-dessus continue de
 * borner l'évaluation, les pièces jointes et le tableau de bord.
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
        if ($user === null || self::voitTout($user)) {
            return;
        }

        if (self::estSimpleAgent($user)) {
            $query->where('activites.saisi_par', $user->getKey());

            return;
        }

        if ($perimetre = $user->perimetreActivitesIds()) {
            $query->whereIn('activites.departement_id', $perimetre);
        }
    }

    /**
     * Variante de `appliquer()` pour la consultation : le périmètre habituel, plus
     * toutes les activités validées.
     *
     * @param  Builder|\Illuminate\Database\Query\Builder  $query
     */
    public static function appliquerConsultation($query, ?User $user): void
    {
        // Sans restriction de périmètre, il n'y a rien à élargir (et un groupe OR vide
        // serait ignoré par le builder, ne laissant que les activités validées).
        if ($user === null || ! self::estRestreint($user)) {
            return;
        }

        $query->where(fn ($q) => $q
            ->where('activites.statut', 'valide')
            ->orWhere(fn ($perimetre) => self::appliquer($perimetre, $user)));
    }

    /**
     * Pendant unitaire de `appliquerConsultation()`, pour la policy.
     */
    public static function peutVoir(User $user, Activite $activite): bool
    {
        return self::voitTout($user)
            || $activite->statut === 'valide'
            || self::dansPerimetre($user, $activite);
    }

    /**
     * Pendant unitaire de `appliquer()` : l'activité relève-t-elle du périmètre de
     * travail de l'utilisateur ? Sert aux actions (évaluation, pièces jointes).
     */
    public static function dansPerimetre(User $user, Activite $activite): bool
    {
        if (self::travailleSurTout($user)) {
            return true;
        }

        if (self::estSimpleAgent($user)) {
            return (int) $activite->saisi_par === (int) $user->getKey();
        }

        $perimetre = $user->perimetreActivitesIds();

        return $perimetre !== null
            && in_array((int) $activite->departement_id, $perimetre, true);
    }

    /**
     * Signature du périmètre de visibilité, pour les caches. Deux utilisateurs ne
     * partagent une entrée que s'ils voient exactement les mêmes activités : ceux
     * dont la vue dépend de leur identité (agent) ont donc leur propre clé.
     */
    public static function signature(?User $user): string
    {
        if ($user === null || self::voitTout($user)) {
            return 'global';
        }

        if (self::estSimpleAgent($user)) {
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
     * `appliquer()` restreint-il la requête pour cet utilisateur ? Mêmes branches,
     * même ordre.
     */
    private static function estRestreint(User $user): bool
    {
        if (self::voitTout($user)) {
            return false;
        }

        return self::estSimpleAgent($user)
            || (bool) $user->perimetreActivitesIds();
    }

    /** Rôles qui voient toutes les activités de toutes les entités. */
    private static function voitTout(User $user): bool
    {
        return self::travailleSurTout($user) || $user->hasRole('agent-planification');
    }

    /**
     * Rôles dont le périmètre de travail (pièces jointes, évaluation, soumission)
     * couvre toutes les entités. La cellule planification voit tout sans y travailler.
     */
    private static function travailleSurTout(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'dbcgoq', 'service-controle-gestion']);
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
