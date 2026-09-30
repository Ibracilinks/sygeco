<x-layouts::app title="Résultats">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Résultats stratégiques</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Suivi des résultats par objectif, statut et niveau de déclinaison en extrants.</p>
            </div>
            @can('create_resultats')
                <a href="{{ route('resultats.create') }}" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Nouveau résultat
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
                <p class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-300">Avec extrants</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-100">{{ number_format($summary['avec_extrants'] ?? 0) }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/40">
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 dark:border-rose-800 dark:bg-rose-950/40">
                <p class="text-sm font-medium text-rose-800 dark:text-rose-200">{{ session('error') }}</p>
            </div>
        @endif

        <form method="GET" action="{{ route('resultats.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-5">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Code ou libellé"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">

            <select name="objectif_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous objectifs</option>
                @foreach ($objectifs as $objectif)
                    <option value="{{ $objectif->id }}" @selected((string) ($filters['objectif_id'] ?? '') === (string) $objectif->id)>
                        {{ $objectif->code }} - {{ $objectif->periode_libelle }}
                    </option>
                @endforeach
            </select>

            <select name="is_active" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous statuts</option>
                <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Actifs</option>
                <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactifs</option>
            </select>

            <select name="sort" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="ordre" @selected(($filters['sort'] ?? '') === 'ordre')>Trier par ordre</option>
                <option value="code" @selected(($filters['sort'] ?? '') === 'code')>Trier par code</option>
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
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Résultat</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Objectif</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Extrants</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($resultats as $resultat)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $resultat->code }}</p>
                                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ Str::limit($resultat->libelle, 90) }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Ordre: {{ $resultat->ordre ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                <p class="font-medium">{{ $resultat->objectif?->code ?? '-' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $resultat->objectif?->periode_libelle ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $resultat->extrants_count }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $resultat->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200' }}">
                                    {{ $resultat->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-1">
                                    <x-actions.view :href="route('resultats.show', $resultat)" />
                                    @can('edit_resultats')
                                        <x-actions.edit :href="route('resultats.edit', $resultat)" />
                                    @endcan
                                    @can('delete_resultats')
                                        <x-actions.delete :action="route('resultats.destroy', $resultat)" confirm="Confirmer la suppression ?" />
                                    @endcan
                                    @can('edit_resultats')
                                        @if ($resultat->is_active)
                                            <x-actions.deactivate :action="route('resultats.toggle-status', $resultat)" />
                                        @else
                                            <x-actions.activate :action="route('resultats.toggle-status', $resultat)" />
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Aucun résultat trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $resultats->links() }}</div>
    </div>
</x-layouts::app>
