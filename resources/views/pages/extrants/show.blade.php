<x-layouts::app title="Détails Extrant">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl overflow-y-auto p-6">

        <!-- En-tête avec navigation -->
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span
                        class="px-2 py-1 text-xs rounded-full {{ $extrant->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                        {{ $extrant->is_active ? '✓ Actif' : '✗ Inactif' }}
                    </span>
                    <span
                        class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        Ordre {{ $extrant->ordre }}
                    </span>
                </div>
                <h1 class="text-3xl font-bold dark:text-white">{{ $extrant->libelle }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-2">Code: {{ $extrant->code }}</p>
                <div class="mt-2">
                    <a href="{{ route('objectifs.show', $extrant->objectif) }}"
                        class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12h18M9 6l-6 6 6 6"></path>
                        </svg>
                        Objectif: {{ $extrant->objectif->code }} - {{ Str::limit($extrant->objectif->libelle, 60) }}
                    </a>
                </div>
            </div>
            <div class="flex gap-2">
                @can('edit_extrants')
                    <a href="{{ route('extrants.edit', $extrant) }}"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Modifier
                    </a>
                @endcan
                <a href="{{ route('extrants.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        <!-- Ligne 1: KPIs de l'extrant -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Activités</p>
                        <p class="text-3xl font-bold dark:text-white mt-1">{{ $stats['nb_activites'] }}</p>
                        <p class="text-xs text-zinc-500 mt-1">au total</p>
                    </div>
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
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
                    <div class="rounded-full bg-green-100 dark:bg-green-900 p-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor"
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
                    <div class="rounded-full bg-purple-100 dark:bg-purple-900 p-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Départements</p>
                        <p class="text-3xl font-bold dark:text-white mt-1">{{ $stats['nb_departements'] ?? 0 }}</p>
                        <p class="text-xs text-zinc-500 mt-1">impliqués</p>
                    </div>
                    <div class="rounded-full bg-orange-100 dark:bg-orange-900 p-3">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ligne 2: Graphiques analytiques -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Graphique: Évolution du budget -->
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Évolution du Budget</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Par période</p>
                </div>
                <div class="p-6">
                    @if (isset($evolutionBudget) && count($evolutionBudget) > 0)
                        <canvas id="evolutionBudgetChart" height="250"></canvas>
                    @else
                        <div class="text-center py-8 text-zinc-500">Données insuffisantes pour le graphique</div>
                    @endif
                </div>
            </div>

            <!-- Graphique: Répartition par département -->
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Budget par Département</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Répartition budgétaire</p>
                </div>
                <div class="p-6">
                    @if (isset($budgetParDepartement) && count($budgetParDepartement) > 0)
                        <canvas id="budgetDepartementChart" height="250"></canvas>
                    @else
                        <div class="text-center py-8 text-zinc-500">Aucune donnée disponible</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ligne 3: Tableau des performances par département -->
        @if ($extrant->activites->count() > 0)
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-semibold dark:text-white">Performance par Département</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Détail des activités et budgets par
                                département</p>
                        </div>
                        @can('create_activites')
                            <a href="{{ route('activites.create', ['extrant_id' => $extrant->id]) }}"
                                class="text-sm text-blue-600 hover:underline inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter une activité
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="p-6">
                    @php
                        $activitesParDept = $extrant->activites->groupBy('departement_id');
                        $totalBudget = $extrant->activites->sum('cout');
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Département</th>
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
                                @foreach ($activitesParDept as $deptId => $activites)
                                    @php
                                        $departement = $activites->first()->departement;
                                        $budgetDept = $activites->sum('cout');
                                        $pourcentage = $totalBudget > 0 ? ($budgetDept / $totalBudget) * 100 : 0;
                                        $nbActivites = $activites->count();
                                    @endphp
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-700 transition">
                                        <td class="px-4 py-3 font-medium dark:text-white">
                                            {{ $departement->nom ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-right dark:text-white">{{ $nbActivites }}</td>
                                        <td class="px-4 py-3 text-right dark:text-white">
                                            {{ number_format($budgetDept, 0, ',', ' ') }}</td>
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
                                            <a href="#" class="text-blue-500 hover:text-blue-700">
                                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-neutral-50 dark:bg-zinc-900">
                                <tr>
                                    <td class="px-4 py-3 font-semibold dark:text-white">Total</td>
                                    <td class="px-4 py-3 text-right font-semibold dark:text-white">
                                        {{ $extrant->activites->count() }}</td>
                                    <td class="px-4 py-3 text-right font-semibold dark:text-white">
                                        {{ number_format($totalBudget, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold dark:text-white">100%</td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ligne 4: Liste des activités -->
        @if ($extrant->activites->count() > 0)
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold dark:text-white">Liste des Activités
                            ({{ $extrant->activites->count() }})</h2>
                        @can('create_activites')
                            <a href="{{ route('activites.create', ['extrant_id' => $extrant->id]) }}"
                                class="text-sm text-blue-600 hover:underline inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter une activité
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach ($extrant->activites->sortByDesc('created_at') as $activite)
                            <div
                                class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-zinc-700 transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span
                                                class="font-medium dark:text-white">{{ Str::limit($activite->nom_activite, 80) }}</span>
                                            <span
                                                class="px-2 py-0.5 text-xs rounded-full
                                                {{ $activite->statut == 'valide'
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                    : ($activite->statut == 'soumis'
                                                        ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300') }}">
                                                {{ $activite->statut_label }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-zinc-500 mt-2">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                    </path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                {{ $activite->departement->nom ?? 'N/A' }}
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                                {{ number_format($activite->cout, 0, ',', ' ') }} FCFA
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                Saisi le
                                                {{ $activite->date_saisie ? $activite->date_saisie->format('d/m/Y') : 'N/A' }}
                                            </div>
                                        </div>
                                        @if ($activite->trimestres_selectionnes)
                                            <div class="flex items-center gap-1 text-xs text-zinc-400 mt-2">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                    </path>
                                                </svg>
                                                Trimestres: {{ $activite->trimestres_selectionnes }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex gap-2 ml-4">
                                        <a href="{{ route('activites.show', $activite) }}"
                                            class="text-blue-500 hover:text-blue-700" title="Voir détails">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-zinc-400 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p class="text-zinc-500 dark:text-zinc-400">Aucune activité pour cet extrant</p>
                    @can('create_activites')
                        <a href="{{ route('activites.create', ['extrant_id' => $extrant->id]) }}"
                            class="inline-flex items-center px-4 py-2 mt-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Ajouter une activité
                        </a>
                    @endcan
                </div>
            </div>
        @endif

    </div>
</x-layouts::app>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique évolution du budget
            @if (isset($evolutionBudget) && count($evolutionBudget) > 0)
                const evolutionCtx = document.getElementById('evolutionBudgetChart').getContext('2d');
                new Chart(evolutionCtx, {
                    type: 'line',
                    data: {
                        labels: @json(array_keys($evolutionBudget)),
                        datasets: [{
                            label: 'Budget (FCFA)',
                            data: @json(array_values($evolutionBudget)),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
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
                                        return 'Budget: ' + new Intl.NumberFormat('fr-FR').format(
                                            context.raw) + ' FCFA';
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
            @endif

            // Graphique budget par département
            @if (isset($budgetParDepartement) && count($budgetParDepartement) > 0)
                const deptCtx = document.getElementById('budgetDepartementChart').getContext('2d');
                new Chart(deptCtx, {
                    type: 'bar',
                    data: {
                        labels: @json(array_keys($budgetParDepartement)),
                        datasets: [{
                            label: 'Budget (FCFA)',
                            data: @json(array_values($budgetParDepartement)),
                            backgroundColor: '#10b981',
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
                                        return 'Budget: ' + new Intl.NumberFormat('fr-FR').format(
                                            context.raw) + ' FCFA';
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
            @endif
        });
    </script>
@endpush
