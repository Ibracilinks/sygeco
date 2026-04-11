<x-layouts::app title="Dashboard BI - DBCGOQ">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl overflow-y-auto p-6">

        <!-- En-tête -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold dark:text-white">Tableau de bord</h1>
            </div>
            <div class="flex gap-3">
                <div class="relative">
                    <select id="annee-select"
                        class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                        <option value="2025" selected>2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                </div>
                <button onclick="window.print()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Exporter PDF
                </button>
            </div>
        </div>

        <!-- Ligne 1: KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-charts.kpi-card title="Total Objectifs" :value="$stats['total_objectifs'] ?? 0" trend="up" color="blue"
                icon="document-text" />

            <x-charts.kpi-card title="Total Extrants" :value="$stats['total_extrants']" trend="up" color="green" icon="folder" />

            <x-charts.kpi-card title="Total Activités" :value="$stats['total_activites']" trend="up" color="purple"
                icon="clipboard-document-list" />

            <x-charts.kpi-card title="Budget Total" :value="number_format($stats['budget_total'] / 1000000, 1)" unit="M FCFA" trend="up" color="yellow"
                icon="currency-dollar" />
        </div>

        <!-- Ligne 2: Line Chart + Gauge -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Line Chart: Évolution des Activités -->
            <div
                class="lg:col-span-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Évolution des Activités</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Nombre d'activités par mois</p>
                </div>
                <x-charts.line-chart :labels="array_column($evolutionMensuelle, 'mois')" :datasets="[
                    [
                        'label' => 'Nombre d\'activités',
                        'data' => array_column($evolutionMensuelle, 'nb_activites'),
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                ]" :height="300" />
            </div>

            <!-- Gauge Chart: Taux de réalisation -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Taux de réalisation</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Activités validées vs total</p>
                </div>
                <x-charts.gauge-chart :value="$stats['taux_realisation']" title="Taux de complétion" unit="%" :size="200" />
            </div>
        </div>

        <!-- Ligne 3: Bar Chart + Pie Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Bar Chart: Budget par Objectif -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Budget par Objectif</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">En millions FCFA</p>
                </div>
                <x-charts.bar-chart :labels="array_column($budgetParObjectif, 'code')" :datasets="[
                    [
                        'label' => 'Budget (M FCFA)',
                        'data' => array_column($budgetParObjectif, 'budget'),
                        'backgroundColor' => '#3b82f6',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </div>

            <!-- Pie Chart: Distribution par Extrant -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Top Extrants</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Par nombre d'activités</p>
                </div>
                <x-charts.pie-chart :labels="collect($topExtrants)->take(5)->pluck('code')->toArray()" :data="collect($topExtrants)->take(5)->pluck('nb_activites')->toArray()" type="pie" :height="300" />
            </div>
        </div>

        <!-- Ligne 4: Horizontal Bar + Donut -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Horizontal Bar: Top Activités -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Top Activités</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Par coût</p>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach (array_slice($topActivites, 0, 8) as $item)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="dark:text-white truncate" style="max-width: 60%;">{{ $item['code'] }} -
                                    {{ Str::limit($item['nom_activite'], 40) }}</span>
                                <span
                                    class="font-semibold dark:text-white">{{ number_format($item['cout'] / 1000000, 1) }}
                                    M</span>
                            </div>
                            <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                                @php
                                    $maxBudget = max(array_column($topActivites, 'cout'));
                                    $width = $maxBudget > 0 ? ($item['cout'] / $maxBudget) * 100 : 0;
                                @endphp
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full"
                                    style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Donut Chart: Distribution Budgétaire -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Distribution Budgétaire</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Par tranche de coût</p>
                </div>
                <x-charts.pie-chart :labels="array_keys($distributionBudgetaire)" :data="array_values($distributionBudgetaire)" type="donut" :height="300" />
            </div>
        </div>

        <!-- Ligne 5: Bar Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Bar Chart: Activités par Statut -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Activités par Statut</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">État d'avancement</p>
                </div>
                <x-charts.bar-chart :labels="array_keys($activitesParStatut)" :datasets="[
                    [
                        'label' => 'Nombre d\'activités',
                        'data' => array_values($activitesParStatut),
                        'backgroundColor' => ['#f59e0b', '#3b82f6', '#10b981'],
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </div>

            <!-- Bar Chart: Activités par Trimestre -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Activités par Trimestre</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Répartition trimestrielle</p>
                </div>
                <x-charts.bar-chart :labels="['T1', 'T2', 'T3', 'T4']" :datasets="[
                    [
                        'label' => 'Nombre d\'activités',
                        'data' => $activitesParTrimestre,
                        'backgroundColor' => '#8b5cf6',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </div>
        </div>

    </div>

    <script>
        // Filtre par année
        var anneeSelect = document.getElementById('annee-select');
        if (anneeSelect) {
            anneeSelect.addEventListener('change', function(e) {
                window.location.href = '?annee=' + e.target.value;
            });
        }
    </script>
</x-layouts::app>
