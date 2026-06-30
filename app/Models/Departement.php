<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departement extends Model
{
    use HasFactory, LogsActivityWithDefaults, SoftDeletes;

    protected $table = 'departements';

    public const TYPE_DIRECTION = 'direction';
    public const TYPE_DEPARTEMENT = 'departement';
    public const TYPE_SERVICE = 'service';

    public const TYPES = [
        self::TYPE_DIRECTION,
        self::TYPE_DEPARTEMENT,
        self::TYPE_SERVICE,
    ];

    protected $fillable = [
        'code',
        'nom',
        'type',
        'parent_id',
        'description',
        'responsable_id',
        'is_active',
        'ordre'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations hiérarchiques
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // Relations
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function activites()
    {
        return $this->hasMany(Activite::class, 'departement_id');
    }

    // Relations many-to-many (responsabilités)
    public function activitesResponsables()
    {
        return $this->belongsToMany(Activite::class, 'activite_departement')->withTimestamps();
    }

    public function objectifs()
    {
        return $this->belongsToMany(Objectif::class, 'departement_objectif')->withTimestamps();
    }

    public function resultats()
    {
        return $this->belongsToMany(Resultat::class, 'departement_resultat')->withTimestamps();
    }

    public function extrants()
    {
        return $this->belongsToMany(Extrant::class, 'departement_extrant')->withTimestamps();
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('nom');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
