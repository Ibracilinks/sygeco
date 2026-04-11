<?php

namespace App\Services;

use App\Models\Objectif;
use App\Models\Extrant;
use App\Models\Activite;
use App\Models\Departement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardDataService
{
    protected $annee;

    public function __construct()
    {
        $this->annee = request()->get('annee', Carbon::now()->year);
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
                ->where('objectifs.annee', $this->annee)
                ->selectRaw('
                    COUNT(DISTINCT objectifs.id) as total_objectifs,
                    COUNT(DISTINCT extrants.id) as total_extrants,
                    COUNT(activites.id) as total_activites,
                    SUM(activites.cout) as budget_total,
                    SUM(CASE WHEN activites.statut = "valide" THEN 1 ELSE 0 END) as activites_validees
                ')
                ->first();

            $tauxRealisation = $stats->total_activites > 0 ? round(($stats->activites_validees / $stats->total_activites) * 100, 1) : 0;

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
            $result = DB::table('objectifs')
                ->leftJoin('extrants', 'objectifs.id', '=', 'extrants.objectif_id')
                ->leftJoin('activites', 'extrants.id', '=', 'activites.extrant_id')
                ->where('objectifs.annee', $this->annee)
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
            $result = DB::table('extrants')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->leftJoin('activites', 'extrants.id', '=', 'activites.extrant_id')
                ->where('objectifs.annee', $this->annee)
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
                ->where('objectifs.annee', $this->annee)
                ->select('activites.id', 'activites.nom_activite', 'activites.cout')
                ->orderByDesc('activites.cout')
                ->limit(10)
                ->get()
                ->map(function ($activite) {
                    return [
                        'id' => $activite->id,
                        'code' => 'ACT-' . $activite->id,
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
            $result = DB::table('departements')
                ->leftJoin('activites', 'departements.id', '=', 'activites.departement_id')
                ->leftJoin('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->leftJoin('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->where('objectifs.annee', $this->annee)
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

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $mois = $date->format('M Y');

                $stats = DB::table('activites')
                    ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                    ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                    ->where('objectifs.annee', $this->annee)
                    ->whereMonth('activites.created_at', $date->month)
                    ->whereYear('activites.created_at', $date->year)
                    ->selectRaw('
                        COUNT(activites.id) as nb_activites,
                        COALESCE(SUM(activites.cout), 0) as budget
                    ')
                    ->first();

                $data[] = [
                    'mois' => $mois,
                    'nb_activites' => $stats->nb_activites,
                    'budget' => $stats->budget,
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
                ->where('objectifs.annee', $this->annee)
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
     * Activités par statut
     */
    public function getActivitesParStatut()
    {
        return Cache::remember("dashboard_activites_statut_{$this->annee}", 3600, function () {
            $result = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->where('objectifs.annee', $this->annee)
                ->selectRaw('
                    activites.statut,
                    COUNT(activites.id) as count
                ')
                ->groupBy('activites.statut')
                ->pluck('count', 'activites.statut')
                ->toArray();

            // Ensure all statuts are present
            $statuts = ['brouillon' => 0, 'soumis' => 0, 'valide' => 0];
            foreach ($result as $statut => $count) {
                if (isset($statuts[$statut])) {
                    $statuts[$statut] = $count;
                }
            }

            return [
                'Brouillon' => $statuts['brouillon'],
                'Soumis' => $statuts['soumis'],
                'Valide' => $statuts['valide'],
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
                ->where('objectifs.annee', $this->annee)
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
                ->where('objectifs.annee', $this->annee)
                ->where('activites.created_at', '>=', Carbon::now()->subMonths(3))
                ->count();

            $trimestrePrecedent = DB::table('activites')
                ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
                ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
                ->where('objectifs.annee', $this->annee)
                ->whereBetween('activites.created_at', [Carbon::now()->subMonths(6), Carbon::now()->subMonths(3)])
                ->count();

            if ($trimestrePrecedent == 0) return 0;

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
                ->where('objectifs.annee', $this->annee)
                ->whereYear('activites.created_at', $this->annee)
                ->selectRaw('
                    COUNT(activites.id) as count,
                    COALESCE(SUM(activites.cout), 0) as total
                ')
                ->first();

            return $stats->count > 0 ? $stats->total / $stats->count : 0;
        });
    }
}
