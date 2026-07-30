<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiviteEvaluation extends Model
{
    use HasFactory;

    protected $table = 'activite_evaluations';

    public const PERIODE_MI_PARCOURS = 'mi_parcours';
    public const PERIODE_FIN_ANNEE = 'fin_annee';

    /** Libellés des périodes d'évaluation. */
    public const PERIODES = [
        self::PERIODE_MI_PARCOURS => 'Mi-parcours',
        self::PERIODE_FIN_ANNEE => "Fin d'année",
    ];

    /** Correspondance entre le segment d'URL et la période stockée. */
    public const SLUGS = [
        'mi-parcours' => self::PERIODE_MI_PARCOURS,
        'fin-annee' => self::PERIODE_FIN_ANNEE,
    ];

    protected $fillable = [
        'activite_id',
        'periode',
        'statut_execution',
        'montant_utilise',
        'valeur_indicateur',
        'observation',
        'maj_par',
        'maj_le',
    ];

    protected $casts = [
        'montant_utilise' => 'decimal:2',
        'valeur_indicateur' => 'decimal:2',
        'maj_le' => 'datetime',
    ];

    public function activite()
    {
        return $this->belongsTo(Activite::class);
    }

    public function majPar()
    {
        return $this->belongsTo(User::class, 'maj_par');
    }

    public function periodeLibelle(): string
    {
        return self::PERIODES[$this->periode] ?? $this->periode;
    }

    /**
     * Période stockée correspondant à un segment d'URL, ou null si inconnu.
     */
    public static function periodeDepuisSlug(string $slug): ?string
    {
        return self::SLUGS[$slug] ?? null;
    }

    public static function slugDePeriode(string $periode): ?string
    {
        return array_search($periode, self::SLUGS, true) ?: null;
    }

    /**
     * Écart entre le budget planifié de l'activité et le montant utilisé sur la période.
     * Positif = économie, négatif = dépassement. Null si non renseigné.
     */
    public function getEcartBudgetaireAttribute(): ?float
    {
        if ($this->montant_utilise === null || ! $this->activite) {
            return null;
        }

        return (float) $this->activite->cout - (float) $this->montant_utilise;
    }
}
