<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Departement;
use App\Models\User;
use App\Notifications\BienvenueUtilisateurNotification;
use App\Notifications\MotDePasseReinitialiseNotification;
use App\Support\MotDePasseProvisoire;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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

        $roles = $this->rolesAttribuables();

        return view('pages.users.create', compact('departements', 'roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            // Remplacé aussitôt par le mot de passe provisoire envoyé par e-mail.
            'password' => Str::password(32),
            'departement_id' => $validated['departement_id'] ?? null,
            'poste' => $validated['poste'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
        ]);

        $roleNames = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
        $user->syncRoles($roleNames);

        // Mail de bienvenue avec un mot de passe provisoire, à changer à la première connexion.
        $user->notify(new BienvenueUtilisateurNotification(MotDePasseProvisoire::attribuer($user)));

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
        $this->verifierGestion($user);

        $departements = DB::table('departements')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $roles = $this->rolesAttribuables();

        return view('pages.users.edit', compact('user', 'departements', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->verifierGestion($user);
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'departement_id' => $validated['departement_id'] ?? null,
            'poste' => $validated['poste'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
        ]);

        // Les rôles que l'utilisateur connecté ne peut pas attribuer ne lui sont pas
        // proposés : ceux que le compte porte déjà sont conservés tels quels.
        $roleNames = Role::whereIn('id', $validated['roles'])->pluck('name')
            ->merge($user->getRoleNames()->reject(fn ($role) => Auth::user()->peutAttribuerRole($role)))
            ->unique()->values()->all();
        $user->syncRoles($roleNames);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    /**
     * Attribue un nouveau mot de passe provisoire et l'envoie par e-mail ; l'utilisateur
     * devra le changer à sa prochaine connexion.
     */
    public function reinitialiserMotDePasse(User $user)
    {
        $this->verifierGestion($user);

        $user->notify(new MotDePasseReinitialiseNotification(MotDePasseProvisoire::attribuer($user)));

        return redirect()->route('users.show', $user)
            ->with('success', "Mot de passe réinitialisé : un mot de passe provisoire a été envoyé à {$user->email}.");
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
     * Rôles que l'utilisateur connecté peut attribuer (le contrôle de gestion
     * n'attribue ni superadmin, ni dbcgoq, ni service-controle-gestion, ni
     * service-budget).
     */
    private function rolesAttribuables()
    {
        return DB::table('roles')->orderBy('name')->get(['id', 'name'])
            ->filter(fn ($role) => Auth::user()->peutAttribuerRole($role->name))
            ->values();
    }

    /** Un compte superadmin, admin ou dbcgoq n'est géré que par un superadmin ou la DBCGOQ. */
    private function verifierGestion(User $user): void
    {
        abort_unless(Auth::user()->peutGererUtilisateur($user), 403);
    }

    /**
     * @param  array{search: string, departement_id: string, role: string, sort?: string, direction?: string}  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $term = '%'.str_replace(' ', '%', $filters['search']).'%';

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
