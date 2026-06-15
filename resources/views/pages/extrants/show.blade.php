<x-layouts::app title="Détails Extrant - {{ $extrant->code }}">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $extrant->libelle }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Code: {{ $extrant->code }}</p>
                <a href="{{ route('objectifs.show', $extrant->objectif) }}" class="mt-1 inline-flex items-center text-sm font-medium text-sky-700 hover:text-sky-600 dark:text-sky-300">
                    Objectif: {{ $extrant->objectif->code }} - {{ Str::limit($extrant->objectif->libelle, 80) }}
                </a>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('edit_extrants')
                    <a href="{{ route('extrants.edit', $extrant) }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-400">Modifier</a>
                @endcan
                <a href="{{ route('extrants.index') }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Retour</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</p>
                <p class="mt-2 text-lg font-semibold {{ $extrant->is_active ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">{{ $extrant->is_active ? 'Actif' : 'Inactif' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $stats['nb_activites'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Départements</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $stats['nb_departements'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Budget total</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($stats['budget_total'], 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Budget moyen</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($stats['budget_moyen'], 0, ',', ' ') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Activités rattachées</h2>
                    @can('create_activites')
                        <a href="{{ route('activites.create', ['extrant_id' => $extrant->id]) }}" class="text-sm font-medium text-sky-700 hover:text-sky-600 dark:text-sky-300">Ajouter une activité</a>
                    @endcan
                </div>

                @if ($extrant->activites->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucune activité associée à cet extrant.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($extrant->activites as $activite)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/60">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ Str::limit($activite->nom_activite, 100) }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Département: {{ $activite->departement->nom ?? '-' }}</p>
                                    </div>
                                    <x-actions.view :href="route('activites.show', $activite)" />
                                </div>
                                <div class="mt-3 grid grid-cols-1 gap-2 text-xs text-slate-500 dark:text-slate-400 sm:grid-cols-3">
                                    <span>Statut: {{ $activite->statut_label }}</span>
                                    <span>Budget: {{ number_format($activite->cout, 0, ',', ' ') }}</span>
                                    <span>Trimestres: {{ $activite->trimestres_selectionnes ?: '-' }}</span>
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
                        <dt class="text-slate-500 dark:text-slate-400">Activités validées</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $activitesParStatut['valide'] ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Activités soumises</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $activitesParStatut['soumis'] ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Activités brouillon</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $activitesParStatut['brouillon'] ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Ordre d'affichage</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $extrant->ordre ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-layouts::app>
