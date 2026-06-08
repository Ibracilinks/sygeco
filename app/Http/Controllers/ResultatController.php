<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResultatRequest;
use App\Http\Requests\UpdateResultatRequest;
use App\Models\Resultat;
use App\Models\Objectif;
use App\Support\ActiveExercice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ResultatController extends Controller
{
    /**
     * Liste des résultats
     */
    public function index(Request $request)
    {
        $exerciceId = ActiveExercice::id();

        $filters = [
            'search' => trim((string) $request->string('search')),
            'objectif_id' => trim((string) $request->string('objectif_id')),
            'is_active' => trim((string) $request->string('is_active')),
            'sort' => (string) $request->string('sort', 'ordre'),
            'direction' => (string) $request->string('direction', 'asc'),
        ];

        $query = Resultat::query()
            ->with(['objectif:id,code,annee,libelle'])
            ->withCount('extrants');

        if ($exerciceId !== null) {
            $query->whereHas('objectif', fn (Builder $builder) => $builder->where('exercice_id', $exerciceId));
        }

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'], $filters['direction']);

        $resultats = $query->paginate(15)->withQueryString();

        $summaryQuery = Resultat::query();
        if ($exerciceId !== null) {
            $summaryQuery->whereHas('objectif', fn (Builder $builder) => $builder->where('exercice_id', $exerciceId));
        }
        $this->applyFilters($summaryQuery, $filters);

        $summary = [
            'total' => (clone $summaryQuery)->count('*'),
            'actifs' => (clone $summaryQuery)->where('is_active', true)->count('*'),
            'inactifs' => (clone $summaryQuery)->where('is_active', false)->count('*'),
            'avec_extrants' => (clone $summaryQuery)->has('extrants')->count('*'),
        ];

        $objectifs = Objectif::query()
            ->where('statut', 'actif')
            ->when($exerciceId !== null, fn ($q) => $q->where('exercice_id', $exerciceId))
            ->orderBy('annee', 'desc')
            ->orderBy('code')
            ->get(['id', 'code', 'annee', 'libelle']);

        return view('pages.resultats.index', compact('resultats', 'objectifs', 'summary', 'filters'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $exerciceId = ActiveExercice::id();
        $objectifs = Objectif::query()
            ->where('statut', 'actif')
            ->when($exerciceId !== null, fn ($q) => $q->where('exercice_id', $exerciceId))
            ->orderBy('annee', 'desc')
            ->get();
        $selectedObjectif = $request->get('objectif_id');

        return view('pages.resultats.create', compact('objectifs', 'selectedObjectif'));
    }

    /**
     * Enregistrement
     */
    public function store(StoreResultatRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

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
        $exerciceId = ActiveExercice::id();
        $objectifs = Objectif::query()
            ->where('statut', 'actif')
            ->when($exerciceId !== null, fn ($q) => $q->where('exercice_id', $exerciceId))
            ->orderBy('annee', 'desc')
            ->get();

        return view('pages.resultats.edit', compact('resultat', 'objectifs'));
    }

    /**
     * Mise à jour
     */
    public function update(UpdateResultatRequest $request, Resultat $resultat)
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

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
        Resultat::query()->whereKey($resultat->id)->delete();

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

    /**
     * @param array{search: string, objectif_id: string, is_active: string, sort?: string, direction?: string} $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['objectif_id'] !== '') {
            $query->where('objectif_id', (int) $filters['objectif_id']);
        }

        if ($filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active'] === '1');
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
        $allowedSorts = ['code', 'ordre', 'created_at', 'is_active'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'ordre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $query->orderBy($sort, $direction)->orderBy('code', 'asc');
    }
}
