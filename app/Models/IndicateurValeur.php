<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicateurValeur extends Model
{
    use HasFactory;

    protected $table = 'indicateur_valeurs';

    protected $fillable = [
        'indicateur_id',
        'departement_id',
        'periode',
        'valeur_realisee',
        'ecart',
        'taux_realisation',
        'commentaire',
        'saisi_par',
        'date_saisie',
        'statut',
    ];

    protected $casts = [
        'valeur_realisee' => 'decimal:2',
        'ecart' => 'decimal:2',
        'taux_realisation' => 'decimal:2',
        'date_saisie' => 'date',
    ];

    // Relations
    public function indicateur()
    {
        return $this->belongsTo(Indicateur::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function saisiPar()
    {
        return $this->belongsTo(User::class, 'saisi_par');
    }

    // Accesseurs
    public function getStatutLabelAttribute()
    {
        return match ($this->statut) {
            'brouillon' => '📝 Brouillon',
            'valide' => '✅ Validé',
            default => $this->statut
        };
    }

    // Méthodes métier
    public function calculerEcart()
    {
        if ($this->indicateur && $this->indicateur->cible) {
            $this->ecart = $this->valeur_realisee - $this->indicateur->cible;
            $this->taux_realisation = ($this->valeur_realisee / $this->indicateur->cible) * 100;
        }
    }
}
