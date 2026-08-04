<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartementRequest;
use App\Http\Requests\UpdateDepartementRequest;
use App\Models\Activite;
use App\Models\ActiviteEvaluation;
use App\Models\Departement;
use App\Support\ActiveExercice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DepartementController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'status' => (string) $request->string('status'),
            'type' => (string) $request->string('type'),
            'sort' => (string) $request->string('sort', 'ordre'),
            'direction' => (string) $request->string('direction', 'asc'),
        ];

        $query = Departement::query()
            ->with(['responsable:id,name,email', 'parent:id,nom,type'])
            ->withCount(['users', 'activites']);

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'], $filters['direction']);

        $departements = $query->paginate(15)->withQueryString();

        $summaryQuery = Departement::query();
        $this->applyFilters($summaryQuery, $filters);

        $summary = [
            'total' => (clone $summaryQuery)->count('*'),
            'actifs' => (clone $summaryQuery)->where('is_active', true)->count('*'),
            'inactifs' => (clone $summaryQuery)->where('is_active', false)->count('*'),
            'avec_responsable' => (clone $summaryQuery)->whereNotNull('responsable_id', 'and')->count('*'),
        ];

        // Organigramme complet (racines = directions), chargé récursivement.
        $arbre = Departement::query()
            ->whereNull('parent_id')
            ->with('enfantsRecursifs')
            ->ordered()
            ->get();

        return view('pages.departements.index', compact('departements', 'filters', 'summary', 'arbre'));
    }

    public function create()
    {
        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $parents = $this->parentsCandidats();

        return view('pages.departements.create', compact('users', 'parents'));
    }

    public function store(StoreDepartementRequest $request)
    {
        $validated = $request->validated();

        $validated['ordre'] = $validated['ordre'] ?? ((int) Departement::max('ordre') + 1);

        Departement::create($validated);

        return redirect()->route('departements.index')
            ->with('success', 'Département créé avec succès.');
    }

    public function show(Departement $departement)
    {
        $departement
            ->load([
                'responsable:id,name,email,telephone',
                'users:id,name,email,departement_id',
                'parent.parent',
                'enfants:id,nom,code,type,parent_id,is_active',
            ])
            ->loadCount(['users', 'activites', 'enfants']);

        $recentActivites = $departement->activites()
            ->select('id', 'nom_activite', 'statut', 'cout', 'created_at')
            ->latest()
            ->limit(6)
            ->get();

        $analytique = $this->analytique($departement);

        return view('pages.departements.show', compact('departement', 'recentActivites', 'analytique'));
    }

    /**
     * Analytique d'une entité pour l'exercice actif, calculée sur son sous-arbre :
     * une Direction Centrale agrège donc les activités de ses services. Remonte la
     * chaîne du cadre logique (résultats et extrants effectivement portés), l'état
     * budgétaire, l'avancement du circuit de validation et l'exécution évaluée.
     *
     * @return array<string, mixed>
     */
    private function analytique(Departement $departement): array
    {
        $exercice = ActiveExercice::model();
        $perimetre = $departement->sousArbreIds();

        $activites = Activite::query()
            ->whereIn('departement_id', $perimetre)
            ->forExercice($exercice?->id)
            ->with(['extrant.resultat', 'departement:id,nom', 'evaluations'])
            ->get();

        $extrants = $activites->pluck('extrant')->filter()->unique('id');
        $resultats = $extrants->pluck('resultat')->filter()->unique('id');

        $budgetPlanifie = (float) $activites->sum('cout');
        $budgetConsomme = (float) $activites->sum(
            fn (Activite $activite) => (float) ($activite->evaluation(ActiviteEvaluation::PERIODE_FIN_ANNEE)?->montant_utilise
                ?? $activite->evaluation(ActiviteEvaluation::PERIODE_MI_PARCOURS)?->montant_utilise
                ?? 0)
        );

        // Exécution telle qu'évaluée en fin d'année : les activités validées non encore
        // évaluées sont comptées à part, jamais assimilées à des activités non réalisées.
        $validees = $activites->where('statut', 'valide');
        $execution = ['realise' => 0, 'en_cours' => 0, 'non_realise' => 0, 'non_evaluee' => 0];

        foreach ($validees as $activite) {
            $statut = $activite->evaluation(ActiviteEvaluation::PERIODE_FIN_ANNEE)?->statut_execution;
            $execution[$statut && isset($execution[$statut]) ? $statut : 'non_evaluee']++;
        }

        // Ventilation par entité du sous-arbre, pour situer la contribution de chaque service.
        $parEntite = $activites
            ->groupBy('departement_id')
            ->map(fn ($groupe) => [
                'nom' => $groupe->first()->departement->nom ?? '—',
                'nb_activites' => $groupe->count(),
                'budget' => (float) $groupe->sum('cout'),
                'validees' => $groupe->where('statut', 'valide')->count(),
            ])
            ->sortByDesc('budget')
            ->values();

        return [
            'exercice' => $exercice,
            'nb_entites' => count($perimetre),
            'nb_resultats' => $resultats->count(),
            'nb_extrants' => $extrants->count(),
            'nb_activites' => $activites->count(),
            'statuts' => [
                'brouillon' => $activites->where('statut', 'brouillon')->count(),
                'en_attente' => $activites->where('statut', 'en_attente')->count(),
                'valide' => $validees->count(),
                'rejete' => $activites->where('statut', 'rejete')->count(),
            ],
            'budget_planifie' => $budgetPlanifie,
            'budget_consomme' => $budgetConsomme,
            'taux_consommation' => $budgetPlanifie > 0 ? round($budgetConsomme / $budgetPlanifie * 100, 1) : 0.0,
            'taux_validation' => $activites->count() > 0 ? round($validees->count() / $activites->count() * 100, 1) : 0.0,
            'execution' => $execution,
            'par_entite' => $parEntite,
        ];
    }

    public function edit(Departement $departement)
    {
        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Une entité ne peut pas se rattacher à elle-même.
        $parents = $this->parentsCandidats()
            ->reject(fn (Departement $candidat) => $candidat->id === $departement->id)
            ->values();

        return view('pages.departements.edit', compact('departement', 'users', 'parents'));
    }

    public function update(UpdateDepartementRequest $request, Departement $departement)
    {
        $validated = $request->validated();

        $departement->update($validated);

        return redirect()->route('departements.index')
            ->with('success', 'Département mis à jour.');
    }

    public function destroy(Departement $departement)
    {
        if ($departement->users()->count() > 0) {
            return redirect()->route('departements.index')
                ->with('error', 'Impossible de supprimer un département qui a des utilisateurs.');
        }

        Departement::query()->whereKey($departement->id)->delete();

        return redirect()->route('departements.index')
            ->with('success', 'Département supprimé.');
    }

    /**
     * Entités pouvant servir de parent : directions et départements
     * (un service est toujours une feuille, jamais un parent).
     *
     * @return \Illuminate\Support\Collection<int, Departement>
     */
    private function parentsCandidats()
    {
        return Departement::query()
            ->whereIn('type', [Departement::TYPE_DIRECTION, Departement::TYPE_DEPARTEMENT])
            ->ordered()
            ->get(['id', 'nom', 'type']);
    }

    /**
     * @param array{search: string, status: string, type?: string, sort?: string, direction?: string} $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $term = '%' . str_replace(' ', '%', $filters['search']) . '%';

            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('code', 'like', $term)
                    ->orWhere('nom', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($filters['status'] === 'active') {
            $query->where('is_active', true);
        }

        if ($filters['status'] === 'inactive') {
            $query->where('is_active', false);
        }

        if (in_array($filters['type'] ?? '', Departement::TYPES, true)) {
            $query->where('type', $filters['type']);
        }
    }

    private function applySort(Builder $query, string $sort, string $direction): void
    {
        $allowedSorts = ['ordre', 'nom', 'code', 'users_count', 'activites_count', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'ordre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        if ($sort === 'users_count' || $sort === 'activites_count') {
            $query->orderBy($sort, $direction)->orderBy('nom');

            return;
        }

        if ($sort === 'ordre') {
            $query->orderBy('ordre', $direction)->orderBy('nom');

            return;
        }

        $query->orderBy($sort, $direction);
    }
}
