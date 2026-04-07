<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activite extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'extrant_id',
        'code',
        'libelle',
        'description',
        'budget_previsionnel_global',
        'date_debut_prevue',
        'date_fin_prevue',
        'ordre',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date_debut_prevue' => 'date',
        'date_fin_prevue' => 'date',
    ];

    public function extrant()
    {
        return $this->belongsTo(Extrant::class);
    }
}
