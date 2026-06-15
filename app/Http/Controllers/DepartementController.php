<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartementRequest;
use App\Http\Requests\UpdateDepartementRequest;
use App\Models\Departement;
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
            'sort' => (string) $request->string('sort', 'ordre'),
            'direction' => (string) $request->string('direction', 'asc'),
        ];

        $query = Departement::query()
            ->with(['responsable:id,name,email'])
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

        return view('pages.departements.index', compact('departements', 'filters', 'summary'));
    }

    public function create()
    {
        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('pages.departements.create', compact('users'));
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
            ])
            ->loadCount(['users', 'activites']);

        $recentActivites = $departement->activites()
            ->select('id', 'nom_activite', 'statut', 'cout', 'created_at')
            ->latest()
            ->limit(6)
            ->get();

        return view('pages.departements.show', compact('departement', 'recentActivites'));
    }

    public function edit(Departement $departement)
    {
        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('pages.departements.edit', compact('departement', 'users'));
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
     * @param array{search: string, status: string, sort?: string, direction?: string} $filters
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
