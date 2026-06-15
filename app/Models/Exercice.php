<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    use HasFactory, LogsActivityWithDefaults;

    protected $fillable = [
        'annee',
        'date_debut',
        'date_fin',
        'date_ouverture_saisie',
        'date_limite_saisie',
        'ouverture_notifiee_le',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_ouverture_saisie' => 'date',
            'date_limite_saisie' => 'date',
            'ouverture_notifiee_le' => 'datetime',
        ];
    }

    public function objectifs()
    {
        return $this->hasMany(Objectif::class);
    }

    public function relances()
    {
        return $this->hasMany(ExerciceRelance::class);
    }

    /**
     * Nombre de jours restant avant la date limite de saisie (négatif si dépassée).
     */
    public function joursAvantLimite(): ?int
    {
        if (! $this->date_limite_saisie) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->date_limite_saisie->copy()->startOfDay(), false);
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
