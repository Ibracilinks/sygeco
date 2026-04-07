<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Extrant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'resultat_strategique_id',
        'code',
        'libelle',
        'description',
        'ordre',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function resultatStrategique()
    {
        return $this->belongsTo(ResultatStrategique::class);
    }

    public function activites()
    {
        return $this->hasMany(Activite::class);
    }
}
