<x-layouts::app title="Détails Objectif Stratégique">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl overflow-y-auto p-6">

        <!-- En-tête avec navigation -->
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span
                        class="px-2 py-1 text-xs rounded-full {{ $objectif->statut == 'actif' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                        {{ $objectif->statut == 'actif' ? '✓ Actif' : '✗ Inactif' }}
                    </span>
                    <span
                        class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        Année {{ $objectif->annee }}
                    </span>
                    <span
                        class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                        Ordre {{ $objectif->ordre }}
                    </span>
                </div>
                <h1 class="text-3xl font-bold dark:text-white">{{ $objectif->libelle }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-2">Code: {{ $objectif->code }}</p>
            </div>
            <div class="flex gap-2">
                @can('edit_objectifs')
                    <a href="{{ route('objectifs.edit', $objectif) }}"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Modifier
                    </a>
                @endcan
                <a href="{{ route('objectifs.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        <!-- Ligne 1: KPIs de l'objectif -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Extrants</p>
                        <p class="text-3xl font-bold dark:text-white mt-1">{{ $stats['nb_extrants'] }}</p>
                        <p class="text-xs text-zinc-500 mt-1">déclinés en activités</p>
                    </div>
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Activités</p>
                        <p class="text-3xl font-bold dark:text-white mt-1">{{ $stats['nb_activites'] }}</p>
                        <p class="text-xs text-green-600 mt-1">{{ $stats['taux_activites'] ?? '100' }}% programmées</p>
                    </div>
                    <div class="rounded-full bg-green-100 dark:bg-green-900 p-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Budget total</p>
                        <p class="text-3xl font-bold dark:text-white mt-1">
                            {{ number_format($stats['budget_total'] / 1000000, 1) }} M</p>
                        <p class="text-xs text-zinc-500 mt-1">FCFA</p>
                    </div>
                    <div class="rounded-full bg-purple-100 dark:bg-purple-900 p-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Budget moyen</p>
                        <p class="text-3xl font-bold dark:text-white mt-1">
                            {{ $stats['budget_moyen'] ? number_format($stats['budget_moyen'] / 1000000, 1) : '0' }} M
                        </p>
                        <p class="text-xs text-zinc-500 mt-1">par activité</p>
                    </div>
                    <div class="rounded-full bg-orange-100 dark:bg-orange-900 p-3">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ligne 2: Graphiques analytiques -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Graphique: Répartition budget par extrant -->
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Budget par Extrant</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Répartition budgétaire (FCFA)</p>
                </div>
                <div class="p-6">
                    @if ($objectif->extrants->count() > 0)
                        <canvas id="budgetExtrantsChart" height="250"></canvas>
                    @else
                        <div class="text-center py-8 text-zinc-500">Aucune donnée disponible</div>
                    @endif
                </div>
            </div>

            <!-- Graphique: Distribution des activités -->
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Distribution des Activités</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Par extrant</p>
                </div>
                <div class="p-6">
                    @if ($objectif->extrants->count() > 0)
                        <canvas id="activitesExtrantsChart" height="250"></canvas>
                    @else
                        <div class="text-center py-8 text-zinc-500">Aucune donnée disponible</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ligne 3: Tableau des performances -->
        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
            <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold dark:text-white">Performances par Extrant</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Détail des activités et budgets</p>
                    </div>
                    @can('create_extrants')
                        <a href="{{ route('extrants.create', ['objectif_id' => $objectif->id]) }}"
                            class="text-sm text-blue-600 hover:underline inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Ajouter un extrant
                        </a>
                    @endcan
                </div>
            </div>
            <div class="p-6">
                @if ($objectif->extrants->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Extrant</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Code</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Nb Activités</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Budget (FCFA)</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        % du total</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Progression</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                @foreach ($objectif->extrants as $extrant)
                                    @php
                                        $budgetExtrant = $extrant->activites->sum('cout');
                                        $pourcentage =
                                            $stats['budget_total'] > 0
                                                ? ($budgetExtrant / $stats['budget_total']) * 100
                                                : 0;
                                        $nbActivites = $extrant->activites->count();
                                    @endphp
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-700 transition">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('extrants.show', $extrant) }}"
                                                class="font-medium text-blue-600 hover:underline dark:text-blue-400">
                                                {{ $extrant->libelle }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-sm dark:text-white">{{ $extrant->code }}
                                        </td>
                                        <td class="px-4 py-3 text-right dark:text-white">{{ $nbActivites }}</td>
                                        <td class="px-4 py-3 text-right dark:text-white">
                                            {{ number_format($budgetExtrant, 0, ',', ' ') }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <span
                                                    class="dark:text-white">{{ number_format($pourcentage, 1) }}%</span>
                                                <div
                                                    class="w-16 bg-neutral-200 dark:bg-neutral-700 rounded-full h-1.5">
                                                    <div class="bg-blue-600 h-1.5 rounded-full"
                                                        style="width: {{ min($pourcentage, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="text-xs text-zinc-500">{{ $nbActivites }} act.</span>
                                                <a href="{{ route('extrants.show', $extrant) }}"
                                                    class="text-blue-500 hover:text-blue-700">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-neutral-50 dark:bg-zinc-900">
                                <tr>
                                    <td colspan="2" class="px-4 py-3 font-semibold dark:text-white">Total</td>
                                    <td class="px-4 py-3 text-right font-semibold dark:text-white">
                                        {{ $stats['nb_activites'] }}</td>
                                    <td class="px-4 py-3 text-right font-semibold dark:text-white">
                                        {{ number_format($stats['budget_total'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold dark:text-white">100%</td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-zinc-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <p class="text-zinc-500 dark:text-zinc-400">Aucun extrant associé à cet objectif</p>
                        @can('create_extrants')
                            <a href="{{ route('extrants.create', ['objectif_id' => $objectif->id]) }}"
                                class="inline-flex items-center px-4 py-2 mt-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter un extrant
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>

        <!-- Ligne 4: Détail des activités -->
        @if ($stats['nb_activites'] > 0)
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Activités récentes</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Les 5 dernières activités saisies</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @php
                            $recentActivites = collect();
                            foreach ($objectif->extrants as $extrant) {
                                $recentActivites = $recentActivites->concat($extrant->activites);
                            }
                            $recentActivites = $recentActivites->sortByDesc('created_at')->take(5);
                        @endphp

                        @foreach ($recentActivites as $activite)
                            <div
                                class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-zinc-700 transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="font-medium dark:text-white">{{ $activite->nom_activite }}</span>
                                            <span
                                                class="px-2 py-0.5 text-xs rounded-full
                                            {{ $activite->statut == 'valide'
                                                ? 'bg-green-100 text-green-800'
                                                : ($activite->statut == 'soumis'
                                                    ? 'bg-yellow-100 text-yellow-800'
                                                    : 'bg-gray-100 text-gray-800') }}">
                                                {{ $activite->statut_label }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-zinc-500 mt-1">
                                            Extrant: {{ $activite->extrant->code }} |
                                            Département: {{ $activite->departement->nom ?? 'N/A' }} |
                                            Coût: {{ number_format($activite->cout, 0, ',', ' ') }} FCFA
                                        </div>
                                        @if ($activite->trimestres_selectionnes)
                                            <div class="text-xs text-zinc-400 mt-1">
                                                Trimestres: {{ $activite->trimestres_selectionnes }}
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('activites.show', $activite) }}"
                                        class="text-blue-500 hover:text-blue-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layouts::app>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique budget par extrant
            @if ($objectif->extrants->count() > 0)
                const budgetCtx = document.getElementById('budgetExtrantsChart').getContext('2d');
                const budgetExtrantsData = @json(
                    $objectif->extrants->map(function ($extrant) {
                        return $extrant->activites->sum('cout');
                    }));
                const extrantsLabels = @json($objectif->extrants->pluck('code'));

                new Chart(budgetCtx, {
                    type: 'bar',
                    data: {
                        labels: extrantsLabels,
                        datasets: [{
                            label: 'Budget (FCFA)',
                            data: budgetExtrantsData,
                            backgroundColor: '#3b82f6',
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let value = context.raw;
                                        return 'Budget: ' + new Intl.NumberFormat('fr-FR').format(
                                            value) + ' FCFA';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return (value / 1000000).toFixed(1) + 'M';
                                    }
                                }
                            }
                        }
                    }
                });

                // Graphique activités par extrant
                const activitesCtx = document.getElementById('activitesExtrantsChart').getContext('2d');
                const activitesExtrantsData = @json(
                    $objectif->extrants->map(function ($extrant) {
                        return $extrant->activites->count();
                    }));

                new Chart(activitesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: extrantsLabels,
                        datasets: [{
                            data: activitesExtrantsData,
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                                '#06b6d4', '#ec4899'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'right'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        let value = context.raw;
                                        return label + ': ' + value + ' activités';
                                    }
                                }
                            }
                        }
                    }
                });
            @endif
        });
    </script>
@endpush
