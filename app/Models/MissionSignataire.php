<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionSignataire extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'ordre',
        'libelle',
        'nom',
        'fonction',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}
