<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Barème d'une mission : catégorie de missionnaire (frais de mission par jour et
 * indemnités par nuitée) ou zone géographique (taux de majoration). Les valeurs
 * sont administrables ; les constantes du modèle Mission restent le jeu de départ
 * et le filet de secours tant que la table n'est pas alimentée.
 */
class MissionBareme extends Model
{
    use HasFactory, LogsActivityWithDefaults;

    public const GROUPE_CATEGORIE_EXTERIEURE = 'categorie_exterieure';
    public const GROUPE_CATEGORIE_NATIONALE = 'categorie_nationale';
    public const GROUPE_ZONE = 'zone';

    public const GROUPES = [
        self::GROUPE_CATEGORIE_EXTERIEURE => 'Catégories — missions à l\'étranger',
        self::GROUPE_CATEGORIE_NATIONALE => 'Catégories — missions intérieur du pays',
        self::GROUPE_ZONE => 'Zones de majoration',
    ];

    protected $fillable = [
        'groupe',
        'code',
        'libelle',
        'description',
        'frais_mission',
        'indemnites',
        'taux',
        'ordre',
    ];

    protected $casts = [
        'frais_mission' => 'decimal:2',
        'indemnites' => 'decimal:2',
        'taux' => 'decimal:2',
    ];

    /** Mémo par requête : les barèmes sont lus à chaque calcul de mission. */
    protected static array $cache = [];

    public function scopeDuGroupe($query, string $groupe)
    {
        return $query->where('groupe', $groupe)->orderBy('ordre')->orderBy('code');
    }

    public function estZone(): bool
    {
        return $this->groupe === self::GROUPE_ZONE;
    }

    /**
     * Catégories d'un groupe, au format attendu par les calculs :
     * ['cat_1' => ['label' => …, 'description' => …, 'frais_mission' => …, 'indemnites' => …]].
     *
     * @return array<string, array<string, mixed>>
     */
    public static function categories(string $groupe, array $defaut): array
    {
        return self::resoudre($groupe, $defaut, fn (self $bareme) => [
            'label' => $bareme->libelle,
            'description' => $bareme->description,
            'frais_mission' => (float) $bareme->frais_mission,
            'indemnites' => (float) $bareme->indemnites,
        ]);
    }

    /**
     * Zones de majoration : ['code' => ['label' => …, 'taux' => …]].
     *
     * @return array<string, array<string, mixed>>
     */
    public static function zones(array $defaut): array
    {
        return self::resoudre(self::GROUPE_ZONE, $defaut, fn (self $bareme) => [
            'label' => $bareme->libelle,
            'taux' => (float) $bareme->taux,
        ]);
    }

    /**
     * Lit le groupe en base ; retombe sur les valeurs par défaut si la table n'existe
     * pas encore (migration non jouée) ou si le groupe est vide.
     *
     * @param  array<string, array<string, mixed>>  $defaut
     * @return array<string, array<string, mixed>>
     */
    protected static function resoudre(string $groupe, array $defaut, callable $format): array
    {
        if (array_key_exists($groupe, self::$cache)) {
            return self::$cache[$groupe];
        }

        $valeurs = $defaut;

        if (Schema::hasTable('mission_baremes')) {
            $enBase = self::query()->duGroupe($groupe)->get();

            if ($enBase->isNotEmpty()) {
                $valeurs = $enBase->mapWithKeys(fn (self $bareme) => [$bareme->code => $format($bareme)])->all();
            }
        }

        return self::$cache[$groupe] = $valeurs;
    }

    /** À appeler après une modification des barèmes. */
    public static function oublierCache(): void
    {
        self::$cache = [];
    }
}
