<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'ordre',
        'nom_complet',
        'categorie',
        'montant_par_jour',
        'montant_par_nuitee',
        'nombre_nuitees',
        'sous_total',
        'majoration_taux',
        'majoration_montant',
        'total_general',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}
