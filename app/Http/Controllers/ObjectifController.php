<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreObjectifRequest;
use App\Http\Requests\UpdateObjectifRequest;
use App\Models\Exercice;
use App\Models\Objectif;
use App\Support\ActiveExercice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObjectifController extends Controller
{
    /**
     * Liste des objectifs
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'search' => trim((string) $request->string('search')),
            'exercice_id' => $request->has('exercice_id') ? (string) $request->string('exercice_id') : (string) ActiveExercice::id(),
            'annee' => trim((string) $request->string('annee')),
            'statut' => trim((string) $request->string('statut')),
            'sort' => (string) $request->string('sort', 'ordre'),
            'direction' => (string) $request->string('direction', 'asc'),
        ];

        $query = Objectif::query()
            ->with('exercice:id,annee,statut')
            ->withCount(['resultats', 'extrants']);

        if ($this->isChefDepartement($user)) {
            $this->applyDepartmentScopeToObjectifQuery($query, (int) $user->departement_id);
        }

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'], $filters['direction']);

        $objectifs = $query->paginate(15)->withQueryString();

        $summaryQuery = Objectif::query();
        if ($this->isChefDepartement($user)) {
            $this->applyDepartmentScopeToObjectifQuery($summaryQuery, (int) $user->departement_id);
        }
        $this->applyFilters($summaryQuery, $filters);

        $summary = [
            'total' => (clone $summaryQuery)->count('*'),
            'actifs' => (clone $summaryQuery)->where('statut', 'actif')->count('*'),
            'inactifs' => (clone $summaryQuery)->where('statut', 'inactif')->count('*'),
            'avec_resultats' => (clone $summaryQuery)->has('resultats')->count('*'),
        ];

        $annees = Objectif::query()
            ->when($this->isChefDepartement($user), fn ($q) => $this->applyDepartmentScopeToObjectifQuery($q, (int) $user->departement_id))
            ->select('annee')->distinct()->orderBy('annee', 'desc')->pluck('annee');
        $exercices = Exercice::query()->ordered()->get(['id', 'annee', 'statut']);

        return view('pages.objectifs.index', compact('objectifs', 'annees', 'exercices', 'summary', 'filters'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $exercices = Exercice::query()->ordered()->get(['id', 'annee', 'statut']);
        $defaultExerciceId = ActiveExercice::id();

        return view('pages.objectifs.create', compact('exercices', 'defaultExerciceId'));
    }

    /**
     * Enregistrement
     */
    public function store(StoreObjectifRequest $request)
    {
        $validated = $request->validated();

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
        $user = Auth::user();

        if ($this->isChefDepartement($user) && ! $this->objectifHasDepartmentActivities($objectif, (int) $user->departement_id)) {
            abort(403);
        }

        // Charger toute la hiérarchie : Résultats → Extrants → Activités
        $objectif->load([
            'resultats' => function ($query) use ($user) {
                $this->applyDepartmentScopeToResultatQuery($query, $user?->departement_id);
                $query->orderBy('ordre')->orderBy('code');
                $query->with([
                    'extrants' => function ($q) use ($user) {
                        $this->applyDepartmentScopeToExtrantQuery($q, $user?->departement_id);
                        $q->orderBy('ordre')->orderBy('code');
                        $q->with([
                            'activites' => function ($a) use ($user) {
                                if ($this->isChefDepartement($user) && $user?->departement_id) {
                                    $a->where('departement_id', $user->departement_id);
                                }
                                $a->orderBy('created_at', 'desc');
                            }
                        ]);
                        $q->withCount(['activites' => function ($a) use ($user) {
                            if ($this->isChefDepartement($user) && $user?->departement_id) {
                                $a->where('departement_id', $user->departement_id);
                            }
                        }]);
                    }
                ]);
                $query->withCount(['extrants' => function ($q) use ($user) {
                    $this->applyDepartmentScopeToExtrantQuery($q, $user?->departement_id);
                }]);
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
        $exercices = Exercice::query()->ordered()->get(['id', 'annee', 'statut']);

        return view('pages.objectifs.edit', compact('objectif', 'exercices'));
    }

    /**
     * Mise à jour
     */
    public function update(UpdateObjectifRequest $request, Objectif $objectif)
    {
        $validated = $request->validated();

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
     * @param array{search: string, exercice_id: string, annee: string, statut: string, sort?: string, direction?: string} $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['exercice_id'] !== '') {
            $query->where('exercice_id', (int) $filters['exercice_id']);
        }

        if ($filters['annee'] !== '') {
            $query->where('annee', (int) $filters['annee']);
        }

        if ($filters['statut'] !== '') {
            $query->where('statut', $filters['statut']);
        }

        if ($filters['search'] !== '') {
            $term = '%' . str_replace(' ', '%', $filters['search']) . '%';
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

    private function isChefDepartement($user): bool
    {
        return $user?->hasRole('chef') && $user?->departement_id !== null;
    }

    private function objectifHasDepartmentActivities(Objectif $objectif, int $departementId): bool
    {
        return $objectif->resultats()
            ->whereHas('extrants.activites', fn (Builder $query) => $query->where('departement_id', $departementId))
            ->exists();
    }

    private function applyDepartmentScopeToObjectifQuery(Builder $query, int $departementId): void
    {
        $query->whereHas('resultats.extrants.activites', fn (Builder $builder) => $builder->where('departement_id', $departementId));
    }

    private function applyDepartmentScopeToResultatQuery(Builder|Relation $query, ?int $departementId): void
    {
        if ($departementId === null) {
            return;
        }

        $query->whereHas('extrants.activites', fn (Builder $builder) => $builder->where('departement_id', $departementId));
    }

    private function applyDepartmentScopeToExtrantQuery(Builder|Relation $query, ?int $departementId): void
    {
        if ($departementId === null) {
            return;
        }

        $query->whereHas('activites', fn (Builder $builder) => $builder->where('departement_id', $departementId));
    }
}
