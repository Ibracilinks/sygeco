<?php

namespace App\Http\Controllers;

use App\Models\Resultat;
use App\Models\Objectif;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResultatController extends Controller
{
    /**
     * Liste des résultats
     */
    public function index(Request $request)
    {
        $query = Resultat::with('objectif');

        if ($request->filled('objectif_id')) {
            $query->where('objectif_id', $request->objectif_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('libelle', 'like', "%{$request->search}%");
            });
        }

        $resultats = $query->ordered()->paginate(15)->withQueryString();

        $objectifs = Objectif::where('statut', 'actif')->orderBy('annee', 'desc')->get();

        return view('pages.resultats.index', compact('resultats', 'objectifs'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $objectifs = Objectif::where('statut', 'actif')->orderBy('annee', 'desc')->get();
        $selectedObjectif = $request->get('objectif_id');

        return view('pages.resultats.create', compact('objectifs', 'selectedObjectif'));
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'objectif_id' => 'required|exists:objectifs,id',
            'code' => 'required|string|max:20|unique:resultats',
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $resultat = Resultat::create($validated);

        return redirect()->route('resultats.index')
            ->with('success', "Résultat {$resultat->code} créé avec succès.");
    }

    /**
     * Détail d'un résultat
     */
    public function show(Resultat $resultat)
    {
        $resultat->load(['objectif', 'extrants.activites.departement']);

        $activites = $resultat->extrants->flatMap->activites;
        $budgetTotal = $activites->sum('cout');
        $nbActivites = $activites->count();

        $stats = [
            'nb_extrants' => $resultat->extrants->count(),
            'nb_activites' => $nbActivites,
            'budget_total' => $budgetTotal,
            'budget_moyen' => $nbActivites ? $budgetTotal / $nbActivites : 0,
            'nb_departements' => $activites->pluck('departement_id')->filter()->unique()->count(),
        ];

        $activitesParStatut = [
            'brouillon' => 0,
            'soumis' => 0,
            'valide' => 0,
        ];

        foreach ($activites as $activite) {
            $activitesParStatut[$activite->statut] = ($activitesParStatut[$activite->statut] ?? 0) + 1;
        }

        $budgetParDepartement = $activites
            ->groupBy(fn($activite) => $activite->departement?->nom ?? 'Non affecté')
            ->map(fn($group) => $group->sum('cout'))
            ->sortDesc()
            ->toArray();

        $topExtrants = $resultat->extrants->map(function ($extrant) {
            return [
                'code' => $extrant->code,
                'libelle' => $extrant->libelle,
                'nb_activites' => $extrant->activites->count(),
                'budget' => $extrant->activites->sum('cout'),
            ];
        })->sortByDesc('budget')->take(6)->values()->toArray();

        $distributionBudgetaire = [
            '0 - 1M' => 0,
            '1M - 5M' => 0,
            '5M - 10M' => 0,
            '10M - 50M' => 0,
            '50M+' => 0,
        ];

        foreach ($activites as $activite) {
            $cout = $activite->cout;
            if ($cout < 1000000) {
                $distributionBudgetaire['0 - 1M']++;
            } elseif ($cout < 5000000) {
                $distributionBudgetaire['1M - 5M']++;
            } elseif ($cout < 10000000) {
                $distributionBudgetaire['5M - 10M']++;
            } elseif ($cout < 50000000) {
                $distributionBudgetaire['10M - 50M']++;
            } else {
                $distributionBudgetaire['50M+']++;
            }
        }

        $evolutionMensuelle = collect(range(5, 0))->map(function ($monthsAgo) use ($activites) {
            $date = Carbon::now()->subMonths($monthsAgo);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            return [
                'mois' => $date->isoFormat('MMM YYYY'),
                'nb_activites' => $activites->whereBetween('created_at', [$start, $end])->count(),
                'budget' => $activites->whereBetween('created_at', [$start, $end])->sum('cout'),
            ];
        })->toArray();

        return view('pages.resultats.show', compact(
            'resultat',
            'stats',
            'activitesParStatut',
            'budgetParDepartement',
            'topExtrants',
            'distributionBudgetaire',
            'evolutionMensuelle'
        ));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Resultat $resultat)
    {
        $objectifs = Objectif::where('statut', 'actif')->orderBy('annee', 'desc')->get();
        return view('pages.resultats.edit', compact('resultat', 'objectifs'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Resultat $resultat)
    {
        $validated = $request->validate([
            'objectif_id' => 'required|exists:objectifs,id',
            'code' => ['required', 'string', 'max:20', Rule::unique('resultats')->ignore($resultat->id)],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $resultat->update($validated);

        return redirect()->route('resultats.index')
            ->with('success', "Résultat {$resultat->code} mis à jour.");
    }

    /**
     * Suppression
     */
    public function destroy(Resultat $resultat)
    {
        if ($resultat->extrants()->count() > 0) {
            return redirect()->route('resultats.index')
                ->with('error', 'Impossible de supprimer un résultat qui a des extrants.');
        }

        $code = $resultat->code;
        $resultat->delete();

        return redirect()->route('resultats.index')
            ->with('success', "Résultat {$code} supprimé.");
    }

    /**
     * Activer/Désactiver
     */
    public function toggleStatus(Resultat $resultat)
    {
        $resultat->update(['is_active' => !$resultat->is_active]);

        $status = $resultat->is_active ? 'activé' : 'désactivé';

        return redirect()->route('resultats.index')
            ->with('success', "Résultat {$resultat->code} {$status}.");
    }
}
