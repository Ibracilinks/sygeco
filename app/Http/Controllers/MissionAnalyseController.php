<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Support\AnalyseMissions;
use Illuminate\Http\Request;

/**
 * Vue d'ensemble des missions (toutes structures) : statistiques (destinations,
 * carburant, dépenses par poste), pour le chargé des missions et la DBCGOQ.
 */
class MissionAnalyseController extends Controller
{
    public function index(Request $request)
    {
        $annees = Mission::query()->whereNotNull('date_depart')
            ->pluck('date_depart')
            ->map(fn ($date) => (int) $date->year)
            ->push((int) now()->year)
            ->unique()->sortDesc()->values();

        $filters = [
            'annee' => (int) ($request->integer('annee') ?: now()->year),
            'type' => in_array($request->string('type')->toString(), array_keys(Mission::TYPES), true) ? $request->string('type')->toString() : '',
            'statut' => in_array($request->string('statut')->toString(), array_keys(Mission::STATUTS), true) ? $request->string('statut')->toString() : '',
        ];

        $missions = Mission::query()
            ->with(['participants:id,mission_id,nom_complet', 'etapes', 'departement:id,code,nom', 'departements:id,code,nom'])
            ->whereYear('date_depart', $filters['annee'])
            ->when($filters['type'] !== '', fn ($q) => $q->where('type', $filters['type']))
            ->when($filters['statut'] !== '', fn ($q) => $q->where('statut', $filters['statut']))
            ->orderByDesc('date_depart')
            ->orderByDesc('id')
            ->get();

        $stats = (new AnalyseMissions($missions))->statistiques();

        return view('pages.missions.analyse', compact('missions', 'stats', 'filters', 'annees'));
    }
}
