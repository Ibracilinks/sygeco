<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objectif extends Model
{
    use HasFactory, LogsActivityWithDefaults, SoftDeletes;

    protected $table = 'objectifs';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'annee',
        'statut',
        'ordre',
    ];

    protected $casts = [
        'annee' => 'integer',
    ];

    // Relations

    /**
     * Exercices couverts par l'objectif. Un objectif de plan stratégique en couvre
     * plusieurs (ex. 2026 → 2030) ; un objectif annuel n'en couvre qu'un.
     */
    public function exercices()
    {
        return $this->belongsToMany(Exercice::class, 'exercice_objectif')->withTimestamps();
    }

    public function resultats()
    {
        return $this->hasMany(Resultat::class);
    }

    public function extrants()
    {
        return $this->hasManyThrough(Extrant::class, Resultat::class);
    }

    public function departements()
    {
        return $this->belongsToMany(Departement::class, 'departement_objectif')->withTimestamps();
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Objectifs couvrant l'année donnée (et non plus seulement ceux qui en sont issus).
     */
    public function scopeByAnnee($query, $annee)
    {
        return $query->whereHas('exercices', fn ($q) => $q->where('exercices.annee', $annee));
    }

    public function scopeForExercice($query, ?int $exerciceId)
    {
        if ($exerciceId === null) {
            return $query;
        }

        return $query->whereHas('exercices', fn ($q) => $q->where('exercices.id', $exerciceId));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('code');
    }

    // Accesseurs
    public function getStatutLabelAttribute()
    {
        return match ($this->statut) {
            'actif' => '✅ Actif',
            'inactif' => '❌ Inactif',
            default => $this->statut
        };
    }

    public function getStatutColorAttribute()
    {
        return match ($this->statut) {
            'actif' => 'green',
            'inactif' => 'red',
            default => 'gray'
        };
    }

    /**
     * Période couverte, sous forme « 2026 » ou « 2026-2030 ».
     */
    public function getPeriodeLibelleAttribute(): string
    {
        $annees = $this->relationLoaded('exercices')
            ? $this->exercices->pluck('annee')
            : $this->exercices()->pluck('annee');

        if ($annees->isEmpty()) {
            return (string) $this->annee;
        }

        $min = (int) $annees->min();
        $max = (int) $annees->max();

        return $min === $max ? (string) $min : "{$min}-{$max}";
    }

    public function getEstPluriannuelAttribute(): bool
    {
        return ($this->relationLoaded('exercices') ? $this->exercices->count() : $this->exercices()->count()) > 1;
    }

    // Méthodes métier
    public function getBudgetTotalAttribute()
    {
        $total = 0;
        foreach ($this->resultats as $resultat) {
            foreach ($resultat->extrants as $extrant) {
                $total += $extrant->activites->sum('cout');
            }
        }

        return $total;
    }

    public function getNbResultatsAttribute()
    {
        return $this->resultats()->count();
    }

    public function getNbExtrantsAttribute()
    {
        $total = 0;
        foreach ($this->resultats as $resultat) {
            $total += $resultat->extrants()->count();
        }

        return $total;
    }

    public function getNbActivitesAttribute()
    {
        $total = 0;
        foreach ($this->resultats as $resultat) {
            foreach ($resultat->extrants as $extrant) {
                $total += $extrant->activites()->count();
            }
        }

        return $total;
    }
}
