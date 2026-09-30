<?php

namespace App\Http\Controllers;

use App\Models\Exercice;
use App\Services\DashboardDataService;
use App\Support\ActiveExercice;
use Illuminate\Http\Request;

/**
 * Dashboard parallèle « SAP Cloud Analytics » : même données métier que le tableau de bord
 * principal, mais présentation autonome calquée sur SAP Analytics Cloud (hors layout de base).
 */
class SapAnalyticsController extends Controller
{
    public function __construct(
        protected DashboardDataService $dashboardService
    ) {}

    public function index(Request $request)
    {
        // Comme le tableau de bord principal, le sélecteur d'exercice propage le contexte
        // à toute l'application via la session.
        if ($request->filled('annee')) {
            $exercice = Exercice::query()->where('annee', (int) $request->query('annee'))->first();

            if ($exercice) {
                ActiveExercice::set((int) $exercice->id);
            }
        }

        return view('sap.analytics', $this->dashboardService->getDashboardPayload());
    }
}
