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
        return view('dashboard', [
            'stats' => $this->dashboardService->getStats(),
            'budgetParObjectif' => $this->dashboardService->getBudgetParObjectif() ?: [],
            'topExtrants' => collect($this->dashboardService->getTopExtrants()) ?: [],
            'topActivites' => $this->dashboardService->getTopActivites() ?: [],
            'budgetParDepartement' => $this->dashboardService->getBudgetParDepartement() ?: [],
            'evolutionMensuelle' => $this->dashboardService->getEvolutionMensuelle() ?: [],
            'distributionBudgetaire' => $this->dashboardService->getDistributionBudgetaire() ?: [],
            'activitesParStatut' => $this->dashboardService->getActivitesParStatut() ?: [],
            'activitesParTrimestre' => $this->dashboardService->getActivitesParTrimestre() ?: [0, 0, 0, 0],
            'tendanceActivites' => $this->dashboardService->getTendanceActivites(),
            'budgetMoyenMensuel' => $this->dashboardService->getBudgetMoyenMensuel(),
        ]);
    }
}
