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
    public const TYPE_AGENCE_COMPTABLE = 'agence_comptable';
    public const TYPE_BUREAU_REGIONAL = 'bureau_regional';

    public const TYPES = [
        self::TYPE_DIRECTION,
        self::TYPE_DEPARTEMENT,
        self::TYPE_SERVICE,
        self::TYPE_AGENCE_COMPTABLE,
        self::TYPE_BUREAU_REGIONAL,
    ];

    public const TYPE_LABELS = [
        self::TYPE_DIRECTION => 'Direction Générale',
        self::TYPE_DEPARTEMENT => 'Direction Centrale',
        self::TYPE_SERVICE => 'Service',
        self::TYPE_AGENCE_COMPTABLE => 'Agence Comptable',
        self::TYPE_BUREAU_REGIONAL => 'Bureau Régional',
    ];

    /**
     * Entités rattachées directement à la Direction Générale et qui lui soumettent
     * leurs activités : Directions Centrales, Agence Comptable, Bureaux Régionaux.
     * L'Agence Comptable et les Bureaux Régionaux ne sont pas des Directions
     * Centrales mais jouent le même rôle dans le circuit de planification.
     */
    public const TYPES_RATTACHES_DG = [
        self::TYPE_DEPARTEMENT,
        self::TYPE_AGENCE_COMPTABLE,
        self::TYPE_BUREAU_REGIONAL,
    ];

    /** Types qui ne peuvent jamais avoir d'entité enfant. */
    public const TYPES_FEUILLES = [
        self::TYPE_SERVICE,
        self::TYPE_AGENCE_COMPTABLE,
        self::TYPE_BUREAU_REGIONAL,
    ];

    /** Libellés des groupes utilisés dans les listes déroulantes hiérarchiques. */
    public const GROUPE_DIRECTIONS = 'Direction Générale';
    public const GROUPE_DIRECTIONS_CENTRALES = 'Directions Centrales';
    public const GROUPE_AGENCE_COMPTABLE = 'Agence Comptable';
    public const GROUPE_BUREAUX_REGIONAUX = 'Bureaux Régionaux';

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
     * Libellé lisible du type (Direction Générale / Direction Centrale / Service / …).
     */
    public function typeLibelle(): string
    {
        return self::TYPE_LABELS[$this->type] ?? ucfirst((string) $this->type);
    }

    /** Une entité feuille (service, agence comptable, bureau régional) n'a jamais d'enfant. */
    public function peutAvoirDesEnfants(): bool
    {
        return ! in_array($this->type, self::TYPES_FEUILLES, true);
    }

    /** Soumet-elle ses activités directement à la Direction Générale ? */
    public function estRattacheeDg(): bool
    {
        return in_array($this->type, self::TYPES_RATTACHES_DG, true);
    }

    /**
     * Identifiants du sous-arbre : l'entité elle-même + toutes ses
     * descendantes (départements et services rattachés, à toute profondeur).
     *
     * @return array<int, int>
     */
    public function sousArbreIds(): array
    {
        $ids = [$this->id];
        $frontiere = [$this->id];

        while (! empty($frontiere)) {
            $frontiere = self::query()
                ->whereIn('parent_id', $frontiere)
                ->pluck('id')
                ->all();

            $ids = array_merge($ids, $frontiere);
        }

        return $ids;
    }

    /**
     * Entité de rattachement pour l'arbitrage : les activités d'un service sont
     * concentrées dans l'entité qui le chapeaute, c'est-à-dire son plus proche
     * ancêtre qui n'est pas lui-même un service. Selon la hiérarchie réelle, ce
     * peut être une Direction Centrale (ex. DBCGOQ) comme une Direction (ex. DAGRH).
     *
     * Toute entité qui n'est pas un service se représente elle-même. Un service
     * orphelin (sans ancêtre non-service) se représente également lui-même, faute
     * de quoi ses activités disparaîtraient de l'arbitrage.
     */
    public function entiteDeRattachement(): self
    {
        if ($this->type !== self::TYPE_SERVICE) {
            return $this;
        }

        return $this->ancetres()->last(fn (self $ancetre) => $ancetre->type !== self::TYPE_SERVICE) ?? $this;
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

    /**
     * Entités susceptibles de porter un PTA. La Direction Générale n'existe dans le
     * système que comme sommet de l'organigramme : elle ne formule pas d'activités,
     * elle reçoit celles des entités qui lui sont rattachées.
     */
    public function scopeFormulatrices($query)
    {
        return $query->where('type', '!=', self::TYPE_DIRECTION);
    }

    /**
     * Entités qui soumettent directement à la Direction Générale : Directions
     * Centrales, Agence Comptable et Bureaux Régionaux.
     */
    public function scopeRattacheesDg($query)
    {
        return $query->whereIn('type', self::TYPES_RATTACHES_DG);
    }

    /**
     * Regroupe des entités par Direction Centrale de rattachement, pour alimenter
     * les <optgroup> des listes déroulantes. Groupes et entités triés par nom.
     *
     * @param  iterable<int, self>  $departements
     * @return array<string, array<int, self>>
     */
    public static function grouperParDirectionCentrale($departements): array
    {
        // Carte complète de la hiérarchie : les ancêtres d'une entité visible ne
        // font pas forcément partie du périmètre passé en paramètre.
        $carte = self::query()->get(['id', 'parent_id', 'type', 'nom'])->keyBy('id');

        $groupes = [];

        foreach (collect($departements)->sortBy('nom', SORT_NATURAL | SORT_FLAG_CASE) as $departement) {
            $groupes[self::libelleGroupeHierarchique($departement, $carte)][] = $departement;
        }

        // Une Direction Centrale sans entité rattachée n'a pas besoin de son propre
        // groupe : on rassemble ces entités dans un groupe commun.
        $communLibelle = self::GROUPE_DIRECTIONS_CENTRALES;
        $commun = [];

        foreach ($groupes as $libelle => $entites) {
            if ($libelle !== $communLibelle && count($entites) === 1 && $entites[0]->type === self::TYPE_DEPARTEMENT) {
                $commun[] = $entites[0];
                unset($groupes[$libelle]);
            }
        }

        if ($commun !== []) {
            $groupes[$communLibelle] = array_merge($groupes[$communLibelle] ?? [], $commun);
            usort($groupes[$communLibelle], fn ($a, $b) => strnatcasecmp($a->nom, $b->nom));
        }

        ksort($groupes, SORT_NATURAL | SORT_FLAG_CASE);

        return $groupes;
    }

    /**
     * Nom de la Direction Centrale dont dépend l'entité (elle-même si c'en est une).
     * L'Agence Comptable et les Bureaux Régionaux, rattachés directement à la DG,
     * ont leur propre groupe ; le reste retombe sur le groupe de la DG.
     */
    protected static function libelleGroupeHierarchique(self $departement, \Illuminate\Support\Collection $carte): string
    {
        $courant = $carte[$departement->id] ?? $departement;

        while ($courant) {
            if ($courant->type === self::TYPE_DEPARTEMENT) {
                return $courant->nom;
            }

            if ($courant->type === self::TYPE_AGENCE_COMPTABLE) {
                return self::GROUPE_AGENCE_COMPTABLE;
            }

            if ($courant->type === self::TYPE_BUREAU_REGIONAL) {
                return self::GROUPE_BUREAUX_REGIONAUX;
            }

            $courant = $courant->parent_id ? ($carte[$courant->parent_id] ?? null) : null;
        }

        return self::GROUPE_DIRECTIONS;
    }
}
