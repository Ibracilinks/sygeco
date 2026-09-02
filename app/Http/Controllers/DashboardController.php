<?php

namespace App\Http\Controllers;

use App\Models\Exercice;
use App\Services\DashboardDataService;
use App\Support\ActiveExercice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardDataService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        // Sans vue sur le PTA, le chargé des missions atterrit sur ses missions.
        if ($request->user()?->isServiceBudget() && ! $request->user()->suitLePta()) {
            return redirect()->route('missions.index');
        }

        // Le sélecteur d'année du tableau de bord doit changer le contexte d'exercice
        // pour toute l'application (objectifs, extrants, activités…), et non uniquement
        // l'affichage du dashboard : on persiste donc l'exercice choisi en session.
        $this->syncActiveExercice($request);

        return view('dashboard', $this->dashboardService->getDashboardPayload());
    }

    /**
     * Aligne l'exercice actif de la session sur l'année sélectionnée dans l'URL.
     */
    protected function syncActiveExercice(Request $request): void
    {
        if (! $request->filled('annee')) {
            return;
        }

        $exercice = Exercice::query()->where('annee', (int) $request->query('annee'))->first();

        if ($exercice) {
            ActiveExercice::set((int) $exercice->id);
        }
    }
}
