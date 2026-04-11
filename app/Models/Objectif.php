<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objectif extends Model
{
    use HasFactory, SoftDeletes;

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
    public function extrants()
    {
        return $this->hasMany(Extrant::class);
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
        return $this->extrants->sum(function ($extrant) {
            return $extrant->activites->sum('cout');
        });
    }

    public function getNbExtrantsAttribute()
    {
        return $this->extrants->count();
    }

    public function getNbActivitesAttribute()
    {
        return $this->extrants->sum(function ($extrant) {
            return $extrant->activites->count();
        });
    }
}
