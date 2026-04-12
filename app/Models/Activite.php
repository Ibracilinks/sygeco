<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Activite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'activites';

    protected $fillable = [
        'extrant_id',
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
        'date_saisie' => 'date',
        'date_soumission' => 'datetime',
        'date_validation' => 'datetime',
        'refuse_le' => 'datetime',
    ];

    // Relations
    public function extrant()
    {
        return $this->belongsTo(Extrant::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
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

        return $query->whereHas('extrant.objectif', function ($q) use ($exerciceId) {
            $q->where('exercice_id', $exerciceId);
        });
    }

    public function scopeBrouillon($query)
    {
        return $query->where('statut', 'brouillon');
    }

    public function scopeSoumis($query)
    {
        return $query->where('statut', 'soumis');
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
        if ($this->statut === 'brouillon' && $this->motif_refus) {
            return '❌ Refusé';
        }

        return match ($this->statut) {
            'brouillon' => '📝 Brouillon',
            'soumis' => '⏳ Soumis',
            'valide' => '✅ Validé',
            default => $this->statut
        };
    }

    public function getStatutColorAttribute()
    {
        if ($this->statut === 'brouillon' && $this->motif_refus) {
            return 'red';
        }

        return match ($this->statut) {
            'brouillon' => 'gray',
            'soumis' => 'yellow',
            'valide' => 'green',
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

    public function getDernierMotifRefus()
    {
        return $this->validationHistoriques()
            ->where('action', 'refus')
            ->latest('created_at')
            ->value('commentaire') ?? $this->motif_refus;
    }

    public function peutEtreModifie()
    {
        return $this->statut === 'brouillon';
    }

    public function peutEtreSoumis()
    {
        return $this->statut === 'brouillon';
    }

    public function peutEtreValide()
    {
        return $this->statut === 'soumis';
    }

    public function soumettre()
    {
        if (! $this->peutEtreSoumis()) {
            return false;
        }

        $this->update([
            'statut' => 'soumis',
            'date_soumission' => now(),
            'motif_refus' => null,
            'refuse_le' => null,
            'refuse_par' => null,
        ]);

        $this->logHistorique('soumission', 'brouillon', 'soumis');

        return true;
    }

    public function valider(string $commentaire = null)
    {
        if (! $this->peutEtreValide()) {
            return false;
        }

        $this->update([
            'statut' => 'valide',
            'date_validation' => now(),
            'valide_par' => Auth::id(),
        ]);

        $this->logHistorique('validation', 'soumis', 'valide', $commentaire);

        return true;
    }

    public function refuser(string $motif)
    {
        if (! $this->peutEtreValide()) {
            return false;
        }

        $this->update([
            'statut' => 'brouillon',
            'motif_refus' => $motif,
            'refuse_le' => now(),
            'refuse_par' => Auth::id(),
        ]);

        $this->logHistorique('refus', 'soumis', 'brouillon', $motif);

        return true;
    }

    protected function logHistorique(string $action, string $ancienStatut, string $nouveauStatut, string $commentaire = null, int $utilisateurId = null)
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
