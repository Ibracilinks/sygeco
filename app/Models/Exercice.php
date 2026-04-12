<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    use HasFactory;

    protected $fillable = [
        'annee',
        'date_debut',
        'date_fin',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    public function objectifs()
    {
        return $this->hasMany(Objectif::class);
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('annee');
    }

    public static function actifCourant(): ?self
    {
        return static::query()->actif()->ordered()->first();
    }
}
