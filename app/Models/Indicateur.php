<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indicateur extends Model
{
    use HasFactory, LogsActivityWithDefaults, SoftDeletes;

    protected $fillable = [
        'objectif_id',
        'code',
        'libelle',
        'description',
        'type',
        'unite',
        'formule_calcul',
        'cible',
        'seuil_alerte',
        'periodicite',
        'sens',
        'source_donnee',
        'ordre',
        'is_active',
    ];

    protected $casts = [
        'cible' => 'decimal:2',
        'seuil_alerte' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relations
    public function objectif()
    {
        return $this->belongsTo(Objectif::class);
    }

    public function valeurs()
    {
        return $this->hasMany(IndicateurValeur::class);
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('code');
    }

    // Accesseurs
    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            'performance' => '📊 Performance',
            'gestion' => '📈 Gestion',
            'qualite' => '⭐ Qualité',
            'efficacite' => '⚡ Efficacité',
            default => $this->type
        };
    }

    public function getTypeColorAttribute()
    {
        return match ($this->type) {
            'performance' => 'blue',
            'gestion' => 'green',
            'qualite' => 'purple',
            'efficacite' => 'orange',
            default => 'gray'
        };
    }

    public function getPeriodiciteLabelAttribute()
    {
        return match ($this->periodicite) {
            'mensuel' => 'Mensuel',
            'trimestriel' => 'Trimestriel',
            'semestriel' => 'Semestriel',
            'annuel' => 'Annuel',
            default => $this->periodicite
        };
    }

    public function getSensIconAttribute()
    {
        return $this->sens === 'hausse' ? '📈' : '📉';
    }

    // Méthodes métier
    public function getDerniereValeur($departementId = null)
    {
        $query = $this->valeurs();
        if ($departementId) {
            $query->where('departement_id', $departementId);
        }
        return $query->orderBy('periode', 'desc')->first();
    }

    public function getTauxRealisation($departementId = null)
    {
        $derniereValeur = $this->getDerniereValeur($departementId);
        if (!$derniereValeur || !$this->cible) {
            return null;
        }

        $taux = ($derniereValeur->valeur_realisee / $this->cible) * 100;
        return round($taux, 1);
    }

    public function getStatutRealisation($departementId = null)
    {
        $taux = $this->getTauxRealisation($departementId);
        if ($taux === null) return 'non_renseigne';

        if ($taux >= 100) return 'atteint';
        if ($taux >= 75) return 'bon';
        if ($taux >= 50) return 'moyen';
        return 'critique';
    }
}
