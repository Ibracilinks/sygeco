<?php

namespace App\Services;

use App\Models\ActiviteEvaluation;
use App\Models\Departement;
use App\Models\Exercice;
use App\Support\ActiveExercice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardDataService
{
    protected $annee;

    public function __construct()
    {
        // Année par défaut issue du contexte exercices : exercice actif de la session,
        // sinon exercice « actif » courant, sinon année en cours.
        $defaut = optional(Exercice::find(ActiveExercice::id()))->annee
            ?? optional(Exercice::actifCourant())->annee
            ?? Carbon::now()->year;

        $this->annee = (int) request()->query('annee', $defaut);
    }

    /**
     * Liste des années sélectionnables, issue des exercices existants (ordre décroissant).
     *
     * @return array<int, int>
     */
    protected function anneesExercices(): array
    {
        $annees = Exercice::query()->ordered()->pluck('annee')
            ->map(fn ($a) => (int) $a)
            ->all();

        return $annees !== [] ? $annees : range((int) Carbon::now()->year, (int) Carbon::now()->year - 4);
    }

    /**
     * Exercices correspondant à l'année affichée. Le rattachement des activités se
     * fait par `activites.exercice_id` : un objectif pouvant couvrir plusieurs
     * exercices, son année ne dit plus rien de l'année d'exécution des activités.
     *
     * @return array<int, int>
     */
    protected function exerciceIdsDeLAnnee(): array
    {
        return Exercice::query()->where('annee', $this->annee)->pluck('id')->all();
    }

    /**
     * Statistiques principales
     */
    public function getStats()
    {
        return Cache::remember("dashboard_stats_{$this->annee}", 3600, function () {
            $stats = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                ->whereNull('activites.deleted_at')
                ->selectRaw('
                    COUNT(DISTINCT objectifs.id) as total_objectifs,
                    COUNT(DISTINCT extrants.id) as total_extrants,
                    COUNT(activites.id) as total_activites,
                    SUM(activites.cout) as budget_total,
                    SUM(CASE WHEN activites.statut_execution = "realise" THEN 1 ELSE 0 END) as activites_realisees
                ')
                ->first();

            $tauxRealisation = $stats->total_activites > 0 ? round(($stats->activites_realisees / $stats->total_activites) * 100, 1) : 0;

            return [
                'total_objectifs' => (int) $stats->total_objectifs,
                'total_extrants' => (int) $stats->total_extrants,
                'total_activites' => (int) $stats->total_activites,
                'budget_total' => (float) $stats->budget_total,
                'taux_realisation' => $tauxRealisation,
            ];
        });
    }

    /**
     * Budget par Objectif
     */
    public function getBudgetParObjectif()
    {
        return Cache::remember("dashboard_budget_objectif_{$this->annee}", 3600, function () {
            $exerciceIds = $this->exerciceIdsDeLAnnee();

            $result = DB::table('objectifs')
                ->leftJoin('extrants', 'objectifs.id', '=', 'extrants.objectif_id')
                ->leftJoin('activites', function ($join) use ($exerciceIds) {
                    $join->on('extrants.id', '=', 'activites.extrant_id')
                        ->whereIn('activites.exercice_id', $exerciceIds)
                        ->whereNull('activites.deleted_at');
                })
                // L'objectif est retenu s'il couvre l'année, même pluriannuel.
                ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('exercice_objectif')
                    ->whereColumn('exercice_objectif.objectif_id', 'objectifs.id')
                    ->whereIn('exercice_objectif.exercice_id', $exerciceIds))
                ->selectRaw('
                    objectifs.code,
                    objectifs.libelle,
                    COALESCE(SUM(activites.cout), 0) as budget
                ')
                ->groupBy('objectifs.id', 'objectifs.code', 'objectifs.libelle')
                ->havingRaw('SUM(activites.cout) > 0')
                ->orderByDesc('budget')
                ->get()
                ->map(function ($item) {
                    return [
                        'code' => $item->code,
                        'libelle' => $item->libelle,
                        'budget' => round($item->budget / 1000000, 1),
                    ];
                })
                ->toArray();

            return $result;
        });
    }

    /**
     * Top Extrants
     */
    public function getTopExtrants()
    {
        return Cache::remember("dashboard_top_extrants_{$this->annee}", 3600, function () {
            $exerciceIds = $this->exerciceIdsDeLAnnee();

            $result = DB::table('extrants')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->leftJoin('activites', function ($join) use ($exerciceIds) {
                    $join->on('extrants.id', '=', 'activites.extrant_id')
                        ->whereIn('activites.exercice_id', $exerciceIds)
                        ->whereNull('activites.deleted_at');
                })
                ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('exercice_objectif')
                    ->whereColumn('exercice_objectif.objectif_id', 'objectifs.id')
                    ->whereIn('exercice_objectif.exercice_id', $exerciceIds))
                ->selectRaw('
                    extrants.code,
                    extrants.libelle,
                    COUNT(activites.id) as nb_activites
                ')
                ->groupBy('extrants.id', 'extrants.code', 'extrants.libelle')
                ->havingRaw('COUNT(activites.id) > 0')
                ->orderByDesc('nb_activites')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'code' => $item->code,
                        'libelle' => $item->libelle,
                        'nb_activites' => $item->nb_activites,
                    ];
                })
                ->toArray();

            return $result;
        });
    }

    /**
     * Top Activités par coût
     */
    public function getTopActivites()
    {
        return Cache::remember("dashboard_top_activites_{$this->annee}", 3600, function () {
            $activites = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                ->whereNull('activites.deleted_at')
                ->select('activites.id', 'activites.nom_activite', 'activites.cout')
                ->orderByDesc('activites.cout')
                ->limit(10)
                ->get()
                ->map(function ($activite) {
                    return [
                        'id' => $activite->id,
                        'code' => 'ACT-'.$activite->id,
                        'nom_activite' => $activite->nom_activite,
                        'cout' => $activite->cout,
                    ];
                })
                ->toArray();

            return $activites;
        });
    }

    /**
     * Budget par Département
     */
    public function getBudgetParDepartement()
    {
        return Cache::remember("dashboard_budget_departement_{$this->annee}", 3600, function () {
            $exerciceIds = $this->exerciceIdsDeLAnnee();

            $result = DB::table('departements')
                ->leftJoin('activites', function ($join) use ($exerciceIds) {
                    $join->on('departements.id', '=', 'activites.departement_id')
                        ->whereIn('activites.exercice_id', $exerciceIds)
                        ->whereNull('activites.deleted_at');
                })
                ->selectRaw('
                    departements.nom,
                    COALESCE(SUM(activites.cout), 0) as budget
                ')
                ->groupBy('departements.id', 'departements.nom')
                ->havingRaw('SUM(activites.cout) > 0')
                ->orderByDesc('budget')
                ->get()
                ->map(function ($item) {
                    return [
                        'nom' => $item->nom,
                        'budget' => $item->budget,
                    ];
                })
                ->toArray();

            return $result;
        });
    }

    /**
     * Évolution mensuelle
     */
    public function getEvolutionMensuelle()
    {
        return Cache::remember("dashboard_evolution_mensuelle_{$this->annee}", 3600, function () {
            $data = [];

            // Les 12 mois calendaires de l'exercice sélectionné (janvier → décembre),
            // afin que la courbe reste cohérente avec l'année d'exercice choisie
            // (et ne déborde plus sur l'année suivante comme avec une fenêtre glissante).
            for ($mois = 1; $mois <= 12; $mois++) {
                $date = Carbon::create($this->annee, $mois, 1);

                $stats = DB::table('activites')
                    ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                    ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                    ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                    ->whereNull('activites.deleted_at')
                    ->whereMonth('activites.created_at', $mois)
                    ->whereYear('activites.created_at', $this->annee)
                    ->selectRaw('
                        COUNT(activites.id) as nb_activites,
                        COALESCE(SUM(activites.cout), 0) as budget
                    ')
                    ->first();

                $data[] = [
                    'mois' => $date->format('M Y'),
                    'nb_activites' => (int) $stats->nb_activites,
                    'budget' => (float) $stats->budget,
                ];
            }

            return $data;
        });
    }

    /**
     * Évolution trimestrielle (T1 → T4) : nombre d'activités planifiées et budget associé,
     * en s'appuyant sur les indicateurs de trimestre (trimestre_1..4 = "oui").
     */
    public function getEvolutionTrimestrielle()
    {
        return Cache::remember("dashboard_evolution_trimestrielle_{$this->annee}", 3600, function () {
            $data = [];

            for ($t = 1; $t <= 4; $t++) {
                $colonne = "trimestre_{$t}";

                $stats = DB::table('activites')
                    ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                    ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                    ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                    ->whereNull('activites.deleted_at')
                    ->where("activites.{$colonne}", 'oui')
                    ->selectRaw('
                        COUNT(activites.id) as nb_activites,
                        COALESCE(SUM(activites.cout), 0) as budget
                    ')
                    ->first();

                $data[] = [
                    'trimestre' => "T{$t}",
                    'nb_activites' => (int) $stats->nb_activites,
                    'budget' => (float) $stats->budget,
                ];
            }

            return $data;
        });
    }

    /**
     * Distribution budgétaire par tranche
     */
    public function getDistributionBudgetaire()
    {
        return Cache::remember("dashboard_distribution_budgetaire_{$this->annee}", 3600, function () {
            $distribution = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                ->whereNull('activites.deleted_at')
                ->selectRaw('
                    SUM(CASE WHEN activites.cout < 1000000 THEN 1 ELSE 0 END) as tranche_0_1m,
                    SUM(CASE WHEN activites.cout >= 1000000 AND activites.cout < 5000000 THEN 1 ELSE 0 END) as tranche_1_5m,
                    SUM(CASE WHEN activites.cout >= 5000000 AND activites.cout < 10000000 THEN 1 ELSE 0 END) as tranche_5_10m,
                    SUM(CASE WHEN activites.cout >= 10000000 AND activites.cout < 50000000 THEN 1 ELSE 0 END) as tranche_10_50m,
                    SUM(CASE WHEN activites.cout >= 50000000 THEN 1 ELSE 0 END) as tranche_50m_plus
                ')
                ->first();

            return [
                '0 - 1M' => (int) $distribution->tranche_0_1m,
                '1M - 5M' => (int) $distribution->tranche_1_5m,
                '5M - 10M' => (int) $distribution->tranche_5_10m,
                '10M - 50M' => (int) $distribution->tranche_10_50m,
                '50M+' => (int) $distribution->tranche_50m_plus,
            ];
        });
    }

    /**
     * Répartition de l'état d'exécution des activités validées, telle qu'évaluée
     * en fin d'année. Les activités validées non encore évaluées forment une part
     * distincte : elles ne sont pas assimilées à des activités non réalisées.
     *
     * @return array<string, int>
     */
    public function getExecutionActivitesValidees(): array
    {
        return Cache::remember("dashboard_execution_validees_{$this->annee}", 3600, function () {
            $comptes = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->leftJoin('activite_evaluations', function ($join) {
                    $join->on('activite_evaluations.activite_id', '=', 'activites.id')
                        ->where('activite_evaluations.periode', '=', ActiviteEvaluation::PERIODE_FIN_ANNEE);
                })
                ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                ->where('activites.statut', 'valide')
                ->whereNull('activites.deleted_at')
                ->selectRaw('activite_evaluations.statut_execution as statut, COUNT(activites.id) as total')
                ->groupBy('activite_evaluations.statut_execution')
                ->pluck('total', 'statut')
                ->toArray();

            return [
                'Réalisée' => (int) ($comptes['realise'] ?? 0),
                'En cours de réalisation' => (int) ($comptes['en_cours'] ?? 0),
                'Non réalisée' => (int) ($comptes['non_realise'] ?? 0),
                'Non évaluée' => (int) ($comptes[''] ?? 0),
            ];
        });
    }

    /**
     * Activités par statut
     */
    public function getActivitesParStatut()
    {
        return Cache::remember("dashboard_activites_statut_{$this->annee}", 3600, function () {
            $result = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                ->whereNull('activites.deleted_at')
                ->selectRaw('
                    activites.statut,
                    COUNT(activites.id) as count
                ')
                ->groupBy('activites.statut')
                ->pluck('count', 'activites.statut')
                ->toArray();

            // Ensure all statuts are present
            $statuts = ['brouillon' => 0, 'en_attente' => 0, 'valide' => 0, 'rejete' => 0];
            foreach ($result as $statut => $count) {
                if (isset($statuts[$statut])) {
                    $statuts[$statut] = $count;
                }
            }

            return [
                'Brouillon' => $statuts['brouillon'],
                'En attente' => $statuts['en_attente'],
                'Validé' => $statuts['valide'],
                'Rejeté' => $statuts['rejete'],
            ];
        });
    }

    /**
     * Activités par trimestre
     */
    public function getActivitesParTrimestre()
    {
        return Cache::remember("dashboard_activites_trimestre_{$this->annee}", 3600, function () {
            $trimestres = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                ->whereNull('activites.deleted_at')
                ->selectRaw('
                    SUM(CASE WHEN trimestre_1 = "oui" THEN 1 ELSE 0 END) as t1,
                    SUM(CASE WHEN trimestre_2 = "oui" THEN 1 ELSE 0 END) as t2,
                    SUM(CASE WHEN trimestre_3 = "oui" THEN 1 ELSE 0 END) as t3,
                    SUM(CASE WHEN trimestre_4 = "oui" THEN 1 ELSE 0 END) as t4
                ')
                ->first();

            return [$trimestres->t1, $trimestres->t2, $trimestres->t3, $trimestres->t4];
        });
    }

    /**
     * Tendance des activités
     */
    public function getTendanceActivites()
    {
        return Cache::remember("dashboard_tendance_activites_{$this->annee}", 3600, function () {
            $dernierTrimestre = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                ->whereNull('activites.deleted_at')
                ->where('activites.created_at', '>=', Carbon::now()->subMonths(3))
                ->count();

            $trimestrePrecedent = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                ->whereNull('activites.deleted_at')
                ->whereBetween('activites.created_at', [Carbon::now()->subMonths(6), Carbon::now()->subMonths(3)])
                ->count();

            if ($trimestrePrecedent == 0) {
                return 0;
            }

            return round((($dernierTrimestre - $trimestrePrecedent) / $trimestrePrecedent) * 100);
        });
    }

    /**
     * Budget moyen mensuel
     */
    public function getBudgetMoyenMensuel()
    {
        return Cache::remember("dashboard_budget_moyen_{$this->annee}", 3600, function () {
            $stats = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->whereIn('activites.exercice_id', $this->exerciceIdsDeLAnnee())
                ->whereNull('activites.deleted_at')
                ->whereYear('activites.created_at', $this->annee)
                ->selectRaw('
                    COUNT(activites.id) as count,
                    COALESCE(SUM(activites.cout), 0) as total
                ')
                ->first();

            return $stats->count > 0 ? $stats->total / $stats->count : 0;
        });
    }

    /**
     * Pourcentage de soumission (soumis + validé) par département pour un exercice.
     *
     * @return array<int, array{nom: string, total: int, soumises: int, pct: float}>
     */
    public function getSoumissionParDepartement(?int $exerciceId = null): array
    {
        $exerciceId ??= ActiveExercice::id();

        if ($exerciceId === null) {
            return [];
        }

        return Departement::query()
            ->active()
            ->ordered()
            ->withCount([
                'activites as total_activites' => fn ($q) => $q->forExercice($exerciceId),
                'activites as activites_soumises' => fn ($q) => $q->forExercice($exerciceId)->whereIn('statut', ['en_attente', 'valide']),
            ])
            ->get()
            ->map(fn ($d) => [
                'nom' => $d->nom,
                'total' => (int) $d->total_activites,
                'soumises' => (int) $d->activites_soumises,
                'pct' => $d->total_activites > 0
                    ? round(100 * $d->activites_soumises / $d->total_activites, 1)
                    : 0.0,
            ])
            ->values()
            ->all();
    }

    /**
     * Départements avec au moins une activité en brouillon pour l'exercice.
     *
     * @return array<int, array{nom: string, nb_brouillon: int}>
     */
    public function getDepartementsEnRetard(?int $exerciceId = null): array
    {
        $exerciceId ??= ActiveExercice::id();

        if ($exerciceId === null) {
            return [];
        }

        return Departement::query()
            ->active()
            ->ordered()
            ->withCount([
                'activites as nb_brouillon' => fn ($q) => $q->forExercice($exerciceId)->where('statut', 'brouillon'),
            ])
            // Équivaut à « nb_brouillon > 0 » sans HAVING (rejeté par SQLite sans GROUP BY,
            // et portable sur tous les SGBD).
            ->whereHas('activites', fn ($q) => $q->forExercice($exerciceId)->where('statut', 'brouillon'))
            ->get()
            ->map(fn ($d) => [
                'nom' => $d->nom,
                'nb_brouillon' => (int) $d->nb_brouillon,
            ])
            ->values()
            ->all();
    }

    /**
     * Assemble a normalized payload for the main dashboard UI.
     *
     * @return array<string, mixed>
     */
    public function getDashboardPayload(): array
    {
        $stats = $this->getStats();
        $budgetParObjectif = collect($this->getBudgetParObjectif());
        $topExtrants = collect($this->getTopExtrants());
        $topActivites = collect($this->getTopActivites());
        $evolutionTrimestrielle = collect($this->getEvolutionTrimestrielle());
        $distributionBudgetaire = $this->getDistributionBudgetaire();
        $activitesParStatut = $this->getActivitesParStatut();
        $activitesParTrimestre = $this->getActivitesParTrimestre();
        $soumissionParDepartement = collect($this->getSoumissionParDepartement());
        $departementsEnRetard = collect($this->getDepartementsEnRetard());
        $budgetParDepartement = collect($this->getBudgetParDepartement());

        $executionValidees = $this->getExecutionActivitesValidees();
        $maxActiviteCout = (float) max(1, (float) $topActivites->max('cout'));
        $yearOptions = $this->anneesExercices();
        $totalActivitesStatut = array_sum($activitesParStatut);

        return [
            'filters' => [
                'selected_year' => (int) $this->annee,
                'year_options' => $yearOptions,
            ],
            'kpis' => [
                'objectifs' => (int) ($stats['total_objectifs'] ?? 0),
                'extrants' => (int) ($stats['total_extrants'] ?? 0),
                'activites' => (int) ($stats['total_activites'] ?? 0),
                'budget_total' => (float) ($stats['budget_total'] ?? 0),
                'taux_realisation' => (float) ($stats['taux_realisation'] ?? 0),
                'en_attente' => (int) (($activitesParStatut['Soumis'] ?? 0) + ($activitesParStatut['Brouillon'] ?? 0)),
                'budget_moyen_mensuel' => (float) $this->getBudgetMoyenMensuel(),
            ],
            'insights' => [
                'soumission_moyenne' => round((float) $soumissionParDepartement->avg('pct'), 1),
                'departements_en_retard' => $departementsEnRetard->count(),
                'activites_total_statut' => $totalActivitesStatut,
            ],
            'charts' => [
                'evolution' => [
                    'labels' => $evolutionTrimestrielle->pluck('trimestre')->values()->all(),
                    'activites' => $evolutionTrimestrielle->pluck('nb_activites')->values()->all(),
                    'budget_millions' => $evolutionTrimestrielle
                        ->map(fn ($row) => round(((float) ($row['budget'] ?? 0)) / 1000000, 1))
                        ->values()
                        ->all(),
                ],
                'budget_par_objectif' => [
                    'labels' => $budgetParObjectif->pluck('code')->values()->all(),
                    'values' => $budgetParObjectif->pluck('budget')->values()->all(),
                ],
                'top_extrants' => [
                    'labels' => $topExtrants->take(6)->pluck('code')->values()->all(),
                    'values' => $topExtrants->take(6)->pluck('nb_activites')->values()->all(),
                ],
                'distribution_budgetaire' => [
                    'labels' => array_keys($distributionBudgetaire),
                    'values' => array_values($distributionBudgetaire),
                ],
                'activites_statut' => [
                    'labels' => array_keys($activitesParStatut),
                    'values' => array_values($activitesParStatut),
                ],
                'execution_validees' => [
                    'labels' => array_keys($executionValidees),
                    'values' => array_values($executionValidees),
                ],
                'activites_trimestre' => [
                    'labels' => ['T1', 'T2', 'T3', 'T4'],
                    'values' => $activitesParTrimestre,
                ],
            ],
            'tables' => [
                'top_activites' => $topActivites
                    ->map(function ($activite) use ($maxActiviteCout) {
                        $cout = (float) ($activite['cout'] ?? 0);

                        return [
                            'id' => $activite['id'] ?? null,
                            'code' => (string) ($activite['code'] ?? ''),
                            'nom_activite' => (string) ($activite['nom_activite'] ?? ''),
                            'cout' => $cout,
                            'cout_millions' => round($cout / 1000000, 1),
                            'ratio' => round(($cout / $maxActiviteCout) * 100, 1),
                        ];
                    })
                    ->take(8)
                    ->values()
                    ->all(),
                'soumission_departements' => $soumissionParDepartement->values()->all(),
                'departements_en_retard' => $departementsEnRetard->values()->all(),
                'budget_departements' => $budgetParDepartement
                    ->map(fn ($row) => [
                        'nom' => (string) ($row['nom'] ?? ''),
                        'budget' => (float) ($row['budget'] ?? 0),
                        'budget_millions' => round(((float) ($row['budget'] ?? 0)) / 1000000, 1),
                    ])
                    ->take(8)
                    ->values()
                    ->all(),
            ],
        ];
    }
}
