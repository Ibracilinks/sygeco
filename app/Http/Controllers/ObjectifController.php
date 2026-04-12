<?php

namespace App\Http\Controllers;

use App\Models\Exercice;
use App\Models\Objectif;
use App\Support\ActiveExercice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ObjectifController extends Controller
{
    /**
     * Liste des objectifs
     */
    public function index(Request $request)
    {
        $query = Objectif::query()->with('exercice');

        if ($request->has('exercice_id') && $request->exercice_id === '') {
            $exerciceId = null;
        } elseif ($request->filled('exercice_id')) {
            $exerciceId = (int) $request->exercice_id;
        } else {
            $exerciceId = ActiveExercice::id();
        }
        $query->forExercice($exerciceId);

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
        $exercices = Exercice::query()->ordered()->get();

        return view('pages.objectifs.index', compact('objectifs', 'annees', 'exercices', 'exerciceId'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $exercices = Exercice::query()->ordered()->get();
        $defaultExerciceId = ActiveExercice::id();

        return view('pages.objectifs.create', compact('exercices', 'defaultExerciceId'));
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exercice_id' => 'required|exists:exercices,id',
            'code' => 'required|string|max:20|unique:objectifs',
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'ordre' => 'nullable|integer',
        ]);

        $exercice = Exercice::query()->findOrFail($validated['exercice_id']);
        $validated['annee'] = $exercice->annee;

        $objectif = Objectif::create($validated);

        return redirect()->route('objectifs.index')
            ->with('success', "Objectif {$objectif->code} créé avec succès.");
    }

    /**
     * Détail d'un objectif
     */
    public function show(Objectif $objectif)
    {
        // Charger toute la hiérarchie : Résultats → Extrants → Activités
        $objectif->load([
            'resultats' => function ($query) {
                $query->orderBy('ordre')->orderBy('code');
                $query->with([
                    'extrants' => function ($q) {
                        $q->orderBy('ordre')->orderBy('code');
                        $q->with([
                            'activites' => function ($a) {
                                $a->orderBy('created_at', 'desc');
                            }
                        ]);
                        $q->withCount('activites');
                    }
                ]);
                $query->withCount('extrants');
            }
        ]);

        // Calcul des statistiques globales
        $budgetTotal = 0;
        $nbActivites = 0;
        $nbExtrants = 0;
        $nbResultats = $objectif->resultats->count();

        foreach ($objectif->resultats as $resultat) {
            $nbExtrants += $resultat->extrants->count();

            foreach ($resultat->extrants as $extrant) {
                $budgetTotal += $extrant->activites->sum('cout');
                $nbActivites += $extrant->activites->count();
            }
        }

        $budgetMoyen = $nbActivites > 0 ? $budgetTotal / $nbActivites : 0;

        $stats = [
            'nb_resultats' => $nbResultats,
            'nb_extrants' => $nbExtrants,
            'nb_activites' => $nbActivites,
            'budget_total' => $budgetTotal,
            'budget_moyen' => $budgetMoyen,
            'taux_activites' => $nbActivites > 0 ? 100 : 0,
        ];

        return view('pages.objectifs.show', compact('objectif', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Objectif $objectif)
    {
        $exercices = Exercice::query()->ordered()->get();

        return view('pages.objectifs.edit', compact('objectif', 'exercices'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Objectif $objectif)
    {
        $validated = $request->validate([
            'exercice_id' => 'required|exists:exercices,id',
            'code' => ['required', 'string', 'max:20', Rule::unique('objectifs')->ignore($objectif->id)],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'ordre' => 'nullable|integer',
        ]);

        $exercice = Exercice::query()->findOrFail($validated['exercice_id']);
        $validated['annee'] = $exercice->annee;

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
