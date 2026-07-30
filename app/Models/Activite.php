<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Activite extends Model
{
    use HasFactory, LogsActivityWithDefaults, SoftDeletes;

    protected $table = 'activites';

    /**
     * Montant maximum acceptable pour les colonnes monétaires (decimal(15,2)).
     */
    public const MONTANT_MAX = 9999999999999.99;

    protected $fillable = [
        'extrant_id',
        'exercice_id',
        'non_programmee',
        'departement_id',
        'nom_activite',
        'indicateur_objectivement_verifiable',
        'moyen_verification',
        'cout',
        'trimestre_1',
        'trimestre_2',
        'trimestre_3',
        'trimestre_4',
        'statut',
        'statut_execution',
        'execution_commentaire',
        'montant_utilise',
        'valeur_indicateur',
        'execution_maj_le',
        'execution_maj_par',
        'saisi_par',
        'date_saisie',
        'date_soumission',
        'date_validation',
        'valide_par',
        'refuse_le',
        'refuse_par',
        'motif_refus',
        'commentaires',
    ];

    protected $casts = [
        'cout' => 'decimal:2',
        'montant_utilise' => 'decimal:2',
        'valeur_indicateur' => 'decimal:2',
        'non_programmee' => 'boolean',
        'date_saisie' => 'date',
        'date_soumission' => 'datetime',
        'date_validation' => 'datetime',
        'refuse_le' => 'datetime',
        'execution_maj_le' => 'datetime',
    ];

    /**
     * Libellés et couleurs des statuts d'exécution (suivi « Track Activité »).
     */
    public const STATUTS_EXECUTION = [
        'non_realise' => 'Non réalisé',
        'en_cours' => 'En cours',
        'realise' => 'Réalisé',
    ];

    public function getStatutExecutionLabelAttribute(): string
    {
        return self::STATUTS_EXECUTION[$this->statut_execution] ?? 'Non réalisé';
    }

    public function getStatutExecutionCouleurAttribute(): string
    {
        return match ($this->statut_execution) {
            'realise' => 'emerald',
            'en_cours' => 'amber',
            default => 'slate',
        };
    }

    /**
     * Écart entre le budget planifié (cout) et le montant réellement utilisé.
     * Positif = économie, négatif = dépassement. Null si non renseigné.
     */
    public function getEcartBudgetaireAttribute(): ?float
    {
        if ($this->montant_utilise === null) {
            return null;
        }

        return (float) $this->cout - (float) $this->montant_utilise;
    }

    public function executionMajPar()
    {
        return $this->belongsTo(User::class, 'execution_maj_par');
    }

    /**
     * Évaluations de l'activité, une par période (mi-parcours / fin d'année).
     */
    public function evaluations()
    {
        return $this->hasMany(ActiviteEvaluation::class);
    }

    /**
     * Évaluation d'une période donnée, si elle a été saisie.
     */
    public function evaluation(string $periode): ?ActiviteEvaluation
    {
        return $this->relationLoaded('evaluations')
            ? $this->evaluations->firstWhere('periode', $periode)
            : $this->evaluations()->where('periode', $periode)->first();
    }

    /**
     * Exercice rattaché à l'activité (via extrant → objectif).
     */
    public function exercice(): ?Exercice
    {
        // Une activité non programmée est rattachée directement à un exercice ;
        // une activité planifiée l'est via son extrant → objectif.
        if ($this->exercice_id) {
            return $this->exerciceDirect ?? Exercice::find($this->exercice_id);
        }

        return $this->extrant?->objectif?->exercice;
    }

    /**
     * Rattachement direct à l'exercice (activités non programmées).
     */
    public function exerciceDirect()
    {
        return $this->belongsTo(Exercice::class, 'exercice_id');
    }

    /**
     * Filtre sur l'état d'exécution évalué pour une période donnée. Une activité sans
     * évaluation saisie n'est pas « non réalisée » : elle n'est pas encore évaluée
     * (voir scopeSansEvaluation).
     */
    public function scopeParStatutEvaluation($query, string $periode, string $statut)
    {
        return $query->whereHas('evaluations', fn ($q) => $q->where('periode', $periode)->where('statut_execution', $statut));
    }

    /**
     * Activités dont l'évaluation de la période n'a pas encore été renseignée.
     */
    public function scopeSansEvaluation($query, string $periode)
    {
        return $query->whereDoesntHave('evaluations', fn ($q) => $q->where('periode', $periode));
    }

    public function scopeByStatutExecution($query, $statut)
    {
        return $query->where('statut_execution', $statut);
    }

    // Relations
    public function extrant()
    {
        return $this->belongsTo(Extrant::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    /**
     * Départements responsables (many-to-many). `departement_id` reste le département principal.
     */
    public function departements()
    {
        return $this->belongsToMany(Departement::class, 'activite_departement')->withTimestamps();
    }

    public function saisiePar()
    {
        return $this->belongsTo(User::class, 'saisi_par');
    }

    // Scopes
    public function scopeByDepartement($query, $departementId)
    {
        return $query->where('departement_id', $departementId);
    }

    public function scopeByExtrant($query, $extrantId)
    {
        return $query->where('extrant_id', $extrantId);
    }

    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeForExercice($query, ?int $exerciceId)
    {
        if ($exerciceId === null) {
            return $query;
        }

        // Activités planifiées (via extrant → objectif) OU non programmées (lien direct à l'exercice).
        return $query->where(function ($outer) use ($exerciceId) {
            $outer->whereHas('extrant.objectif', function ($q) use ($exerciceId) {
                $q->where('exercice_id', $exerciceId);
            })->orWhere('exercice_id', $exerciceId);
        });
    }

    public function scopeBrouillon($query)
    {
        return $query->where('statut', 'brouillon');
    }

    public function scopeSoumis($query)
    {
        // « Soumis » = en attente de validation (nom de scope conservé pour compatibilité).
        return $query->where('statut', 'en_attente');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeRejete($query)
    {
        return $query->where('statut', 'rejete');
    }

    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopePourTrimestre($query, $trimestre)
    {
        $field = 'trimestre_' . $trimestre;
        return $query->where($field, 'oui');
    }

    // Accesseurs
    public function getTrimestresSelectionnesAttribute()
    {
        $trimestres = [];
        if ($this->trimestre_1 == 'oui') $trimestres[] = 'T1';
        if ($this->trimestre_2 == 'oui') $trimestres[] = 'T2';
        if ($this->trimestre_3 == 'oui') $trimestres[] = 'T3';
        if ($this->trimestre_4 == 'oui') $trimestres[] = 'T4';
        return implode(', ', $trimestres);
    }

    public function getCoutFormateAttribute()
    {
        return number_format($this->cout, 0, ',', ' ') . ' FCFA';
    }

    public function getStatutLabelAttribute()
    {
        return match ($this->statut) {
            'brouillon' => '📝 Brouillon',
            'en_attente', 'soumis' => '⏳ En attente de validation',
            'valide' => '✅ Validé',
            'rejete' => '❌ Rejeté',
            default => $this->statut
        };
    }

    public function getStatutColorAttribute()
    {
        return match ($this->statut) {
            'brouillon' => 'gray',
            'en_attente', 'soumis' => 'yellow',
            'valide' => 'green',
            'rejete' => 'red',
            default => 'gray'
        };
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function refusePar()
    {
        return $this->belongsTo(User::class, 'refuse_par');
    }

    public function validationHistoriques()
    {
        return $this->hasMany(ValidationHistorique::class);
    }

    public function piecesJointes()
    {
        return $this->hasMany(ActivitePieceJointe::class)->latest();
    }

    public function getDernierMotifRefus()
    {
        return $this->validationHistoriques()
            ->where('action', 'refus')
            ->latest('created_at')
            ->value('commentaire') ?? $this->motif_refus;
    }

    public function peutEtreModifie()
    {
        // Une activité rejetée peut être corrigée puis re-soumise.
        return in_array($this->statut, ['brouillon', 'rejete'], true);
    }

    public function peutEtreSoumis()
    {
        return in_array($this->statut, ['brouillon', 'rejete'], true);
    }

    public function peutEtreValide()
    {
        return $this->statut === 'en_attente';
    }

    public function soumettre()
    {
        if (! $this->peutEtreSoumis()) {
            return false;
        }

        $ancienStatut = $this->statut;

        $this->update([
            'statut' => 'en_attente',
            'date_soumission' => now(),
            'motif_refus' => null,
            'refuse_le' => null,
            'refuse_par' => null,
        ]);

        $this->logHistorique('soumission', $ancienStatut, 'en_attente');

        return true;
    }

    public function valider(?string $commentaire = null)
    {
        if (! $this->peutEtreValide()) {
            return false;
        }

        $this->update([
            'statut' => 'valide',
            'date_validation' => now(),
            'valide_par' => Auth::id(),
        ]);

        $this->logHistorique('validation', 'en_attente', 'valide', $commentaire);

        return true;
    }

    public function refuser(string $motif)
    {
        if (! $this->peutEtreValide()) {
            return false;
        }

        $this->update([
            'statut' => 'rejete',
            'motif_refus' => $motif,
            'refuse_le' => now(),
            'refuse_par' => Auth::id(),
        ]);

        $this->logHistorique('refus', 'en_attente', 'rejete', $motif);

        return true;
    }

    /**
     * Destinataires à notifier lors d'un changement de budget (coût) de l'activité :
     * le chef du service concerné (responsable de la structure de l'activité) et le
     * directeur de la Direction Centrale de rattachement.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    public function destinatairesChangementBudget(): \Illuminate\Support\Collection
    {
        $structure = $this->departement;

        if (! $structure) {
            return collect();
        }

        $destinataires = collect();

        // Chef de service : responsable de la structure porteuse de l'activité.
        if ($structure->responsable) {
            $destinataires->push($structure->responsable);
        }

        // Directeur de la Direction Centrale : la structure elle-même si c'en est une,
        // sinon la Direction Centrale la plus proche parmi ses ancêtres.
        $directionCentrale = $structure->type === Departement::TYPE_DEPARTEMENT
            ? $structure
            : $structure->ancetres()->firstWhere('type', Departement::TYPE_DEPARTEMENT);

        if ($directionCentrale && $directionCentrale->responsable) {
            $destinataires->push($directionCentrale->responsable);
        }

        return $destinataires->filter()->unique('id')->values();
    }

    /**
     * Arbitrage budgétaire : modification d'une activité par le responsable avant validation.
     */
    public function arbitrerModification(array $data, ?string $motif = null): void
    {
        $this->update($data);
        $this->logHistorique('arbitrage_modification', $this->statut, $this->statut, $motif);
    }

    /**
     * Arbitrage budgétaire : suppression (archivage) d'une activité.
     */
    public function arbitrerSuppression(?string $motif = null): void
    {
        $this->logHistorique('arbitrage_suppression', $this->statut, $this->statut, $motif);
        $this->delete();
    }

    /**
     * Journalise une action d'arbitrage (ex. fusion) sur l'activité.
     */
    public function journaliserArbitrage(string $action, ?string $motif = null): void
    {
        $this->logHistorique($action, $this->statut, $this->statut, $motif);
    }

    protected function logHistorique(string $action, string $ancienStatut, string $nouveauStatut, ?string $commentaire = null, ?int $utilisateurId = null)
    {
        $this->validationHistoriques()->create([
            'action' => $action,
            'utilisateur_id' => $utilisateurId ?? Auth::id(),
            'commentaire' => $commentaire,
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => $nouveauStatut,
        ]);
    }

    public function estModifiable()
    {
        return $this->peutEtreModifie();
    }
}
