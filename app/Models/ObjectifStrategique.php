<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObjectifStrategique extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'ordre',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relation avec les résultats stratégiques
     */
    public function resultatsStrategiques()
    {
        return $this->hasMany(ResultatStrategique::class);
    }

    /**
     * Alias pour la relation (pour faciliter l'utilisation)
     */
    public function resultats()
    {
        return $this->resultatsStrategiques();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
