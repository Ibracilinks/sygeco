<x-layouts::app title="Missions">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Missions</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Ordres de mission même ville et extérieurs, avec participants, signataires et génération PDF.</p>
            </div>
            @can('create_missions')
                <a href="{{ route('missions.create') }}"
                    class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Nouvelle mission
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['total']) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/70 dark:bg-amber-950/30">
                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Brouillons</p>
                <p class="mt-2 text-3xl font-semibold text-amber-800 dark:text-amber-100">{{ number_format($summary['brouillons']) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/70 dark:bg-emerald-950/30">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Finalisées</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($summary['finalisees']) }}</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/70 dark:bg-sky-950/30">
                <p class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-300">Budget cumulé</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-100">{{ number_format($summary['budget_total'], 0, ',', ' ') }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('missions.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-5">
            <input
                type="text"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="Référence, objet, lieu"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            >
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous les statuts</option>
                @foreach (\App\Models\Mission::STATUTS as $code => $label)
                    <option value="{{ $code }}" @selected($filters['status'] === $code)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous les types</option>
                @foreach (\App\Models\Mission::TYPES as $code => $label)
                    <option value="{{ $code }}" @selected(($filters['type'] ?? '') === $code)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="date_document" @selected($filters['sort'] === 'date_document')>Trier par date</option>
                <option value="reference" @selected($filters['sort'] === 'reference')>Trier par référence</option>
                <option value="montant_total" @selected($filters['sort'] === 'montant_total')>Trier par montant</option>
            </select>
            <div class="flex gap-2">
                <select name="direction" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    <option value="desc" @selected($filters['direction'] === 'desc')>Décroissant</option>
                    <option value="asc" @selected($filters['direction'] === 'asc')>Croissant</option>
                </select>
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Filtrer</button>
            </div>
        </form>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Référence / Objet</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Période</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Données</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Budget</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($missions as $mission)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $mission->reference }}</div>
                                <p class="mt-1 max-w-xl text-sm text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($mission->objet, 120) }}</p>
                                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    {{ \App\Models\Mission::TYPES[$mission->type] ?? $mission->type }} • {{ $mission->departement?->nom ?? 'Sans structure' }} • créé par {{ $mission->createur?->name ?? '—' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                <div>{{ $mission->date_document?->format('d/m/Y') }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $mission->duree_texte }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                <div>{{ $mission->participants_count }} participant(s)</div>
                                <div>{{ $mission->nombre_jours }} jour(s)</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    @if ($mission->estMemeVille())
                                        {{ $mission->nombre_tickets_carburant }} ticket(s)
                                    @else
                                        {{ $mission->zone_label ?? 'Zone à définir' }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                {{ number_format((float) $mission->montant_total, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $mission->statut === 'finalise' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' }}">
                                    {{ \App\Models\Mission::STATUTS[$mission->statut] ?? $mission->statut }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-1">
                                    <x-actions.view :href="route('missions.show', $mission)" />
                                    @can('edit_missions')
                                        <x-actions.edit :href="route('missions.edit', $mission)" />
                                    @endcan
                                    @can('delete_missions')
                                        <x-actions.delete :action="route('missions.destroy', $mission)" confirm="Confirmer la suppression de cette mission ?" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                Aucune mission trouvée avec les filtres actuels.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $missions->links() }}
        </div>
    </div>
</x-layouts::app>
