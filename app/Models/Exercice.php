<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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

    /**
     * Objectifs couverts par l'exercice. Un objectif pluriannuel (plan stratégique)
     * est rattaché à chacun des exercices de sa période.
     */
    public function objectifs()
    {
        return $this->belongsToMany(Objectif::class, 'exercice_objectif')->withTimestamps();
    }

    /**
     * Activités rattachées directement à l'exercice.
     */
    public function activites()
    {
        return $this->hasMany(Activite::class);
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
     * La fenêtre de saisie d'une période d'évaluation est-elle ouverte aujourd'hui ?
     */
    public function enPeriodeEvaluationPour(string $periode): bool
    {
        return $periode === ActiviteEvaluation::PERIODE_MI_PARCOURS
            ? $this->enPeriodeMiParcours()
            : $this->enPeriodeEvaluation();
    }

    /**
     * Bornes de la fenêtre de saisie d'une période d'évaluation.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    public function fenetreEvaluation(string $periode): array
    {
        return $periode === ActiviteEvaluation::PERIODE_MI_PARCOURS
            ? [$this->date_debut_mi_parcours, $this->date_fin_mi_parcours]
            : [$this->date_debut_evaluation, $this->date_fin_evaluation];
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
