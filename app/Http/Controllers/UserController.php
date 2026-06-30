<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Departement;
use App\Notifications\BienvenueUtilisateurNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'departement_id' => (string) $request->string('departement_id'),
            'role' => trim((string) $request->string('role')),
            'sort' => (string) $request->string('sort', 'name'),
            'direction' => (string) $request->string('direction', 'asc'),
        ];

        $query = User::query()
            ->with(['departement:id,nom', 'roles:id,name'])
            ->withCount('roles');

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'], $filters['direction']);

        $users = $query->paginate(15)->withQueryString();

        $summaryQuery = User::query();
        $this->applyFilters($summaryQuery, $filters);

        $summary = [
            'total' => (clone $summaryQuery)->count('*'),
            'verifies' => (clone $summaryQuery)->whereNotNull('email_verified_at', 'and')->count('*'),
            'sans_departement' => (clone $summaryQuery)->where('departement_id', null)->count('*'),
            'avec_roles' => (clone $summaryQuery)->whereHas('roles')->count('*'),
        ];

        $departements = Departement::query()
            ->active()
            ->ordered()
            ->get(['id', 'nom']);

        $availableRoles = Role::query()->orderBy('name')->pluck('name');

        return view('pages.users.index', compact('users', 'summary', 'filters', 'departements', 'availableRoles'));
    }

    public function create()
    {
        $departements = DB::table('departements')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $roles = DB::table('roles')->orderBy('name')->get(['id', 'name']);

        return view('pages.users.create', compact('departements', 'roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'departement_id' => $validated['departement_id'] ?? null,
            'poste' => $validated['poste'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
        ]);

        $roleNames = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
        $user->syncRoles($roleNames);

        // Mail de bienvenue avec les identifiants (mot de passe défini par l'administrateur).
        $user->notify(new BienvenueUtilisateurNotification($validated['password']));

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé et notifié par e-mail.');
    }

    public function show(User $user)
    {
        $user->load(['departement:id,nom', 'roles:id,name'])->loadCount('roles');

        $departementPeers = collect();
        if ($user->departement_id !== null) {
            $departementPeers = User::query()
                ->where('departement_id', $user->departement_id)
                ->where('id', '!=', $user->id)
                ->orderBy('name', 'asc')
                ->limit(8)
                ->get(['id', 'name', 'email', 'poste']);
        }

        return view('pages.users.show', compact('user', 'departementPeers'));
    }

    public function edit(User $user)
    {
        $departements = DB::table('departements')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $roles = DB::table('roles')->orderBy('name')->get(['id', 'name']);

        return view('pages.users.edit', compact('user', 'departements', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'departement_id' => $validated['departement_id'] ?? null,
            'poste' => $validated['poste'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
        ]);

        if (filled($validated['password'] ?? null)) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $roleNames = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
        $user->syncRoles($roleNames);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        User::query()->whereKey($user->id)->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé.');
    }

    /**
     * @param array{search: string, departement_id: string, role: string, sort?: string, direction?: string} $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $term = '%' . str_replace(' ', '%', $filters['search']) . '%';

            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('poste', 'like', $term)
                    ->orWhere('telephone', 'like', $term);
            });
        }

        if ($filters['departement_id'] !== '') {
            $query->where('departement_id', (int) $filters['departement_id']);
        }

        if ($filters['role'] !== '') {
            $query->whereHas('roles', function (Builder $roleQuery) use ($filters) {
                $roleQuery->where('name', $filters['role']);
            });
        }
    }

    private function applySort(Builder $query, string $sort, string $direction): void
    {
        $allowedSorts = ['name', 'email', 'created_at', 'email_verified_at', 'roles_count'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'name';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        if ($sort === 'roles_count') {
            $query->orderBy('roles_count', $direction)->orderBy('name');

            return;
        }

        $query->orderBy($sort, $direction);
    }
}
