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
        'exercice_id',
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
    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
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

    public function scopeByAnnee($query, $annee)
    {
        return $query->where('annee', $annee);
    }

    public function scopeForExercice($query, ?int $exerciceId)
    {
        if ($exerciceId === null) {
            return $query;
        }

        return $query->where('exercice_id', $exerciceId);
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
