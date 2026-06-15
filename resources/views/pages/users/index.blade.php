<x-layouts::app title="Utilisateurs">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Utilisateurs</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Gestion des comptes, rattachements départementaux et rôles applicatifs.</p>
            </div>
            @can('create_users')
                <a href="{{ route('users.create') }}"
                    class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Nouvel utilisateur
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['total'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/70 dark:bg-emerald-950/30">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Comptes vérifiés</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($summary['verifies'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/70 dark:bg-amber-950/30">
                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Sans département</p>
                <p class="mt-2 text-3xl font-semibold text-amber-800 dark:text-amber-100">{{ number_format($summary['sans_departement'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/70 dark:bg-sky-950/30">
                <p class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-300">Avec rôles</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-100">{{ number_format($summary['avec_roles'] ?? 0) }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-5">
            <input
                type="text"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="Rechercher nom, email, poste, téléphone"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            >
            <select name="departement_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous départements</option>
                @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}" @selected((string) ($filters['departement_id'] ?? '') === (string) $departement->id)>
                        {{ $departement->nom }}
                    </option>
                @endforeach
            </select>
            <select name="role" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous rôles</option>
                @foreach ($availableRoles as $roleName)
                    <option value="{{ $roleName }}" @selected(($filters['role'] ?? '') === $roleName)>{{ $roleName }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="name" @selected(($filters['sort'] ?? 'name') === 'name')>Trier par nom</option>
                <option value="email" @selected(($filters['sort'] ?? '') === 'email')>Trier par email</option>
                <option value="roles_count" @selected(($filters['sort'] ?? '') === 'roles_count')>Trier par nombre de rôles</option>
                <option value="created_at" @selected(($filters['sort'] ?? '') === 'created_at')>Trier par création</option>
            </select>
            <div class="flex gap-2">
                <select name="direction" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    <option value="asc" @selected(($filters['direction'] ?? 'asc') === 'asc')>Croissant</option>
                    <option value="desc" @selected(($filters['direction'] ?? '') === 'desc')>Décroissant</option>
                </select>
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Filtrer</button>
            </div>
        </form>

        @if (session('success'))
            <div class="rounded-lg bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 p-4">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 p-4">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Identité</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Organisation</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Contact</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Rôles</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($users as $user)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                <div class="text-sm text-slate-600 dark:text-slate-300">{{ $user->email }}</div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $user->email_verified_at ? 'Email vérifié' : 'Email non vérifié' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                <div>{{ $user->departement?->nom ?? 'Aucun département' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->poste ?: 'Poste non défini' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                <div>{{ $user->telephone ?: '-' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Créé le {{ optional($user->created_at)->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if ($user->roles->isEmpty())
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Aucun rôle</span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($user->roles as $role)
                                            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800 dark:bg-sky-900/40 dark:text-sky-200">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-1">
                                    <x-actions.view :href="route('users.show', $user)" />
                                    @can('edit_users')
                                        <x-actions.edit :href="route('users.edit', $user)" />
                                    @endcan
                                    @can('delete_users')
                                        <x-actions.delete :action="route('users.destroy', $user)" confirm="Confirmer la suppression ?" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                Aucun utilisateur trouvé avec les filtres actuels.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $users->links() }}
        </div>
    </div>
</x-layouts::app>
