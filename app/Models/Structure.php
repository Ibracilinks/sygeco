<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Structure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'libelle',
        'type',
        'parent_id',
        'responsable_nom',
        'responsable_email',
        'telephone',
        'adresse',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relation hiérarchique
    public function parent()
    {
        return $this->belongsTo(Structure::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(Structure::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scope pour filtrer par type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
