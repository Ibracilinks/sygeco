<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resultat extends Model
{
    use HasFactory, LogsActivityWithDefaults, SoftDeletes;

    protected $table = 'resultats';

    protected $fillable = [
        'objectif_id',
        'code',
        'libelle',
        'description',
        'ordre',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function objectif()
    {
        return $this->belongsTo(Objectif::class);
    }

    public function extrants()
    {
        return $this->hasMany(Extrant::class);
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('code');
    }
}
