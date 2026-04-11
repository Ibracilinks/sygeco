<?php

namespace App\Http\Controllers;

use App\Models\Objectif;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ObjectifController extends Controller
{
    /**
     * Liste des objectifs
     */
    public function index(Request $request)
    {
        $query = Objectif::query();

        if ($request->filled('annee')) {
            $query->where('annee', $request->annee);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('libelle', 'like', "%{$request->search}%");
            });
        }

        $objectifs = $query->ordered()->paginate(15)->withQueryString();

        $annees = Objectif::select('annee')->distinct()->orderBy('annee', 'desc')->pluck('annee');

        return view('pages.objectifs.index', compact('objectifs', 'annees'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('pages.objectifs.create');
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:objectifs',
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'annee' => 'required|integer|min:2000|max:2100',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'ordre' => 'nullable|integer',
        ]);

        $objectif = Objectif::create($validated);

        return redirect()->route('objectifs.index')
            ->with('success', "Objectif {$objectif->code} créé avec succès.");
    }

    /**
     * Détail d'un objectif
     */
    public function show(Objectif $objectif)
    {
        $objectif->load(['extrants' => function ($query) {
            $query->withCount('activites');
            $query->with(['activites' => function ($q) {
                $q->latest();
            }]);
        }]);

        // Calcul des statistiques
        $budgetTotal = 0;
        $nbActivites = 0;

        foreach ($objectif->extrants as $extrant) {
            $budgetTotal += $extrant->activites->sum('cout');
            $nbActivites += $extrant->activites->count();
        }

        $budgetMoyen = $nbActivites > 0 ? $budgetTotal / $nbActivites : 0;

        $stats = [
            'nb_extrants' => $objectif->extrants->count(),
            'nb_activites' => $nbActivites,
            'budget_total' => $budgetTotal,
            'budget_moyen' => $budgetMoyen,
            'taux_activites' => $nbActivites > 0 ? 100 : 0, // À adapter selon votre logique
        ];

        return view('pages.objectifs.show', compact('objectif', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Objectif $objectif)
    {
        return view('pages.objectifs.edit', compact('objectif'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Objectif $objectif)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('objectifs')->ignore($objectif->id)],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'annee' => 'required|integer|min:2000|max:2100',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'ordre' => 'nullable|integer',
        ]);

        $objectif->update($validated);

        return redirect()->route('objectifs.index')
            ->with('success', "Objectif {$objectif->code} mis à jour.");
    }

    /**
     * Suppression
     */
    public function destroy(Objectif $objectif)
    {
        if ($objectif->extrants()->count() > 0) {
            return redirect()->route('objectifs.index')
                ->with('error', 'Impossible de supprimer un objectif qui a des extrants.');
        }

        $code = $objectif->code;
        $objectif->delete();

        return redirect()->route('objectifs.index')
            ->with('success', "Objectif {$code} supprimé.");
    }

    /**
     * Activer/Désactiver un objectif
     */
    public function toggleStatut(Objectif $objectif)
    {
        $nouveauStatut = $objectif->statut === 'actif' ? 'inactif' : 'actif';
        $objectif->update(['statut' => $nouveauStatut]);

        $message = $nouveauStatut === 'actif' ? 'activé' : 'désactivé';

        return redirect()->route('objectifs.index')
            ->with('success', "Objectif {$objectif->code} {$message}.");
    }
}
