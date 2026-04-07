<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResultatStrategique extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'objectif_strategique_id',
        'code',
        'libelle',
        'description',
        'ordre',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function objectifStrategique()
    {
        return $this->belongsTo(ObjectifStrategique::class);
    }

    public function extrants()
    {
        return $this->hasMany(Extrant::class);
    }
}
