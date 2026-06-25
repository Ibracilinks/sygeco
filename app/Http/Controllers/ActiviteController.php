<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\ActivitePieceJointe;
use App\Models\Departement;
use App\Models\Extrant;
use App\Models\User;
use App\Notifications\ActiviteRefusee;
use App\Notifications\ActiviteSoumiseNotification;
use App\Notifications\ActiviteValidee;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

        if ($request->filled('statut_execution')) {
            $query->where('statut_execution', $request->statut_execution);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom_activite', 'like', "%{$request->search}%")
                    ->orWhere('indicateur_objectivement_verifiable', 'like', "%{$request->search}%");
            });
        }

        // Si l'utilisateur est chef de département, filtrer par son département
        if ((Auth::user()->hasRole('chef_departement') || Auth::user()->hasRole('agent')) && Auth::user()->departement_id) {
            $query->where('departement_id', Auth::user()->departement_id);
        }

        $summaryQuery = clone $query;
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'brouillon' => (clone $summaryQuery)->where('statut', 'brouillon')->count(),
            'soumis' => (clone $summaryQuery)->where('statut', 'soumis')->count(),
            'valide' => (clone $summaryQuery)->where('statut', 'valide')->count(),
        ];

        $activites = $query->orderBy('date_saisie', 'desc')->paginate(15)->withQueryString();

        $extrants = Extrant::query()
            ->with('objectif')
            ->actif()
            ->when($exerciceId !== null, fn ($q) => $q->whereHas('objectif', fn ($oq) => $oq->where('exercice_id', $exerciceId)))
            ->ordered()
            ->get();
        $departements = Departement::active()->ordered()->get();
        $statuts = ['brouillon', 'soumis', 'valide'];
        $filters = $request->only(['search', 'extrant_id', 'departement_id', 'statut', 'trimestre', 'statut_execution']);

        return view('pages.activites.index', compact('activites', 'extrants', 'departements', 'statuts', 'summary', 'filters'));
    }

    /**
     * Suivi de l'exécution des activités (réalisé / en cours / non réalisé) avec observations.
     */
    public function suivi(Request $request)
    {
        $exerciceId = ActiveExercice::id();

        $query = Activite::with(['extrant', 'departement'])->forExercice($exerciceId);

        if ((Auth::user()->hasRole('chef_departement') || Auth::user()->hasRole('agent')) && Auth::user()->departement_id) {
            $query->where('departement_id', Auth::user()->departement_id);
        }

        if ($request->filled('extrant_id')) {
            $query->where('extrant_id', $request->extrant_id);
        }
        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }
        if ($request->filled('statut_execution')) {
            $query->where('statut_execution', $request->statut_execution);
        }
        if ($request->filled('search')) {
            $query->where('nom_activite', 'like', "%{$request->search}%");
        }

        $base = (clone $query);
        $summary = [
            'total' => (clone $base)->count(),
            'non_realise' => (clone $base)->where('statut_execution', 'non_realise')->count(),
            'en_cours' => (clone $base)->where('statut_execution', 'en_cours')->count(),
            'realise' => (clone $base)->where('statut_execution', 'realise')->count(),
        ];
        $summary['taux_realisation'] = $summary['total'] > 0
            ? round($summary['realise'] / $summary['total'] * 100, 1)
            : 0.0;

        $activites = $query->orderBy('extrant_id')->orderBy('id')->paginate(20)->withQueryString();

        $extrants = Extrant::query()
            ->with('objectif')
            ->actif()
            ->when($exerciceId !== null, fn ($q) => $q->whereHas('objectif', fn ($oq) => $oq->where('exercice_id', $exerciceId)))
            ->ordered()
            ->get();
        $departements = Departement::active()->ordered()->get();
        $filters = $request->only(['search', 'extrant_id', 'departement_id', 'statut_execution']);

        // Fenêtre de saisie de l'exécution : ouverte pour le dbcgoq en permanence,
        // sinon uniquement pendant le mi-parcours ou l'évaluation de l'exercice actif.
        $exercice = ActiveExercice::model();
        $periodeSuivi = $exercice?->periodeSuiviCourante();
        $peutSaisirExecution = Auth::user()->can('validate_activites')
            || ($exercice?->enPeriodeSuiviExecution() ?? false);

        return view('pages.activites.suivi', compact('activites', 'extrants', 'departements', 'summary', 'filters', 'exercice', 'periodeSuivi', 'peutSaisirExecution'));
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
            'departements',
            'executionMajPar',
            'piecesJointes.auteur',
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
            $this->notifierValidateurs($activite);

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
            if ($activite->saisiePar) {
                $activite->saisiePar->notify(new ActiviteValidee($activite));
            }

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
            if ($activite->saisiePar) {
                $activite->saisiePar->notify(new ActiviteRefusee($activite, $validated['motif_refus']));
            }

            return redirect()->route('activites.show', $activite)
                ->with('success', 'Activité refusée et renvoyée en brouillon.');
        }

        return redirect()->route('activites.show', $activite)
            ->with('error', 'Impossible de refuser cette activité.');
    }

    /**
     * Notifie qu'une activité vient d'être soumise.
     *
     * Cible « par département » : le ou les chefs du département de l'activité (hors auteur
     * de la soumission). Si le département n'a pas de chef, on prévient les validateurs
     * centraux (permission validate_activites) pour que la soumission ne passe pas inaperçue.
     */
    protected function notifierValidateurs(Activite $activite): void
    {
        $destinataires = User::query()
            ->role('chef_departement')
            ->where('departement_id', $activite->departement_id)
            ->where('id', '!=', Auth::id())
            ->get();

        if ($destinataires->isEmpty()) {
            $destinataires = User::query()->permission('validate_activites')->get();
        }

        if ($destinataires->isNotEmpty()) {
            Notification::send($destinataires, new ActiviteSoumiseNotification($activite));
        }
    }

    /**
     * Mettre à jour le suivi d'exécution (Track Activité : réalisé / en cours / non réalisé).
     */
    public function updateExecution(Request $request, Activite $activite)
    {
        $user = Auth::user();

        if (! $user->can('edit_activites') && ! $user->can('validate_activites')) {
            return back()->with('error', "Vous n'êtes pas autorisé à mettre à jour le suivi d'exécution.");
        }

        // Les chefs de département ne peuvent renseigner l'exécution que pendant une fenêtre
        // ouverte (mi-parcours ou évaluation). Le dbcgoq (validate_activites) garde l'accès permanent.
        if (! $user->can('validate_activites')) {
            $exercice = $activite->exercice();

            if (! $exercice || ! $exercice->enPeriodeSuiviExecution()) {
                return back()->with('error', "La saisie de l'exécution n'est ouverte que pendant les périodes de mi-parcours ou d'évaluation.");
            }
        }

        $validated = $request->validate([
            'statut_execution' => ['required', Rule::in(array_keys(Activite::STATUTS_EXECUTION))],
            'execution_commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $activite->update([
            'statut_execution' => $validated['statut_execution'],
            'execution_commentaire' => $validated['execution_commentaire'] ?? null,
            'execution_maj_le' => now(),
            'execution_maj_par' => Auth::id(),
        ]);

        return back()->with('success', "Suivi d'exécution mis à jour.");
    }

    public function storePieceJointe(Request $request, Activite $activite)
    {
        $this->authorize('view', $activite);

        if (! Auth::user()->can('edit_activites') && ! Auth::user()->can('validate_activites')) {
            return back()->with('error', "Vous n'êtes pas autorisé à ajouter des fichiers.");
        }

        $validated = $request->validate([
            'fichier' => ['required', 'file', 'max:10240'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $uploadedFile = $validated['fichier'];
        $path = $uploadedFile->store("activites/{$activite->id}", 'public');

        $activite->piecesJointes()->create([
            'user_id' => Auth::id(),
            'nom_original' => $uploadedFile->getClientOriginalName(),
            'chemin' => $path,
            'mime_type' => $uploadedFile->getClientMimeType(),
            'taille' => $uploadedFile->getSize(),
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Fichier ajouté avec succès.');
    }

    public function downloadPieceJointe(Activite $activite, ActivitePieceJointe $pieceJointe)
    {
        $this->authorize('view', $activite);

        abort_unless((int) $pieceJointe->activite_id === (int) $activite->id, 404);

        return Storage::disk('public')->download($pieceJointe->chemin, $pieceJointe->nom_original);
    }
}
