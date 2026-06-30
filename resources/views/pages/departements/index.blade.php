<x-layouts::app title="Départements">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Départements</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Gouvernance, responsables et charge opérationnelle par département.</p>
            </div>
            @can('create_departements')
                <a href="{{ route('departements.create') }}"
                    class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Nouveau Département
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['total'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/70 dark:bg-emerald-950/30">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Actifs</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($summary['actifs'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/70 dark:bg-amber-950/30">
                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Inactifs</p>
                <p class="mt-2 text-3xl font-semibold text-amber-800 dark:text-amber-100">{{ number_format($summary['inactifs'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/70 dark:bg-sky-950/30">
                <p class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-300">Avec responsable</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-100">{{ number_format($summary['avec_responsable'] ?? 0) }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('departements.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-5">
            <input
                type="text"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="Rechercher par code, nom, description"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            >
            <select name="type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous les niveaux</option>
                @foreach (\App\Models\Departement::TYPE_LABELS as $valeur => $libelle)
                    <option value="{{ $valeur }}" @selected(($filters['type'] ?? '') === $valeur)>{{ $libelle }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous les statuts</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Actifs</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactifs</option>
            </select>
            <select name="sort" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="ordre" @selected(($filters['sort'] ?? '') === 'ordre')>Trier par ordre</option>
                <option value="nom" @selected(($filters['sort'] ?? '') === 'nom')>Trier par nom</option>
                <option value="users_count" @selected(($filters['sort'] ?? '') === 'users_count')>Trier par utilisateurs</option>
                <option value="activites_count" @selected(($filters['sort'] ?? '') === 'activites_count')>Trier par activités</option>
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

        @if ($arbre->isNotEmpty())
            <details class="group rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900" open>
                <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-3">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">Organigramme</span>
                    <span class="text-xs text-slate-500 transition group-open:rotate-180 dark:text-slate-400">▾</span>
                </summary>
                <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-700">
                    <ul class="space-y-0.5">
                        @foreach ($arbre as $racine)
                            @include('pages.departements.partials.arbre-noeud', ['noeud' => $racine, 'niveau' => 0])
                        @endforeach
                    </ul>
                </div>
            </details>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Entité</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Type / Rattachement</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Responsable</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Données</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($departements as $departement)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $departement->nom }}</div>
                                <div class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $departement->code }}</div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Ordre: {{ $departement->ordre ?? '-' }}
                                </div>
                                @if (filled($departement->description))
                                    <p class="mt-2 max-w-md text-xs text-slate-600 dark:text-slate-300">{{ Str::limit($departement->description, 90) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                @include('pages.departements.partials.type-badge', ['type' => $departement->type])
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    @if ($departement->parent)
                                        Rattaché à
                                        <a href="{{ route('departements.show', $departement->parent) }}" class="font-medium text-slate-700 hover:underline dark:text-slate-200">{{ $departement->parent->nom }}</a>
                                    @else
                                        Entité racine
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                @if ($departement->responsable)
                                    <div class="font-medium">{{ $departement->responsable->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $departement->responsable->email }}</div>
                                @else
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Aucun responsable</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                <div>{{ $departement->users_count }} utilisateur(s)</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $departement->activites_count }} activité(s)</div>
                            </td>
                            <td class="px-5 py-4">
                                @if ($departement->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Actif</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Inactif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-1">
                                    <x-actions.view :href="route('departements.show', $departement)" />
                                    @can('edit_departements')
                                        <x-actions.edit :href="route('departements.edit', $departement)" />
                                    @endcan
                                    @can('delete_departements')
                                        <x-actions.delete :action="route('departements.destroy', $departement)" confirm="Confirmer la suppression ?" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                Aucun département trouvé avec les filtres actuels.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $departements->links() }}
        </div>
    </div>
</x-layouts::app>
