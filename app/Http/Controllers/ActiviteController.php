<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Extrant;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiviteController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Activite::class, 'activite', [
            'except' => ['valider', 'refuser', 'soumettre'],
        ]);
    }

    /**
     * Liste des activités
     */
    public function index(Request $request)
    {
        $query = Activite::with(['extrant.objectif', 'departement', 'saisiePar']);

        $exerciceId = ActiveExercice::id();
        $query->forExercice($exerciceId);

        // Filtres
        if ($request->filled('extrant_id')) {
            $query->where('extrant_id', $request->extrant_id);
        }

        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('trimestre')) {
            $query->pourTrimestre($request->trimestre);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom_activite', 'like', "%{$request->search}%")
                    ->orWhere('indicateur_objectivement_verifiable', 'like', "%{$request->search}%");
            });
        }

        // Si l'utilisateur est chef de département, filtrer par son département
        if (Auth::user()->hasRole('chef_departement') && Auth::user()->departement_id) {
            $query->where('departement_id', Auth::user()->departement_id);
        }

        $activites = $query->orderBy('date_saisie', 'desc')->paginate(15)->withQueryString();

        $extrants = Extrant::query()
            ->with('objectif')
            ->actif()
            ->when($exerciceId !== null, fn ($q) => $q->whereHas('objectif', fn ($oq) => $oq->where('exercice_id', $exerciceId)))
            ->ordered()
            ->get();
        $departements = Departement::active()->ordered()->get();
        $statuts = ['brouillon', 'soumis', 'valide'];

        return view('pages.activites.index', compact('activites', 'extrants', 'departements', 'statuts'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $exerciceId = ActiveExercice::id();
        $extrants = Extrant::query()
            ->with('objectif')
            ->actif()
            ->when($exerciceId !== null, fn ($q) => $q->whereHas('objectif', fn ($oq) => $oq->where('exercice_id', $exerciceId)))
            ->ordered()
            ->get();
        $departements = Departement::active()->ordered()->get();

        $departementId = Auth::user()->departement_id ?? $departements->first()?->id;

        $selectedExtrant = $request->get('extrant_id');

        return view('pages.activites.create', compact('extrants', 'departements', 'departementId', 'selectedExtrant'));
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'extrant_id' => 'required|exists:extrants,id',
            'departement_id' => 'required|exists:departements,id',
            'nom_activite' => 'required|string',
            'indicateur_objectivement_verifiable' => 'required|string',
            'moyen_verification' => 'required|string',
            'cout' => 'required|numeric|min:0',
            'trimestre_1' => 'nullable|in:on,oui',
            'trimestre_2' => 'nullable|in:on,oui',
            'trimestre_3' => 'nullable|in:on,oui',
            'trimestre_4' => 'nullable|in:on,oui',
            'commentaires' => 'nullable|string',
        ]);

        if (Auth::user()->hasRole('chef_departement') && Auth::user()->departement_id) {
            $validated['departement_id'] = Auth::user()->departement_id;
        }

        $activite = new Activite();
        $activite->extrant_id = $validated['extrant_id'];
        $activite->departement_id = $validated['departement_id'];
        $activite->nom_activite = $validated['nom_activite'];
        $activite->indicateur_objectivement_verifiable = $validated['indicateur_objectivement_verifiable'];
        $activite->moyen_verification = $validated['moyen_verification'];
        $activite->cout = $validated['cout'];
        $activite->trimestre_1 = isset($validated['trimestre_1']) ? 'oui' : 'non';
        $activite->trimestre_2 = isset($validated['trimestre_2']) ? 'oui' : 'non';
        $activite->trimestre_3 = isset($validated['trimestre_3']) ? 'oui' : 'non';
        $activite->trimestre_4 = isset($validated['trimestre_4']) ? 'oui' : 'non';
        $activite->saisi_par = Auth::id();
        $activite->date_saisie = now();
        $activite->commentaires = $validated['commentaires'] ?? null;
        $activite->save();

        return redirect()->route('activites.index')
            ->with('success', 'Activité créée avec succès.');
    }

    /**
     * Détail d'une activité
     */
    public function show(Activite $activite)
    {
        $activite->load([
            'extrant.objectif',
            'departement.responsable',
            'saisiePar',
            'validePar',
            'refusePar',
            'validationHistoriques.utilisateur',
        ]);

        return view('pages.activites.show', compact('activite'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Activite $activite)
    {
        $exerciceId = ActiveExercice::id();
        $extrants = Extrant::query()
            ->with('objectif')
            ->actif()
            ->when($exerciceId !== null, fn ($q) => $q->whereHas('objectif', fn ($oq) => $oq->where('exercice_id', $exerciceId)))
            ->ordered()
            ->get();
        $departements = Departement::active()->ordered()->get();

        return view('pages.activites.edit', compact('activite', 'extrants', 'departements'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Activite $activite)
    {
        $validated = $request->validate([
            'extrant_id' => 'required|exists:extrants,id',
            'departement_id' => 'required|exists:departements,id',
            'nom_activite' => 'required|string',
            'indicateur_objectivement_verifiable' => 'required|string',
            'moyen_verification' => 'required|string',
            'cout' => 'required|numeric|min:0',
            'trimestre_1' => 'nullable|in:on,oui',
            'trimestre_2' => 'nullable|in:on,oui',
            'trimestre_3' => 'nullable|in:on,oui',
            'trimestre_4' => 'nullable|in:on,oui',
            'commentaires' => 'nullable|string',
        ]);

        if (Auth::user()->hasRole('chef_departement') && Auth::user()->departement_id) {
            $validated['departement_id'] = Auth::user()->departement_id;
        }

        $activite->update([
            'extrant_id' => $validated['extrant_id'],
            'departement_id' => $validated['departement_id'],
            'nom_activite' => $validated['nom_activite'],
            'indicateur_objectivement_verifiable' => $validated['indicateur_objectivement_verifiable'],
            'moyen_verification' => $validated['moyen_verification'],
            'cout' => $validated['cout'],
            'trimestre_1' => isset($validated['trimestre_1']) ? 'oui' : 'non',
            'trimestre_2' => isset($validated['trimestre_2']) ? 'oui' : 'non',
            'trimestre_3' => isset($validated['trimestre_3']) ? 'oui' : 'non',
            'trimestre_4' => isset($validated['trimestre_4']) ? 'oui' : 'non',
            'commentaires' => $validated['commentaires'] ?? null,
        ]);

        return redirect()->route('activites.index')
            ->with('success', 'Activité mise à jour.');
    }

    /**
     * Suppression
     */
    public function destroy(Activite $activite)
    {
        $activite->delete();

        return redirect()->route('activites.index')
            ->with('success', 'Activité supprimée.');
    }

    /**
     * Soumettre une activité (brouillon -> soumis)
     */
    public function soumettre(Activite $activite)
    {
        $this->authorize('submit', $activite);

        if ($activite->soumettre()) {
            return redirect()->route('activites.index')
                ->with('success', 'Activité soumise avec succès.');
        }

        return redirect()->route('activites.index')
            ->with('error', 'Impossible de soumettre cette activité.');
    }

    /**
     * Valider une activité (soumis -> valide) - Réservé DBCGOQ
     */
    public function valider(Activite $activite)
    {
        if (!Auth::user()->can('validate_activites')) {
            return redirect()->route('activites.index')
                ->with('error', 'Vous ne pouvez pas valider cette activité.');
        }

        if ($activite->valider()) {
            return redirect()->route('activites.index')
                ->with('success', 'Activité validée avec succès.');
        }

        return redirect()->route('activites.index')
            ->with('error', 'Impossible de valider cette activité.');
    }

    /**
     * Refuser une activité (soumis -> brouillon)
     */
    public function refuser(Request $request, Activite $activite)
    {
        if (!Auth::user()->can('validate_activites')) {
            return redirect()->route('activites.show', $activite)
                ->with('error', 'Vous ne pouvez pas refuser cette activité.');
        }

        $validated = $request->validate([
            'motif_refus' => 'required|string|min:10',
        ]);

        if ($activite->refuser($validated['motif_refus'])) {
            return redirect()->route('activites.show', $activite)
                ->with('success', 'Activité refusée et renvoyée en brouillon.');
        }

        return redirect()->route('activites.show', $activite)
            ->with('error', 'Impossible de refuser cette activité.');
    }
}
