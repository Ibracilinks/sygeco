<x-layouts::app title="Détails Objectif - {{ $objectif->code }}">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $objectif->libelle }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Code: {{ $objectif->code }} • Année: {{ $objectif->annee }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('edit_objectifs')
                    <a href="{{ route('objectifs.edit', $objectif) }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-400">Modifier</a>
                @endcan
                <a href="{{ route('objectifs.index') }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Retour</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</p>
                <p class="mt-2 text-lg font-semibold {{ $objectif->statut === 'actif' ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">{{ $objectif->statut === 'actif' ? 'Actif' : 'Inactif' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Résultats</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $stats['nb_resultats'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Extrants</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $stats['nb_extrants'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $stats['nb_activites'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Budget total</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($stats['budget_total'], 0, ',', ' ') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Résultats et extrants</h2>
                    @can('create_resultats')
                        <a href="{{ route('resultats.create', ['objectif_id' => $objectif->id]) }}" class="text-sm font-medium text-sky-700 hover:text-sky-600 dark:text-sky-300">Ajouter un résultat</a>
                    @endcan
                </div>

                @if ($objectif->resultats->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucun résultat associé à cet objectif.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($objectif->resultats as $resultat)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/60">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $resultat->code }}</p>
                                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ Str::limit($resultat->libelle, 100) }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Extrants: {{ $resultat->extrants->count() }}</p>
                                    </div>
                                    <x-actions.view :href="route('resultats.show', $resultat)" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Indicateurs clés</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Budget moyen / activité</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ number_format($stats['budget_moyen'], 0, ',', ' ') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Ordre d'affichage</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $objectif->ordre ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Résultats actifs</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $objectif->resultats->where('is_active', true)->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Description</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $objectif->description }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-layouts::app>
