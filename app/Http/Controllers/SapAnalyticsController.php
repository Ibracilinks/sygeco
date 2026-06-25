<?php

namespace App\Http\Controllers;

use App\Services\DashboardDataService;

/**
 * Dashboard parallèle « SAP Cloud Analytics » : même données métier que le tableau de bord
 * principal, mais présentation autonome calquée sur SAP Analytics Cloud (hors layout de base).
 */
class SapAnalyticsController extends Controller
{
    public function __construct(
        protected DashboardDataService $dashboardService
    ) {}

    public function index()
    {
        return view('sap.analytics', $this->dashboardService->getDashboardPayload());
    }
}
