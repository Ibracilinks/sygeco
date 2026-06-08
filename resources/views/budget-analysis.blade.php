<x-layouts::app title="Analyse Budgétaire - DBCGOQ">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl overflow-y-auto p-6">

        <!-- En-tête -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold dark:text-white">Analyse Budgétaire</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Analyse détaillée des budgets et dépenses</p>
            </div>
            <div class="flex gap-3">
                <div class="relative">
                    <select id="annee-select"
                        class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm dark:text-white">
                        <option value="2026" {{ $annee == 2026 ? 'selected' : '' }}>2026</option>
                        <option value="2025" {{ $annee == 2025 ? 'selected' : '' }}>2025</option>
                        <option value="2024" {{ $annee == 2024 ? 'selected' : '' }}>2024</option>
                        <option value="2023" {{ $annee == 2023 ? 'selected' : '' }}>2023</option>
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

        <!-- Section 1: Vue d'ensemble budgétaire -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-charts.kpi-card title="Budget Total" :value="$budgetOverview['total_budget']" unit="M FCFA" trend="up" color="blue"
                icon="currency-dollar" divisor="1000000" decimals="1" />

            <x-charts.kpi-card title="Coût Moyen par Activité" :value="$budgetOverview['average_cost_per_activity']" unit="FCFA" trend="neutral"
                color="green" icon="calculator" decimals="0" />

            <x-charts.kpi-card title="Taux d'Utilisation" :value="$budgetOverview['budget_utilization_rate']" unit="%" trend="up" color="purple"
                icon="chart-bar" decimals="1" />

            <x-charts.kpi-card title="Efficacité Dépenses" :value="$budgetVsActual['spending_efficiency']" unit="%" trend="up"
                color="yellow" icon="trending-up" decimals="1" />
        </div>

        <!-- Section 2: Budget vs Réel -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Budget vs Réel par Objectif -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Budget vs Réel par Objectif</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Comparaison budgétaire</p>
                </div>
                <x-charts.bar-chart :labels="array_column($budgetVsActual['planned_vs_actual'], 'code')" :datasets="[
                    [
                        'label' => 'Budget Planifié (M FCFA)',
                        'data' => array_map(function ($item) {
                            return $item['budget'] * 1.1;
                        }, $budgetVsActual['planned_vs_actual']),
                        'backgroundColor' => '#3b82f6',
                        'borderRadius' => 8,
                    ],
                    [
                        'label' => 'Dépenses Réelles (M FCFA)',
                        'data' => array_column($budgetVsActual['planned_vs_actual'], 'budget'),
                        'backgroundColor' => '#10b981',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </div>

            <!-- Évolution Mensuelle des Dépenses -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Évolution Mensuelle des Dépenses</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Budget dépensé par mois</p>
                </div>
                <x-charts.line-chart :labels="array_column($budgetVsActual['monthly_spending'], 'mois')" :datasets="[
                    [
                        'label' => 'Dépenses (M FCFA)',
                        'data' => array_map(function ($item) {
                            return $item['budget'] / 1000000;
                        }, $budgetVsActual['monthly_spending']),
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                ]" :height="300" />
            </div>
        </div>

        <!-- Section 3: Analyse par Département -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Dépenses par Département -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Dépenses par Département</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Répartition budgétaire</p>
                </div>
                <x-charts.bar-chart :labels="array_column($departmentBudgetAnalysis['department_spending'], 'nom')" :datasets="[
                    [
                        'label' => 'Budget Dépensé (M FCFA)',
                        'data' => array_map(function ($item) {
                            return $item['budget'] / 1000000;
                        }, $departmentBudgetAnalysis['department_spending']),
                        'backgroundColor' => '#8b5cf6',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </div>

            <!-- Efficacité par Département -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Efficacité par Département</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Taux d'efficacité budgétaire</p>
                </div>
                <x-charts.bar-chart :labels="array_column($departmentBudgetAnalysis['department_efficiency'], 'nom')" :datasets="[
                    [
                        'label' => 'Efficacité (%)',
                        'data' => array_column($departmentBudgetAnalysis['department_efficiency'], 'efficiency'),
                        'backgroundColor' => '#06b6d4',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </div>
        </div>

        <!-- Section 4: Planification Trimestrielle -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Répartition Trimestrielle -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Répartition Trimestrielle</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Activités par trimestre</p>
                </div>
                <x-charts.pie-chart :labels="['Trimestre 1', 'Trimestre 2', 'Trimestre 3', 'Trimestre 4']" :data="$quarterlyBudgetPlanning['quarterly_distribution']" type="pie" :height="300" />
            </div>

            <!-- Performance Trimestrielle -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Performance Trimestrielle</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Budget vs Réalisé</p>
                </div>
                <x-charts.bar-chart :labels="array_keys($quarterlyBudgetPlanning['quarterly_performance'])" :datasets="[
                    [
                        'label' => 'Budget Planifié',
                        'data' => array_column($quarterlyBudgetPlanning['quarterly_performance'], 'budget'),
                        'backgroundColor' => '#3b82f6',
                        'borderRadius' => 8,
                    ],
                    [
                        'label' => 'Réalisé',
                        'data' => array_column($quarterlyBudgetPlanning['quarterly_performance'], 'actual'),
                        'backgroundColor' => '#10b981',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </div>
        </div>

        <!-- Section 5: Alertes Budgétaires -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
            <div class="border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                <h2 class="text-lg font-semibold dark:text-white">Alertes Budgétaires</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Dépassements et sous-utilisations</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Départements en dépassement -->
                <div>
                    <h3 class="text-sm font-medium text-red-600 dark:text-red-400 mb-3">🚨 Dépassements Budgetaires</h3>
                    <div class="space-y-2">
                        @forelse($budgetAlerts['over_budget_departments'] as $dept)
                            <div class="flex justify-between items-center p-2 bg-red-50 dark:bg-red-900/20 rounded">
                                <span class="text-sm dark:text-white">{{ $dept['nom'] }}</span>
                                <span class="text-sm font-medium text-red-600">
                                    {{ number_format(abs($dept['variance']) / 1000000, 1) }}M FCFA
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Aucun dépassement détecté</p>
                        @endforelse
                    </div>
                </div>

                <!-- Budgets sous-utilisés -->
                <div>
                    <h3 class="text-sm font-medium text-yellow-600 dark:text-yellow-400 mb-3">⚠️ Budgets Sous-utilisés
                    </h3>
                    <div class="space-y-2">
                        @forelse($budgetAlerts['under_utilized_budgets'] as $dept)
                            <div
                                class="flex justify-between items-center p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded">
                                <span class="text-sm dark:text-white">{{ $dept['nom'] }}</span>
                                <span class="text-sm font-medium text-yellow-600">
                                    {{ number_format($dept['variance'] / 1000000, 1) }}M FCFA
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Aucune sous-utilisation détectée</p>
                        @endforelse
                    </div>
                </div>

                <!-- Activités à coût élevé -->
                <div>
                    <h3 class="text-sm font-medium text-purple-600 dark:text-purple-400 mb-3">💰 Activités Coûteuses
                    </h3>
                    <div class="space-y-2">
                        @forelse(array_slice($budgetAlerts['high_cost_activities'], 0, 3) as $activity)
                            <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded">
                                <p class="text-sm dark:text-white truncate">{{ $activity['nom_activite'] }}</p>
                                <p class="text-sm font-medium text-purple-600">
                                    {{ number_format($activity['cout'] / 1000000, 1) }}M FCFA
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Aucune activité coûteuse détectée</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 6: Tendances et Prévisions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Tendances Annuelles -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Tendances Annuelles</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Évolution budgétaire sur 3 ans</p>
                </div>
                <x-charts.line-chart :labels="array_column($budgetTrends['yearly_trends'], 'year')" :datasets="[
                    [
                        'label' => 'Budget (M FCFA)',
                        'data' => array_map(function ($item) {
                            return $item['budget'] / 1000000;
                        }, $budgetTrends['yearly_trends']),
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Dépenses Réelles (M FCFA)',
                        'data' => array_map(function ($item) {
                            return $item['actual'] / 1000000;
                        }, $budgetTrends['yearly_trends']),
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4,
                    ],
                ]" :height="300" />
            </div>

            <!-- Prévisions Budgétaires -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Prévisions Budgétaires</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Projections pour les prochaines années</p>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Taux de Croissance</p>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                +{{ number_format($budgetTrends['growth_rate'], 1) }}%
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-blue-600 dark:text-blue-400">Année {{ $annee + 1 }}</p>
                            <p class="text-lg font-semibold text-blue-700 dark:text-blue-300">
                                {{ number_format($budgetTrends['forecast'][$annee + 1] / 1000000, 1) }}M FCFA
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">Projection</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300">
                                Année {{ $annee + 2 }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-green-600 dark:text-green-400">Estimation</p>
                            <p class="text-lg font-semibold text-green-700 dark:text-green-300">
                                {{ number_format($budgetTrends['forecast'][$annee + 2] / 1000000, 1) }}M FCFA
                            </p>
                        </div>
                    </div>
                </div>
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
