<?php

namespace App\Http\Controllers;

use App\Services\DashboardDataService;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardDataService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        // Only load data that's actually used in the view
        return view('dashboard', [
            'stats' => $this->dashboardService->getStats(),
            'budgetParObjectif' => $this->dashboardService->getBudgetParObjectif(),
            'topExtrants' => collect($this->dashboardService->getTopExtrants()),
            'topActivites' => $this->dashboardService->getTopActivites(),
            'evolutionMensuelle' => $this->dashboardService->getEvolutionMensuelle(),
            'distributionBudgetaire' => $this->dashboardService->getDistributionBudgetaire(),
            'activitesParStatut' => $this->dashboardService->getActivitesParStatut(),
            'activitesParTrimestre' => $this->dashboardService->getActivitesParTrimestre(),
        ]);
    }
}
