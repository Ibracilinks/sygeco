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

    public const TYPE_LABELS = [
        self::TYPE_DIRECTION => 'Direction',
        self::TYPE_DEPARTEMENT => 'Département',
        self::TYPE_SERVICE => 'Service',
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
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    /**
     * Sous-arbre complet, chargé récursivement pour l'affichage de l'organigramme.
     */
    public function enfantsRecursifs()
    {
        return $this->enfants()->with('enfantsRecursifs');
    }

    /**
     * Libellé lisible du type (Direction / Département / Service).
     */
    public function typeLibelle(): string
    {
        return self::TYPE_LABELS[$this->type] ?? ucfirst((string) $this->type);
    }

    /**
     * Chaîne des ancêtres, de la racine jusqu'au parent direct.
     *
     * @return \Illuminate\Support\Collection<int, Departement>
     */
    public function ancetres(): \Illuminate\Support\Collection
    {
        $chaine = collect();
        $courant = $this->parent;

        while ($courant) {
            $chaine->prepend($courant);
            $courant = $courant->parent;
        }

        return $chaine;
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
