<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreObjectifRequest;
use App\Http\Requests\UpdateObjectifRequest;
use App\Models\Exercice;
use App\Models\Objectif;
use App\Support\ActiveExercice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObjectifController extends Controller
{
    /**
     * Liste des objectifs
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'exercice_id' => $request->has('exercice_id') ? (string) $request->string('exercice_id') : (string) ActiveExercice::id(),
            'annee' => trim((string) $request->string('annee')),
            'statut' => trim((string) $request->string('statut')),
            'sort' => (string) $request->string('sort', 'ordre'),
            'direction' => (string) $request->string('direction', 'asc'),
        ];

        // Résultats et extrants appartiennent au couple « exercice + objectif » :
        // ils ne sont comptés que pour l'exercice filtré.
        $exerciceFiltre = $filters['exercice_id'] !== '' ? (int) $filters['exercice_id'] : null;

        $query = Objectif::query()
            ->with('exercices:id,annee,statut')
            ->withCount([
                'resultats' => fn ($q) => $q->forExercice($exerciceFiltre),
                'extrants' => fn ($q) => $q->forExercice($exerciceFiltre),
            ]);

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'], $filters['direction']);

        $objectifs = $query->paginate(15)->withQueryString();

        $summaryQuery = Objectif::query();
        $this->applyFilters($summaryQuery, $filters);

        $summary = [
            'total' => (clone $summaryQuery)->count('*'),
            'actifs' => (clone $summaryQuery)->where('statut', 'actif')->count('*'),
            'inactifs' => (clone $summaryQuery)->where('statut', 'inactif')->count('*'),
            'avec_resultats' => (clone $summaryQuery)->whereHas('resultats', fn ($q) => $q->forExercice($exerciceFiltre))->count('*'),
        ];

        // Les années sélectionnables sont celles des exercices : un objectif
        // pluriannuel est proposé sous chacune des années qu'il couvre.
        $annees = Exercice::query()->orderBy('annee', 'desc')->distinct()->pluck('annee');
        $exercices = Exercice::query()->ordered()->get(['id', 'annee', 'statut']);

        return view('pages.objectifs.index', compact('objectifs', 'annees', 'exercices', 'summary', 'filters'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $exercices = Exercice::query()->ordered()->get(['id', 'annee', 'statut']);
        // Pré-sélection sur l'exercice courant : le cas mono-exercice reste le plus fréquent.
        $selectedExerciceIds = array_filter([ActiveExercice::id()]);

        return view('pages.objectifs.create', compact('exercices', 'selectedExerciceIds'));
    }

    /**
     * Enregistrement
     */
    public function store(StoreObjectifRequest $request)
    {
        $validated = $request->validated();

        $exerciceIds = $validated['exercice_ids'];
        unset($validated['exercice_ids']);

        // `annee` reste l'année de départ de l'objectif (tri et libellé de période).
        $validated['annee'] = (int) Exercice::query()->whereIn('id', $exerciceIds)->min('annee');

        $objectif = Objectif::create($validated);
        $objectif->rattacherExercices($exerciceIds);

        return redirect()->route('objectifs.index')
            ->with('success', "Objectif {$objectif->code} créé avec succès.");
    }

    /**
     * Détail d'un objectif
     */
    public function show(Objectif $objectif)
    {
        $perimetre = $this->perimetreActivites(Auth::user());

        // Toute la hiérarchie de l'exercice actif : Résultats → Extrants → Activités.
        $exerciceId = ActiveExercice::id();
        $activitesDuPerimetre = fn ($a) => $a->when($perimetre !== null, fn ($q) => $q->whereIn('departement_id', $perimetre));
        $objectif->load([
            'resultats' => function ($query) use ($exerciceId, $activitesDuPerimetre) {
                $query->forExercice($exerciceId)->orderBy('ordre')->orderBy('code');
                $query->with([
                    'extrants' => function ($q) use ($activitesDuPerimetre) {
                        $q->orderBy('ordre')->orderBy('code');
                        $q->with(['activites' => fn ($a) => $activitesDuPerimetre($a)->orderBy('created_at', 'desc')]);
                        $q->withCount(['activites' => $activitesDuPerimetre]);
                    },
                ]);
                $query->withCount('extrants');
            },
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
        $exercices = Exercice::query()->ordered()->get(['id', 'annee', 'statut']);
        $selectedExerciceIds = $objectif->exercices()->pluck('exercices.id')->all();

        return view('pages.objectifs.edit', compact('objectif', 'exercices', 'selectedExerciceIds'));
    }

    /**
     * Mise à jour
     */
    public function update(UpdateObjectifRequest $request, Objectif $objectif)
    {
        $validated = $request->validated();

        $exerciceIds = $validated['exercice_ids'];
        unset($validated['exercice_ids']);

        $validated['annee'] = (int) Exercice::query()->whereIn('id', $exerciceIds)->min('annee');

        $objectif->update($validated);
        // Un exercice ajouté reçoit sa propre copie des résultats et extrants.
        $objectif->rattacherExercices($exerciceIds);

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
        Objectif::query()->whereKey($objectif->id)->delete();

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

    /**
     * @param  array{search: string, exercice_id: string, annee: string, statut: string, sort?: string, direction?: string}  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        // Un objectif est retenu dès qu'il couvre l'exercice / l'année demandé.
        if ($filters['exercice_id'] !== '') {
            $query->forExercice((int) $filters['exercice_id']);
        }

        if ($filters['annee'] !== '') {
            $query->byAnnee((int) $filters['annee']);
        }

        if ($filters['statut'] !== '') {
            $query->where('statut', $filters['statut']);
        }

        if ($filters['search'] !== '') {
            $term = '%'.str_replace(' ', '%', $filters['search']).'%';
            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('code', 'like', $term)
                    ->orWhere('libelle', 'like', $term);
            });
        }
    }

    private function applySort(Builder $query, string $sort, string $direction): void
    {
        $allowedSorts = ['code', 'annee', 'ordre', 'statut', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'ordre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $query->orderBy($sort, $direction)->orderBy('code', 'asc');
    }

    /**
     * Le cadre logique (objectifs, résultats, extrants) est visible de tous ; seules
     * les activités affichées sont ramenées au périmètre du responsable de programme
     * (son entité et les services qu'elle chapeaute).
     *
     * @return array<int, int>|null
     */
    private function perimetreActivites($user): ?array
    {
        return $user?->hasRole('responsable-programme') && $user?->departement_id !== null
            ? $user->perimetreActivitesIds()
            : null;
    }
}
