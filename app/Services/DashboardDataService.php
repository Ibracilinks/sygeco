<?php

namespace App\Services;

use App\Models\Objectif;
use App\Models\Extrant;
use App\Models\Activite;
use App\Models\Departement;
use Illuminate\Support\Facades\DB;
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
        $totalObjectifs = Objectif::where('annee', $this->annee)->count();
        $totalExtrants = Extrant::whereHas('objectif', function ($q) {
            $q->where('annee', $this->annee);
        })->count();

        $totalActivites = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })->count();

        $budgetTotal = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })->sum('cout');

        $activitesValidees = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })->where('statut', 'valide')->count();

        $tauxRealisation = $totalActivites > 0 ? round(($activitesValidees / $totalActivites) * 100, 1) : 0;

        return [
            'total_objectifs' => $totalObjectifs,
            'total_extrants' => $totalExtrants,
            'total_activites' => $totalActivites,
            'budget_total' => $budgetTotal,
            'taux_realisation' => $tauxRealisation,
        ];
    }

    /**
     * Budget par Objectif
     */
    public function getBudgetParObjectif()
    {
        $objectifs = Objectif::where('annee', $this->annee)->get();
        $result = [];

        foreach ($objectifs as $objectif) {
            $budget = 0;
            foreach ($objectif->extrants as $extrant) {
                $budget += $extrant->activites->sum('cout');
            }

            if ($budget > 0) {
                $result[] = [
                    'code' => $objectif->code,
                    'libelle' => $objectif->libelle,
                    'budget' => round($budget / 1000000, 1),
                ];
            }
        }

        usort($result, function ($a, $b) {
            return $b['budget'] <=> $a['budget'];
        });

        // Retourner un tableau vide si aucun résultat
        return $result;
    }

    public function getTopExtrants()
    {
        $extrants = Extrant::whereHas('objectif', function ($q) {
            $q->where('annee', $this->annee);
        })->get();

        $result = [];
        foreach ($extrants as $extrant) {
            $nbActivites = $extrant->activites->count();
            if ($nbActivites > 0) {
                $result[] = [
                    'code' => $extrant->code,
                    'libelle' => $extrant->libelle,
                    'nb_activites' => $nbActivites,
                ];
            }
        }

        usort($result, function ($a, $b) {
            return $b['nb_activites'] <=> $a['nb_activites'];
        });

        return array_slice($result, 0, 10);
    }

    /**
     * Top Activités par coût
     */
    public function getTopActivites()
    {
        $activites = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })
            ->orderBy('cout', 'desc')
            ->limit(10)
            ->get();

        $result = [];
        foreach ($activites as $activite) {
            $result[] = [
                'id' => $activite->id,
                'code' => 'ACT-' . $activite->id,
                'nom_activite' => $activite->nom_activite,
                'cout' => $activite->cout,
            ];
        }

        return $result;
    }

    /**
     * Budget par Département
     */
    public function getBudgetParDepartement()
    {
        $departements = Departement::all();
        $result = [];

        foreach ($departements as $departement) {
            $budget = 0;
            foreach ($departement->activites as $activite) {
                if ($activite->extrant && $activite->extrant->objectif && $activite->extrant->objectif->annee == $this->annee) {
                    $budget += $activite->cout;
                }
            }

            if ($budget > 0) {
                $result[] = [
                    'nom' => $departement->nom,
                    'budget' => $budget,
                ];
            }
        }

        usort($result, function ($a, $b) {
            return $b['budget'] <=> $a['budget'];
        });

        return $result;
    }

    /**
     * Évolution mensuelle
     */
    public function getEvolutionMensuelle()
    {
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $mois = $date->format('M Y');

            $count = Activite::whereHas('extrant.objectif', function ($q) use ($date) {
                $q->where('annee', $date->year);
            })
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $budget = Activite::whereHas('extrant.objectif', function ($q) use ($date) {
                $q->where('annee', $date->year);
            })
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('cout');

            $data[] = [
                'mois' => $mois,
                'nb_activites' => $count,
                'budget' => $budget,
            ];
        }

        return $data;
    }

    /**
     * Distribution budgétaire par tranche
     */
    public function getDistributionBudgetaire()
    {
        $distribution = [
            '0 - 1M' => 0,
            '1M - 5M' => 0,
            '5M - 10M' => 0,
            '10M - 50M' => 0,
            '50M+' => 0,
        ];

        $activites = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })->get();

        foreach ($activites as $activite) {
            $cout = $activite->cout;
            if ($cout < 1000000) {
                $distribution['0 - 1M']++;
            } elseif ($cout < 5000000) {
                $distribution['1M - 5M']++;
            } elseif ($cout < 10000000) {
                $distribution['5M - 10M']++;
            } elseif ($cout < 50000000) {
                $distribution['10M - 50M']++;
            } else {
                $distribution['50M+']++;
            }
        }

        return $distribution;
    }

    /**
     * Activités par statut
     */
    public function getActivitesParStatut()
    {
        $statuts = ['brouillon', 'soumis', 'valide'];
        $result = [];

        foreach ($statuts as $statut) {
            $result[ucfirst($statut)] = Activite::whereHas('extrant.objectif', function ($q) {
                $q->where('annee', $this->annee);
            })
                ->where('statut', $statut)
                ->count();
        }

        return $result;
    }

    /**
     * Activités par trimestre
     */
    public function getActivitesParTrimestre()
    {
        $trimestres = [0, 0, 0, 0];

        $activites = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })->get();

        foreach ($activites as $activite) {
            if ($activite->trimestre_1 == 'oui') $trimestres[0]++;
            if ($activite->trimestre_2 == 'oui') $trimestres[1]++;
            if ($activite->trimestre_3 == 'oui') $trimestres[2]++;
            if ($activite->trimestre_4 == 'oui') $trimestres[3]++;
        }

        return $trimestres;
    }

    /**
     * Tendance des activités
     */
    public function getTendanceActivites()
    {
        $dernierTrimestre = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })
            ->whereBetween('created_at', [Carbon::now()->subMonths(3), Carbon::now()])
            ->count();

        $trimestrePrecedent = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })
            ->whereBetween('created_at', [Carbon::now()->subMonths(6), Carbon::now()->subMonths(3)])
            ->count();

        if ($trimestrePrecedent == 0) return 0;

        return round((($dernierTrimestre - $trimestrePrecedent) / $trimestrePrecedent) * 100);
    }

    /**
     * Budget moyen mensuel
     */
    public function getBudgetMoyenMensuel()
    {
        $total = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })
            ->whereYear('created_at', $this->annee)
            ->sum('cout');

        $count = Activite::whereHas('extrant.objectif', function ($q) {
            $q->where('annee', $this->annee);
        })
            ->whereYear('created_at', $this->annee)
            ->count();

        return $count > 0 ? $total / $count : 0;
    }
}
