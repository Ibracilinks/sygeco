<x-layouts::app title="Exercices">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Exercices</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Pilotage des exercices budgétaires, fenêtres de saisie et statut d'activité.</p>
            </div>
            @can('manage_exercices')
                <a href="{{ route('exercices.create') }}" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Nouvel exercice
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['total'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/25">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Actif</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($summary['actif'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/25">
                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Clôturés</p>
                <p class="mt-2 text-3xl font-semibold text-amber-800 dark:text-amber-100">{{ number_format($summary['cloture'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Brouillons</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['brouillon'] ?? 0) }}</p>
            </div>
        </div>

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

        <form method="GET" action="{{ route('exercices.index') }}" class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <select name="statut" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous statuts</option>
                <option value="actif" @selected(request('statut') === 'actif')>Actif</option>
                <option value="cloture" @selected(request('statut') === 'cloture')>Clôturé</option>
                <option value="brouillon" @selected(request('statut') === 'brouillon')>Brouillon</option>
            </select>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Filtrer</button>
            @if (request('statut'))
                <a href="{{ route('exercices.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400">Réinitialiser</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Année</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Période</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Fenêtre de saisie</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Objectifs</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @php
                        $statutColors = [
                            'brouillon' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-100',
                            'actif' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                            'cloture' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-100',
                        ];
                    @endphp
                    @forelse ($exercices as $exercice)
                        @php $isActiveContext = \App\Support\ActiveExercice::id() === (int) $exercice->id; @endphp
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $exercice->annee }}</p>
                                @if ($isActiveContext)
                                    <span class="mt-1 inline-flex rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800 dark:bg-sky-900/40 dark:text-sky-200">Contexte actif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                {{ $exercice->date_debut->format('d/m/Y') }} — {{ $exercice->date_fin->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                @if ($exercice->date_limite_saisie)
                                    <p>{{ optional($exercice->date_ouverture_saisie)->format('d/m/Y') ?? '—' }} → {{ $exercice->date_limite_saisie->format('d/m/Y') }}</p>
                                    @php $jours = $exercice->joursAvantLimite(); @endphp
                                    <p class="text-xs {{ $jours !== null && $jours < 0 ? 'text-rose-600 dark:text-rose-300' : 'text-slate-500 dark:text-slate-400' }}">
                                        {{ $jours === null ? '' : ($jours < 0 ? 'Délai dépassé' : ($jours === 0 ? "Dernier jour" : "J-{$jours}")) }}
                                    </p>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500">Non définie</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $exercice->objectifs_count }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statutColors[$exercice->statut] ?? $statutColors['brouillon'] }}">
                                    {{ ucfirst($exercice->statut) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <x-actions.activate :action="route('exercices.activate', $exercice)" label="Utiliser" />
                                    <x-actions.view :href="route('exercices.show', $exercice)" />
                                    @can('manage_exercices')
                                        <x-actions.edit :href="route('exercices.edit', $exercice)" />
                                        <x-actions.delete :action="route('exercices.destroy', $exercice)"
                                            confirm="Supprimer cet exercice ?" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Aucun exercice trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $exercices->links() }}
        </div>
    </div>
</x-layouts::app>
