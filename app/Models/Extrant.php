<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Extrant extends Model
{
    use HasFactory, LogsActivityWithDefaults, SoftDeletes;

    protected $fillable = [
        'objectif_id',
        'resultat_id',
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
    public function objectif()
    {
        return $this->belongsTo(Objectif::class);
    }
    public function resultat()
    {
        return $this->belongsTo(Resultat::class);
    }

    public function activites()
    {
        return $this->hasMany(Activite::class);
    }

    public function departements()
    {
        return $this->belongsToMany(Departement::class, 'departement_extrant')->withTimestamps();
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('code');
    }

    // Accesseurs
    public function getFullNameAttribute()
    {
        return $this->code . ' - ' . $this->libelle;
    }

    public function getStatutLabelAttribute()
    {
        return $this->is_active ? '✅ Actif' : '❌ Inactif';
    }

    public function getStatutColorAttribute()
    {
        return $this->is_active ? 'green' : 'red';
    }

    // Méthodes métier
    public function getNbActivitesAttribute()
    {
        return $this->activites()->count();
    }

    public function getBudgetTotalAttribute()
    {
        return $this->activites()->sum('cout');
    }
}
