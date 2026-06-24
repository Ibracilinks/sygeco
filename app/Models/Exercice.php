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
        'date_debut_mi_parcours',
        'date_fin_mi_parcours',
        'mi_parcours_notifiee_le',
        'date_debut_evaluation',
        'date_fin_evaluation',
        'evaluation_notifiee_le',
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
            'date_debut_mi_parcours' => 'date',
            'date_fin_mi_parcours' => 'date',
            'mi_parcours_notifiee_le' => 'datetime',
            'date_debut_evaluation' => 'date',
            'date_fin_evaluation' => 'date',
            'evaluation_notifiee_le' => 'datetime',
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

    /**
     * Vérifie si la date du jour est comprise dans une fenêtre [début, fin].
     */
    protected function dansFenetre($debut, $fin): bool
    {
        if (! $debut || ! $fin) {
            return false;
        }

        $aujourdhui = now()->startOfDay();

        return $aujourdhui->betweenIncluded(
            $debut->copy()->startOfDay(),
            $fin->copy()->startOfDay()
        );
    }

    /**
     * La fenêtre de saisie du mi-parcours est-elle ouverte aujourd'hui ?
     */
    public function enPeriodeMiParcours(): bool
    {
        return $this->dansFenetre($this->date_debut_mi_parcours, $this->date_fin_mi_parcours);
    }

    /**
     * La fenêtre d'évaluation de fin d'exercice est-elle ouverte aujourd'hui ?
     */
    public function enPeriodeEvaluation(): bool
    {
        return $this->dansFenetre($this->date_debut_evaluation, $this->date_fin_evaluation);
    }

    /**
     * Une fenêtre de renseignement de l'exécution (mi-parcours ou évaluation) est-elle ouverte ?
     */
    public function enPeriodeSuiviExecution(): bool
    {
        return $this->enPeriodeMiParcours() || $this->enPeriodeEvaluation();
    }

    /**
     * Libellé de la fenêtre de suivi en cours, ou null si aucune n'est ouverte.
     */
    public function periodeSuiviCourante(): ?string
    {
        if ($this->enPeriodeMiParcours()) {
            return 'mi_parcours';
        }

        if ($this->enPeriodeEvaluation()) {
            return 'evaluation';
        }

        return null;
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
