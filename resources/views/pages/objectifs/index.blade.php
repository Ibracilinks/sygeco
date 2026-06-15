<x-layouts::app title="Objectifs Stratégiques">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Objectifs stratégiques</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Pilotage des objectifs par exercice, statut et structuration des résultats attendus.</p>
            </div>
            @can('create_objectifs')
                <a href="{{ route('objectifs.create') }}" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Nouvel objectif
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['total'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/25">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Actifs</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($summary['actifs'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/60 dark:bg-rose-950/25">
                <p class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">Inactifs</p>
                <p class="mt-2 text-3xl font-semibold text-rose-800 dark:text-rose-100">{{ number_format($summary['inactifs'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/60 dark:bg-sky-950/25">
                <p class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-300">Avec résultats</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-100">{{ number_format($summary['avec_resultats'] ?? 0) }}</p>
            </div>
        </div>

        <!-- Messages flash -->
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

        <form method="GET" action="{{ route('objectifs.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-6">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Code ou libellé"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">

            <select name="exercice_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous exercices</option>
                @foreach ($exercices as $ex)
                    <option value="{{ $ex->id }}" @selected((string) ($filters['exercice_id'] ?? '') === (string) $ex->id)>{{ $ex->annee }}</option>
                @endforeach
            </select>

            <select name="annee" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Toutes années</option>
                @foreach ($annees as $annee)
                    <option value="{{ $annee }}" @selected((string) ($filters['annee'] ?? '') === (string) $annee)>{{ $annee }}</option>
                @endforeach
            </select>

            <select name="statut" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous statuts</option>
                <option value="actif" @selected(($filters['statut'] ?? '') === 'actif')>Actif</option>
                <option value="inactif" @selected(($filters['statut'] ?? '') === 'inactif')>Inactif</option>
            </select>

            <select name="sort" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="ordre" @selected(($filters['sort'] ?? '') === 'ordre')>Trier par ordre</option>
                <option value="code" @selected(($filters['sort'] ?? '') === 'code')>Trier par code</option>
                <option value="annee" @selected(($filters['sort'] ?? '') === 'annee')>Trier par année</option>
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

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Objectif</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Exercice</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Structure</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($objectifs as $objectif)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $objectif->code }}</p>
                                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ Str::limit($objectif->libelle, 90) }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                <p>{{ $objectif->annee }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Exercice: {{ $objectif->exercice?->annee ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                <p>Ordre: {{ $objectif->ordre ?? '-' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $objectif->resultats_count }} résultats • {{ $objectif->extrants_count }} extrants</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $objectif->statut === 'actif' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200' }}">
                                    {{ $objectif->statut === 'actif' ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-3 text-sm">
                                    <a href="{{ route('objectifs.show', $objectif) }}" class="font-medium text-sky-700 hover:text-sky-600 dark:text-sky-300">Voir</a>
                                    @can('edit_objectifs')
                                        <a href="{{ route('objectifs.edit', $objectif) }}" class="font-medium text-amber-700 hover:text-amber-600 dark:text-amber-300">Modifier</a>
                                    @endcan
                                    @can('delete_objectifs')
                                        <form action="{{ route('objectifs.destroy', $objectif) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer la suppression de cet objectif ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-rose-700 hover:text-rose-600 dark:text-rose-300">Supprimer</button>
                                        </form>
                                    @endcan
                                    @can('edit_objectifs')
                                        <form action="{{ route('objectifs.toggle-statut', $objectif) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="font-medium {{ $objectif->statut === 'actif' ? 'text-rose-700 hover:text-rose-600 dark:text-rose-300' : 'text-emerald-700 hover:text-emerald-600 dark:text-emerald-300' }}">
                                                {{ $objectif->statut === 'actif' ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                Aucun objectif trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $objectifs->links() }}
        </div>
    </div>
</x-layouts::app>
