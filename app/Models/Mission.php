<?php

namespace App\Models;

use App\Concerns\LogsActivityWithDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use NumberFormatter;

class Mission extends Model
{
    use HasFactory, LogsActivityWithDefaults, SoftDeletes;

    public const TYPE_MEME_VILLE = 'meme_ville';

    public const TYPE_EXTERIEURE = 'exterieure';

    public const TYPE_REGION = 'region';

    public const TYPES = [
        self::TYPE_MEME_VILLE => 'Même ville',
        self::TYPE_EXTERIEURE => "À l'étranger",
        self::TYPE_REGION => 'Intérieur du pays',
    ];

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'finalise' => 'Finalisé',
    ];

    public const REFERENCE_SUFFIXE_DEFAUT = 'MSDS-CANAM-DAGRH';

    public const CATEGORIES_EXTERIEURES = [
        'cat_1' => [
            'label' => 'Catégorie I',
            'description' => 'Ministère de Tutelle, PCA, Directeur Général',
            'frais_mission' => 75000,
            'indemnites' => 400000,
        ],
        'cat_2' => [
            'label' => 'Catégorie II',
            'description' => 'Autres Administrateurs, DGA, Agent Comptable',
            'frais_mission' => 50000,
            'indemnites' => 300000,
        ],
        'cat_3' => [
            'label' => 'Catégorie III',
            'description' => 'Directeurs Centraux et assimilés, Conseillers Techniques, Contrôleur Financier',
            'frais_mission' => 50000,
            'indemnites' => 250000,
        ],
        'cat_4' => [
            'label' => 'Catégorie IV',
            'description' => 'Chefs de Service, Cadres Supérieurs, Chefs de Service Rattachés, Chargés de Missions',
            'frais_mission' => 50000,
            'indemnites' => 200000,
        ],
        'cat_5' => [
            'label' => 'Catégorie V',
            'description' => 'Autres cadres, autres missionnaires, CSA, Médecins Contrôleurs',
            'frais_mission' => 50000,
            'indemnites' => 150000,
        ],
        'cat_6' => [
            'label' => 'Catégorie VI',
            'description' => 'Autres cadres',
            'frais_mission' => 50000,
            'indemnites' => 100000,
        ],
        'cat_7' => [
            'label' => 'Catégorie VII',
            'description' => "Personnel d'appui",
            'frais_mission' => 50000,
            'indemnites' => 50000,
        ],
    ];

    public const CATEGORIES_NATIONALES = [
        'cat_1' => [
            'label' => 'Catégorie I',
            'description' => 'Ministère de Tutelle, PCA, Directeur Général',
            'frais_mission' => 35000,
            'indemnites' => 150000,
        ],
        'cat_2' => [
            'label' => 'Catégorie II',
            'description' => 'Autres Administrateurs, DGA, Agent Comptable',
            'frais_mission' => 25000,
            'indemnites' => 80000,
        ],
        'cat_3' => [
            'label' => 'Catégorie III',
            'description' => 'Directeurs Centraux et assimilés, Conseillers Techniques, Contrôleur Financier',
            'frais_mission' => 25000,
            'indemnites' => 70000,
        ],
        'cat_4' => [
            'label' => 'Catégorie IV',
            'description' => 'Chefs de Service, Cadres Supérieurs, Chefs de Service Rattachés, Chargés de Missions',
            'frais_mission' => 25000,
            'indemnites' => 60000,
        ],
        'cat_5' => [
            'label' => 'Catégorie V',
            'description' => 'Autres Cadres, autres missionnaires, Chefs de Services Adjoints, Médecins Contrôleurs',
            'frais_mission' => 25000,
            'indemnites' => 50000,
        ],
        'cat_6' => [
            'label' => 'Catégorie VI',
            'description' => 'Autres cadres',
            'frais_mission' => 25000,
            'indemnites' => 40000,
        ],
        'cat_7' => [
            'label' => 'Catégorie VII',
            'description' => "Chauffeurs, Personnel d'appui",
            'frais_mission' => 15000,
            'indemnites' => 30000,
        ],
    ];

    public const BAREMES_REGIONAUX = [
        'national' => [
            'label' => 'Barème national par catégorie',
        ],
        'meme_region' => [
            'label' => 'Mission à l’intérieur d’une même région',
            'frais_mission' => 7500,
            'indemnites' => 10000,
        ],
    ];

    public const TYPES_ETAPES_REGIONALES = [
        'region' => 'Chef-lieu de région',
        'cercle' => 'Cercle',
        'commune' => 'Commune',
        'autre_localite' => 'Autre localité',
    ];

    public const ZONES_EXTERIEURES = [
        'exceptionnelle_amerique' => ['label' => 'Exceptionnelle — Pays du continent américain', 'taux' => 50],
        'exceptionnelle_asie' => ['label' => 'Exceptionnelle — Pays du continent asiatique', 'taux' => 50],
        'exceptionnelle_europe' => ['label' => 'Exceptionnelle — Pays du continent européen', 'taux' => 50],
        'exceptionnelle_oceanie' => ['label' => 'Exceptionnelle — Pays du continent océanique', 'taux' => 50],
        'exceptionnelle_afrique_sud' => ['label' => 'Exceptionnelle — Afrique du Sud', 'taux' => 50],
        'exceptionnelle_angola' => ['label' => 'Exceptionnelle — Angola', 'taux' => 50],
        'zone_a_australe' => ['label' => 'A — Pays de l’Afrique Australe', 'taux' => 40],
        'zone_a_centrale' => ['label' => 'A — Pays de l’Afrique Centrale', 'taux' => 40],
        'zone_a_est' => ['label' => 'A — Pays de l’Afrique de l’Est', 'taux' => 40],
        'zone_a_nord' => ['label' => 'A — Pays de l’Afrique du Nord', 'taux' => 40],
        'zone_b_ouest_hors_cfa' => ['label' => 'B — Zones hors CFA de l’Afrique de l’Ouest', 'taux' => 30],
        'zone_c_ouest_cfa' => ['label' => 'C — Zones CFA de l’Afrique de l’Ouest', 'taux' => 25],
    ];

    protected $fillable = [
        'reference',
        'type',
        'departement_id',
        'objet',
        'destination',
        'zone_code',
        'zone_label',
        'zone_taux',
        'date_document',
        'date_depart',
        'date_retour',
        'nombre_personnes',
        'nombre_jours',
        'tickets_carburant_par_jour',
        'nombre_tickets_carburant',
        'montant_par_jour',
        'montant_ticket_carburant',
        'montant_indemnites',
        'montant_majoration',
        'montant_autres_frais',
        'montant_billets',
        'montant_carburant',
        'montant_total',
        'frais_participation_nombre',
        'frais_participation_unitaire',
        'frais_participation_total',
        'frais_visa_nombre',
        'frais_visa_unitaire',
        'frais_visa_total',
        'billets_affaire_nombre',
        'billets_affaire_unitaire',
        'billets_affaire_total',
        'billets_economique_nombre',
        'billets_economique_unitaire',
        'billets_economique_total',
        'lieu_signature',
        'statut',
        'cree_par',
        'maj_par',
    ];

    protected $casts = [
        'date_document' => 'date',
        'date_depart' => 'date',
        'date_retour' => 'date',
        'zone_taux' => 'decimal:2',
        'montant_par_jour' => 'decimal:2',
        'montant_ticket_carburant' => 'decimal:2',
        'montant_indemnites' => 'decimal:2',
        'montant_majoration' => 'decimal:2',
        'montant_autres_frais' => 'decimal:2',
        'montant_billets' => 'decimal:2',
        'montant_carburant' => 'decimal:2',
        'montant_total' => 'decimal:2',
        'frais_participation_unitaire' => 'decimal:2',
        'frais_participation_total' => 'decimal:2',
        'frais_visa_unitaire' => 'decimal:2',
        'frais_visa_total' => 'decimal:2',
        'billets_affaire_unitaire' => 'decimal:2',
        'billets_affaire_total' => 'decimal:2',
        'billets_economique_unitaire' => 'decimal:2',
        'billets_economique_total' => 'decimal:2',
    ];

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function miseAJourPar()
    {
        return $this->belongsTo(User::class, 'maj_par');
    }

    /**
     * Barèmes en vigueur. Les constantes ci-dessus restent la référence d'origine :
     * elles alimentent la table `mission_baremes` et servent de secours si elle est vide.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function categoriesExterieures(): array
    {
        return MissionBareme::categories(MissionBareme::GROUPE_CATEGORIE_EXTERIEURE, self::CATEGORIES_EXTERIEURES);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function categoriesNationales(): array
    {
        return MissionBareme::categories(MissionBareme::GROUPE_CATEGORIE_NATIONALE, self::CATEGORIES_NATIONALES);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function zonesExterieures(): array
    {
        return MissionBareme::zones(self::ZONES_EXTERIEURES);
    }

    public function participants()
    {
        return $this->hasMany(MissionParticipant::class)->orderBy('ordre');
    }

    public function signataires()
    {
        return $this->hasMany(MissionSignataire::class)->orderBy('ordre');
    }

    public function etapes()
    {
        return $this->hasMany(MissionEtape::class)->orderBy('ordre');
    }

    public function estMemeVille(): bool
    {
        return $this->type === self::TYPE_MEME_VILLE;
    }

    public function estExterieure(): bool
    {
        return $this->type === self::TYPE_EXTERIEURE;
    }

    public function estRegionale(): bool
    {
        return $this->type === self::TYPE_REGION;
    }

    /**
     * @param  array<int, array<string, mixed>>  $participants
     * @return array<int, array<string, mixed>>
     */
    public function appliquerCalculs(array $participants, array $etapes = []): array
    {
        // Le nombre de jours reste ajustable : on ne recalcule que s'il n'a pas été saisi.
        $this->nombre_jours = self::resoudreNombreJours(
            $this->type,
            $this->nombre_jours,
            $this->date_depart,
            $this->date_retour
        );

        if ($this->estExterieure()) {
            return $this->appliquerCalculsExterieurs($participants);
        }

        if ($this->estRegionale()) {
            return $this->appliquerCalculsRegionaux($participants, $etapes);
        }

        return $this->appliquerCalculsMemeVille($participants);
    }

    /**
     * @param  array<int, array<string, mixed>>  $participants
     * @return array<int, array<string, mixed>>
     */
    private function appliquerCalculsMemeVille(array $participants): array
    {
        // Une mission dans la même ville ne donne lieu à aucune indemnité : elle
        // ouvre droit à des tickets de carburant, dénombrés et non valorisés.
        $this->nombre_personnes = count($participants);
        $this->nombre_tickets_carburant = $this->nombre_jours * $this->tickets_carburant_par_jour;
        $this->montant_par_jour = 0;
        $this->montant_ticket_carburant = 0;
        $this->montant_indemnites = 0;
        $this->montant_majoration = 0;
        $this->montant_carburant = 0;
        $this->montant_autres_frais = 0;
        $this->montant_billets = 0;
        $this->frais_participation_total = 0;
        $this->frais_visa_total = 0;
        $this->billets_affaire_total = 0;
        $this->billets_economique_total = 0;
        $this->montant_total = 0;

        return array_map(function (array $participant): array {
            return array_merge($participant, [
                'categorie' => null,
                'montant_par_jour' => 0,
                'montant_par_nuitee' => 0,
                'nombre_nuitees' => 0,
                'sous_total' => 0,
                'majoration_taux' => 0,
                'majoration_montant' => 0,
                'total_general' => 0,
            ]);
        }, $participants);
    }

    /**
     * @param  array<int, array<string, mixed>>  $participants
     * @return array<int, array<string, mixed>>
     */
    private function appliquerCalculsExterieurs(array $participants): array
    {
        $zone = self::zonesExterieures()[$this->zone_code] ?? ['label' => null, 'taux' => 0];
        $this->zone_label = $zone['label'];
        $this->zone_taux = (float) $zone['taux'];
        $this->nombre_personnes = count($participants);
        $this->tickets_carburant_par_jour = 0;
        $this->nombre_tickets_carburant = 0;
        $this->montant_par_jour = 0;
        $this->montant_ticket_carburant = 0;
        $this->montant_carburant = 0;

        $montantBase = 0;
        $montantMajoration = 0;
        $jours = max(1, (int) $this->nombre_jours);
        $defaultNuitees = max(0, $jours - 1);

        $participants = array_map(function (array $participant) use ($jours, $defaultNuitees): array {
            $categorie = self::categoriesExterieures()[$participant['categorie'] ?? ''] ?? null;
            $fraisMission = (float) ($categorie['frais_mission'] ?? 0);
            $indemnites = (float) ($categorie['indemnites'] ?? 0);
            // Les nuitées d'une mission à l'étranger découlent de la durée : jours − 1.
            $nuites = $defaultNuitees;
            $sousTotal = round(($fraisMission * $jours) + ($indemnites * $nuites), 2);
            $majoration = round($sousTotal * ((float) $this->zone_taux / 100), 2);
            $total = round($sousTotal + $majoration, 2);

            return array_merge($participant, [
                'montant_par_jour' => $fraisMission,
                'montant_par_nuitee' => $indemnites,
                'nombre_nuitees' => $nuites,
                'sous_total' => $sousTotal,
                'majoration_taux' => (float) $this->zone_taux,
                'majoration_montant' => $majoration,
                'total_general' => $total,
            ]);
        }, $participants);

        foreach ($participants as $participant) {
            $montantBase += (float) $participant['sous_total'];
            $montantMajoration += (float) $participant['majoration_montant'];
        }

        $this->montant_indemnites = round($montantBase, 2);
        $this->montant_majoration = round($montantMajoration, 2);

        $this->frais_participation_total = round((int) $this->frais_participation_nombre * (float) $this->frais_participation_unitaire, 2);
        $this->frais_visa_total = round((int) $this->frais_visa_nombre * (float) $this->frais_visa_unitaire, 2);
        $this->billets_affaire_total = round((int) $this->billets_affaire_nombre * (float) $this->billets_affaire_unitaire, 2);
        $this->billets_economique_total = round((int) $this->billets_economique_nombre * (float) $this->billets_economique_unitaire, 2);
        $this->montant_autres_frais = round((float) $this->frais_participation_total + (float) $this->frais_visa_total, 2);
        $this->montant_billets = round((float) $this->billets_affaire_total + (float) $this->billets_economique_total, 2);
        $this->montant_total = round(
            (float) $this->montant_indemnites
            + (float) $this->montant_majoration
            + (float) $this->montant_autres_frais
            + (float) $this->montant_billets,
            2
        );

        return $participants;
    }

    /**
     * @param  array<int, array<string, mixed>>  $participants
     * @param  array<int, array<string, mixed>>  $etapes
     * @return array<int, array<string, mixed>>
     */
    private function appliquerCalculsRegionaux(array $participants, array $etapes): array
    {
        $this->nombre_personnes = count($participants);
        $this->tickets_carburant_par_jour = 0;
        $this->nombre_tickets_carburant = 0;
        $this->montant_par_jour = 0;
        $this->montant_ticket_carburant = 0;
        $this->montant_carburant = 0;
        $this->montant_majoration = 0;
        $this->montant_autres_frais = 0;
        $this->montant_billets = 0;
        $this->frais_participation_total = 0;
        $this->frais_visa_total = 0;
        $this->billets_affaire_total = 0;
        $this->billets_economique_total = 0;

        $participants = array_map(function (array $participant) use ($etapes): array {
            $categorie = self::categoriesNationales()[$participant['categorie'] ?? ''] ?? null;
            $montantMission = 0.0;
            $montantNuitee = 0.0;
            $totalNuitees = 0;
            $total = 0.0;

            foreach ($etapes as $etape) {
                $bareme = (string) ($etape['bareme'] ?? 'national');
                $jours = (int) ($etape['nombre_jours'] ?? 1);
                $nuites = (int) ($etape['nombre_nuitees'] ?? max(0, $jours - 1));

                if ($bareme === 'meme_region') {
                    $fraisMission = (float) self::BAREMES_REGIONAUX['meme_region']['frais_mission'];
                    $indemnites = (float) self::BAREMES_REGIONAUX['meme_region']['indemnites'];
                } else {
                    $fraisMission = (float) ($categorie['frais_mission'] ?? 0);
                    $indemnites = (float) ($categorie['indemnites'] ?? 0);
                }

                $montantMission += $fraisMission * $jours;
                $montantNuitee += $indemnites * $nuites;
                $totalNuitees += $nuites;
                $total += ($fraisMission * $jours) + ($indemnites * $nuites);
            }

            $joursMission = max(1, (int) $this->nombre_jours);
            $montantParJour = round($montantMission / $joursMission, 2);
            $montantParNuitee = $totalNuitees > 0 ? round($montantNuitee / $totalNuitees, 2) : 0;

            return array_merge($participant, [
                'montant_par_jour' => $montantParJour,
                'montant_par_nuitee' => $montantParNuitee,
                'nombre_nuitees' => $totalNuitees,
                'sous_total' => round($total, 2),
                'majoration_taux' => 0,
                'majoration_montant' => 0,
                'total_general' => round($total, 2),
            ]);
        }, $participants);

        $this->montant_indemnites = round(collect($participants)->sum('total_general'), 2);
        $this->montant_total = (float) $this->montant_indemnites;

        return $participants;
    }

    public static function calculerNombreJours($dateDepart, $dateRetour): int
    {
        $depart = $dateDepart instanceof Carbon ? $dateDepart : Carbon::parse($dateDepart);
        $retour = $dateRetour instanceof Carbon ? $dateRetour : Carbon::parse($dateRetour);

        return max(1, $depart->startOfDay()->diffInDays($retour->startOfDay()) + 1);
    }

    /**
     * Jours ouvrables : les samedis et dimanches ne comptent pas. Utilisé par défaut
     * pour les missions dans la même ville, qui se déroulent sur les jours ouvrés.
     */
    public static function calculerNombreJoursOuvrables($dateDepart, $dateRetour): int
    {
        $depart = ($dateDepart instanceof Carbon ? $dateDepart->copy() : Carbon::parse($dateDepart))->startOfDay();
        $retour = ($dateRetour instanceof Carbon ? $dateRetour->copy() : Carbon::parse($dateRetour))->startOfDay();

        if ($retour->lessThan($depart)) {
            return 1;
        }

        $jours = 0;

        for ($jour = $depart->copy(); $jour->lessThanOrEqualTo($retour); $jour->addDay()) {
            if (! $jour->isWeekend()) {
                $jours++;
            }
        }

        return max(1, $jours);
    }

    /**
     * Nombre de jours retenu : la valeur saisie prime (elle reste ajustable à la main),
     * sinon on déduit des dates — en jours ouvrables pour une mission même ville,
     * en jours calendaires pour les autres types.
     */
    public static function resoudreNombreJours(?string $type, $saisi, $dateDepart, $dateRetour): int
    {
        if (is_numeric($saisi) && (int) $saisi >= 1) {
            return (int) $saisi;
        }

        return $type === self::TYPE_MEME_VILLE
            ? self::calculerNombreJoursOuvrables($dateDepart, $dateRetour)
            : self::calculerNombreJours($dateDepart, $dateRetour);
    }

    public static function calculerNombreNuitees($dateDepart, $dateRetour, bool $premiereNuiteePayee = false): int
    {
        $jours = self::calculerNombreJours($dateDepart, $dateRetour);

        if ($jours <= 0) {
            return 0;
        }

        return $premiereNuiteePayee ? $jours : max(0, $jours - 1);
    }

    public function getDureeTexteAttribute(): string
    {
        return sprintf(
            '%s au %s',
            $this->date_depart?->format('d/m/Y'),
            $this->date_retour?->format('d/m/Y')
        );
    }

    public function getTicketsCarburantEnLettresAttribute(): string
    {
        $nombre = (int) $this->nombre_tickets_carburant;

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter('fr_FR', NumberFormatter::SPELLOUT);
            $texte = $formatter->format($nombre);

            if (is_string($texte) && $texte !== '') {
                return mb_strtoupper($texte.' tickets de carburant');
            }
        }

        return strtoupper($nombre.' tickets de carburant');
    }

    public function getMontantTotalEnLettresAttribute(): string
    {
        $nombre = (int) round((float) $this->montant_total);

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter('fr_FR', NumberFormatter::SPELLOUT);
            $texte = $formatter->format($nombre);

            if (is_string($texte) && $texte !== '') {
                return mb_strtoupper($texte.' francs CFA');
            }
        }

        return strtoupper($nombre.' francs CFA');
    }

    public static function prochaineReference(?string $suffixe = null): string
    {
        $suffixe ??= self::REFERENCE_SUFFIXE_DEFAUT;

        $max = 0;
        foreach (self::query()->pluck('reference') as $reference) {
            if (preg_match('/^(\d+)\//', (string) $reference, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return sprintf('%03d/%s', $max + 1, $suffixe);
    }
}
