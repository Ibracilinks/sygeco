<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resultat extends Model
{
    use HasFactory, LogsActivityWithDefaults, SoftDeletes;

    protected $table = 'resultats';

    protected $fillable = [
        'exercice_id',
        'objectif_id',
        'code',
        'libelle',
        'description',
        'ordre',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }

    public function objectif()
    {
        return $this->belongsTo(Objectif::class);
    }

    public function extrants()
    {
        return $this->hasMany(Extrant::class);
    }

    public function departements()
    {
        return $this->belongsToMany(Departement::class, 'departement_resultat')->withTimestamps();
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Résultats / extrants d'un exercice : ils appartiennent au couple
     * « exercice + objectif », un objectif pluriannuel en portant un jeu par exercice.
     */
    public function scopeForExercice($query, ?int $exerciceId)
    {
        if ($exerciceId === null) {
            return $query;
        }

        return $query->where($query->getModel()->qualifyColumn('exercice_id'), $exerciceId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('code');
    }
}
