<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionEtape extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'ordre',
        'type_etape',
        'bareme',
        'localite',
        'date_depart',
        'date_retour',
        'nombre_jours',
        'nombre_nuitees',
        'premiere_nuitee_payee',
    ];

    protected $casts = [
        'date_depart' => 'date',
        'date_retour' => 'date',
        'premiere_nuitee_payee' => 'boolean',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}
