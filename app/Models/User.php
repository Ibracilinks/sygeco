<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, LogsActivityWithDefaults, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'departement_id',
        'poste',
        'telephone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // Relations
    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    // Vérification des rôles
    public function isSuperadmin()
    {
        return $this->hasRole('superadmin');
    }

    public function isDbcgoq()
    {
        return $this->hasRole('dbcgoq');
    }

    public function isChef()
    {
        return $this->hasRole('chef');
    }

    public function isAgent()
    {
        return $this->hasRole('agent');
    }

    /**
     * Cellule planification : saisit et soumet le PTA de son entité.
     */
    public function isAgentPlanification()
    {
        return $this->hasRole('agent-planification');
    }

    /**
     * Cellule suivi & évaluation : renseigne l'exécution et les évaluations.
     */
    public function isSuiviEvaluation()
    {
        return $this->hasRole('suivi-evaluation');
    }

    /**
     * Chargé des missions : ne travaille que sur le module Missions.
     */
    public function isServiceBudget()
    {
        return $this->hasRole('service-budget');
    }

    /**
     * Profils qui suivent le PTA (programmation, arbitrage, évaluation). Les autres
     * — le chargé des missions aujourd'hui — n'ont ni tableau de bord ni menu
     * « Planification Stratégique ». Mêmes rôles que la route activites.*.
     */
    public function suitLePta(): bool
    {
        return $this->hasAnyRole(['superadmin', 'dbcgoq', 'chef', 'agent', 'agent-planification', 'suivi-evaluation']);
    }

    /**
     * Entités dont l'utilisateur est le chef hiérarchique direct,
     * c'est-à-dire les enfants de son entité de rattachement.
     * Le flux d'approbation est montant : un chef valide les
     * soumissions des entités qu'il chapeaute.
     */
    public function entitesSupervisees()
    {
        if (! $this->isChef() || $this->departement_id === null) {
            return Departement::query()->whereRaw('1 = 0');
        }

        return Departement::query()->where('parent_id', $this->departement_id);
    }

    /**
     * Périmètre de visibilité des activités (par departement_id) :
     * - un chef voit son entité ET toutes les entités situées en dessous (sous-arbre) ;
     * - un agent (y compris agent-planification / suivi-evaluation) ne voit que sa propre entité ;
     * - les autres (dbcgoq, superadmin…) ne sont pas restreints → null.
     *
     * @return array<int, int>|null null = aucune restriction
     */
    public function perimetreActivitesIds(): ?array
    {
        // Le superadmin et le dbcgoq voient tout, même s'ils sont rattachés à une entité
        // et cumulent un rôle chef ou agent (cohérent avec ActivitePolicy::view()).
        if ($this->hasRole('superadmin') || $this->hasRole('dbcgoq')) {
            return null;
        }

        if ($this->departement_id === null) {
            return null;
        }

        if ($this->isChef()) {
            return $this->departement?->sousArbreIds()
                ?? Departement::find($this->departement_id)?->sousArbreIds()
                ?? [$this->departement_id];
        }

        // Agent, cellule planification et cellule suivi & évaluation travaillent
        // tous les trois sur le périmètre de leur seule entité de rattachement.
        if ($this->isAgent() || $this->isAgentPlanification() || $this->isSuiviEvaluation()) {
            return [$this->departement_id];
        }

        return null;
    }
}
