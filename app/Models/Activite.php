<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'commentaires',
    ];

    protected $casts = [
        'cout' => 'decimal:2',
        'date_saisie' => 'date',
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
        return match ($this->statut) {
            'brouillon' => '📝 Brouillon',
            'soumis' => '⏳ Soumis',
            'valide' => '✅ Validé',
            default => $this->statut
        };
    }

    public function getStatutColorAttribute()
    {
        return match ($this->statut) {
            'brouillon' => 'gray',
            'soumis' => 'yellow',
            'valide' => 'green',
            default => 'gray'
        };
    }

    // Méthodes métier
    public function soumettre()
    {
        if ($this->statut === 'brouillon') {
            $this->update(['statut' => 'soumis']);
            return true;
        }
        return false;
    }

    public function valider()
    {
        if ($this->statut === 'soumis') {
            $this->update(['statut' => 'valide']);
            return true;
        }
        return false;
    }

    public function estModifiable()
    {
        return $this->statut === 'brouillon';
    }
}
