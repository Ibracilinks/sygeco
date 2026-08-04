<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\ActivitePieceJointe;
use App\Models\Departement;
use App\Models\Extrant;
use App\Models\User;
use App\Notifications\ActiviteCoutModifieNotification;
use App\Notifications\ActiviteRefusee;
use App\Notifications\ActiviteSoumiseNotification;
use App\Notifications\ActiviteValidee;
use App\Support\ActiveExercice;
use App\Support\CadreLogique;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
        $query = Activite::with(['extrant.objectif', 'extrant.resultat', 'departement', 'saisiePar']);

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

        // Périmètre : un chef voit son entité + tout son sous-arbre ; un agent, sa seule entité.
        if ($perimetre = Auth::user()?->perimetreActivitesIds()) {
            $query->whereIn('departement_id', $perimetre);
        }

        $summaryQuery = clone $query;
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'brouillon' => (clone $summaryQuery)->where('statut', 'brouillon')->count(),
            'en_attente' => (clone $summaryQuery)->where('statut', 'en_attente')->count(),
            'valide' => (clone $summaryQuery)->where('statut', 'valide')->count(),
            'rejete' => (clone $summaryQuery)->where('statut', 'rejete')->count(),
        ];

        // Présentation en cadre logique : Résultat → Extrant → activités. L'ordre SQL
        // suit la même hiérarchie pour qu'une page de pagination donne des blocs cohérents.
        $activites = $query
            ->leftJoin('extrants', 'extrants.id', '=', 'activites.extrant_id')
            ->leftJoin('resultats', 'resultats.id', '=', 'extrants.resultat_id')
            ->orderBy('resultats.ordre')
            ->orderBy('resultats.code')
            ->orderBy('extrants.ordre')
            ->orderBy('extrants.code')
            ->orderBy('activites.date_saisie', 'desc')
            ->select('activites.*')
            ->paginate(15)
            ->withQueryString();

        $groupes = $this->grouperParCadreLogique($activites->getCollection());

        $extrants = Extrant::query()
            ->with('objectif')
            ->actif()
            ->when($exerciceId !== null, fn ($q) => $q->whereHas('objectif', fn ($oq) => $oq->where('exercice_id', $exerciceId)))
            ->ordered()
            ->get();
        $departements = $this->departementsVisibles();
        $departementsGroupes = Departement::grouperParDirectionCentrale($departements);
        $statuts = ['brouillon', 'en_attente', 'valide', 'rejete'];
        $filters = $request->only(['search', 'extrant_id', 'departement_id', 'statut', 'trimestre']);

        return view('pages.activites.index', compact('activites', 'groupes', 'extrants', 'departements', 'departementsGroupes', 'statuts', 'summary', 'filters'));
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
        $departements = $this->departementsVisibles();
        $departementsGroupes = Departement::grouperParDirectionCentrale($departements);

        $departementId = Auth::user()->departement_id ?? $departements->first()?->id;

        $selectedExtrant = $request->get('extrant_id');
        $structuresGroupes = $this->structuresIntervenantesGroupes();

        return view('pages.activites.create', compact('extrants', 'departements', 'departementsGroupes', 'structuresGroupes', 'departementId', 'selectedExtrant'));
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $validated = $this->validerProgrammation($request, [
            'extrant_id' => 'required|exists:extrants,id',
            'departement_id' => 'required|exists:departements,id',
            'structures_intervenantes' => 'nullable|array',
            'structures_intervenantes.*' => 'integer|exists:departements,id',
            'nom_activite' => 'required|string',
            'indicateur_objectivement_verifiable' => 'required|string',
            'moyen_verification' => 'required|string',
            'cout' => 'required|numeric|min:0|max:'.Activite::MONTANT_MAX,
            'trimestre_1' => 'nullable|in:on,oui',
            'trimestre_2' => 'nullable|in:on,oui',
            'trimestre_3' => 'nullable|in:on,oui',
            'trimestre_4' => 'nullable|in:on,oui',
            'commentaires' => 'nullable|string',
        ]);

        $validated['departement_id'] = $this->departementAutorise($validated['departement_id']);

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

        $activite->departements()->sync($this->structuresIntervenantes($validated));

        return redirect()->route('activites.index')
            ->with('success', 'Activité créée avec succès.');
    }

    /**
     * Enregistrement d'une activité NON PROGRAMMÉE depuis le suivi-évaluation.
     * Formulaire allégé : pas d'extrant, rattachement direct à l'exercice actif.
     */
    public function storeNonProgrammee(Request $request)
    {
        if (! Auth::user()->can('edit_activites')) {
            abort(403);
        }

        $exerciceId = ActiveExercice::id();

        if (! $exerciceId) {
            return back()->with('error', "Aucun exercice actif : impossible d'enregistrer une activité non programmée.");
        }

        $validated = $request->validate([
            'departement_id' => 'required|exists:departements,id',
            'nom_activite' => 'required|string',
            'cout' => 'required|numeric|min:0|max:'.Activite::MONTANT_MAX,
            'indicateur_objectivement_verifiable' => 'nullable|string',
            'moyen_verification' => 'nullable|string',
            'statut_execution' => ['nullable', Rule::in(array_keys(Activite::STATUTS_EXECUTION))],
            'trimestre_1' => 'nullable|in:on,oui',
            'trimestre_2' => 'nullable|in:on,oui',
            'trimestre_3' => 'nullable|in:on,oui',
            'trimestre_4' => 'nullable|in:on,oui',
            'commentaires' => 'nullable|string',
        ]);

        $validated['departement_id'] = $this->departementAutorise($validated['departement_id']);

        $activite = new Activite();
        $activite->extrant_id = null;
        $activite->exercice_id = $exerciceId;
        $activite->non_programmee = true;
        $activite->departement_id = $validated['departement_id'];
        $activite->nom_activite = $validated['nom_activite'];
        $activite->indicateur_objectivement_verifiable = $validated['indicateur_objectivement_verifiable'] ?? 'Non spécifié (activité non programmée)';
        $activite->moyen_verification = $validated['moyen_verification'] ?? 'Non spécifié (activité non programmée)';
        $activite->cout = $validated['cout'];
        $activite->trimestre_1 = isset($validated['trimestre_1']) ? 'oui' : 'non';
        $activite->trimestre_2 = isset($validated['trimestre_2']) ? 'oui' : 'non';
        $activite->trimestre_3 = isset($validated['trimestre_3']) ? 'oui' : 'non';
        $activite->trimestre_4 = isset($validated['trimestre_4']) ? 'oui' : 'non';
        // Recorded post-hoc : validée directement si l'utilisateur peut valider, sinon soumise au circuit.
        $activite->statut = Auth::user()->can('validate_activites') ? 'valide' : 'en_attente';
        $activite->statut_execution = $validated['statut_execution'] ?? 'realise';
        $activite->saisi_par = Auth::id();
        $activite->date_saisie = now();
        $activite->commentaires = $validated['commentaires'] ?? null;
        $activite->save();

        return redirect()->route('evaluations.index', 'mi-parcours')
            ->with('success', 'Activité non programmée enregistrée dans le suivi.');
    }

    /**
     * Détail d'une activité
     */
    public function show(Activite $activite)
    {
        $activite->load([
            'extrant.objectif',
            'extrant.resultat',
            'departement.responsable',
            'departement.parent.parent',
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
        $departements = $this->departementsVisibles();
        $departementsGroupes = Departement::grouperParDirectionCentrale($departements);
        $structuresGroupes = $this->structuresIntervenantesGroupes();

        $activite->load('departements');

        return view('pages.activites.edit', compact('activite', 'extrants', 'departements', 'departementsGroupes', 'structuresGroupes'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Activite $activite)
    {
        $validated = $this->validerProgrammation($request, [
            'extrant_id' => 'required|exists:extrants,id',
            'departement_id' => 'required|exists:departements,id',
            'structures_intervenantes' => 'nullable|array',
            'structures_intervenantes.*' => 'integer|exists:departements,id',
            'nom_activite' => 'required|string',
            'indicateur_objectivement_verifiable' => 'required|string',
            'moyen_verification' => 'required|string',
            'cout' => 'required|numeric|min:0|max:'.Activite::MONTANT_MAX,
            'trimestre_1' => 'nullable|in:on,oui',
            'trimestre_2' => 'nullable|in:on,oui',
            'trimestre_3' => 'nullable|in:on,oui',
            'trimestre_4' => 'nullable|in:on,oui',
            'commentaires' => 'nullable|string',
        ]);

        $validated['departement_id'] = $this->departementAutorise($validated['departement_id']);

        $ancienCout = (float) $activite->cout;

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

        $activite->departements()->sync($this->structuresIntervenantes($validated));

        $this->notifierChangementCout($activite, $ancienCout, (float) $validated['cout'], 'edition');

        return redirect()->route('activites.index')
            ->with('success', 'Activité mise à jour.');
    }

    /**
     * Notifie le directeur de la Direction Centrale et le chef de service concernés
     * lorsqu'un coût d'activité change réellement.
     */
    private function notifierChangementCout(Activite $activite, float $ancienCout, float $nouveauCout, string $contexte, ?string $motif = null): void
    {
        if ($ancienCout === $nouveauCout) {
            return;
        }

        foreach ($activite->destinatairesChangementBudget() as $destinataire) {
            $destinataire->notify(new ActiviteCoutModifieNotification($activite, $ancienCout, $nouveauCout, $contexte, $motif));
        }
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
    /**
     * Départements proposés dans les filtres, restreints au périmètre de
     * l'utilisateur (sous-arbre pour un chef, entité propre pour un agent).
     *
     * @return \Illuminate\Support\Collection<int, Departement>
     */
    /**
     * Valide une saisie de programmation en exigeant, en plus des règles fournies,
     * au moins un trimestre coché dans le chronogramme.
     *
     * @param  array<string, mixed>  $regles
     * @return array<string, mixed>
     */
    private function validerProgrammation(Request $request, array $regles): array
    {
        $validator = Validator::make($request->all(), $regles);

        $validator->after(function ($validator) use ($request) {
            $trimestres = ['trimestre_1', 'trimestre_2', 'trimestre_3', 'trimestre_4'];

            if (! collect($trimestres)->contains(fn ($trimestre) => $request->filled($trimestre))) {
                $validator->errors()->add(
                    'chronogramme',
                    'Le chronogramme est obligatoire : sélectionnez au moins un trimestre.'
                );
            }
        });

        return $validator->validate();
    }

    /**
     * Regroupe une page d'activités en cadre logique : Résultat → Extrant → activités.
     * L'ordre des blocs suit celui de la collection reçue (déjà trié en SQL).
     *
     * @param  \Illuminate\Support\Collection<int, Activite>  $activites
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function grouperParCadreLogique($activites): \Illuminate\Support\Collection
    {
        return $activites
            ->groupBy(fn (Activite $activite) => $activite->extrant?->resultat_id ?? 'sans-resultat')
            ->map(fn ($parResultat) => [
                'resultat' => $parResultat->first()->extrant?->resultat,
                'cout_total' => (float) $parResultat->sum('cout'),
                'nb_activites' => $parResultat->count(),
                'extrants' => $parResultat
                    ->groupBy(fn (Activite $activite) => $activite->extrant_id ?? 'sans-extrant')
                    ->map(fn ($parExtrant) => [
                        'extrant' => $parExtrant->first()->extrant,
                        'activites' => $parExtrant->values(),
                        'cout_total' => (float) $parExtrant->sum('cout'),
                    ])
                    ->values(),
            ])
            ->values();
    }

    /**
     * Structures intervenantes retenues : entités participantes distinctes de la
     * structure porteuse (elle est déjà portée par `departement_id`).
     *
     * @param  array<string, mixed>  $validated
     * @return array<int, int>
     */
    private function structuresIntervenantes(array $validated): array
    {
        return collect($validated['structures_intervenantes'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === (int) $validated['departement_id'])
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Entités proposées comme structures intervenantes : toute l'organisation,
     * une activité pouvant mobiliser des entités hors du périmètre de son porteur.
     *
     * @return array<string, array<int, Departement>>
     */
    private function structuresIntervenantesGroupes(): array
    {
        return Departement::grouperParDirectionCentrale(Departement::active()->ordered()->get());
    }

    /**
     * Structure retenue pour l'activité, ramenée au périmètre de l'utilisateur.
     *
     * Un chef choisit librement parmi les entités de son sous-arbre (sa Direction
     * Centrale et les services qu'elle chapeaute) ; une structure hors périmètre
     * est ramenée à son entité de rattachement plutôt qu'acceptée telle quelle.
     * Les profils non restreints (superadmin, dbcgoq) gardent leur choix.
     */
    private function departementAutorise($departementId): int
    {
        $perimetre = Auth::user()?->perimetreActivitesIds();

        if ($perimetre === null || in_array((int) $departementId, $perimetre, true)) {
            return (int) $departementId;
        }

        return (int) (Auth::user()->departement_id ?? $departementId);
    }

    private function departementsVisibles()
    {
        $query = Departement::active()->ordered();

        if ($perimetre = Auth::user()?->perimetreActivitesIds()) {
            $query->whereIn('id', $perimetre);
        }

        return $query->get();
    }

    protected function notifierValidateurs(Activite $activite): void
    {
        $destinataires = User::query()
            ->role('chef')
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
