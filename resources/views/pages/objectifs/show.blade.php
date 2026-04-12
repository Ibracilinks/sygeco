<x-layouts::app title="Objectif Stratégique - {{ $objectif->code }}">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl overflow-y-auto p-6">

        <!-- En-tête avec navigation -->
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="h-16 w-16 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
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
                    <h1 class="text-2xl font-bold dark:text-white">{{ $objectif->libelle }}</h1>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-1">Code: {{ $objectif->code }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                @can('edit_objectifs')
                    <a href="{{ route('objectifs.edit', $objectif) }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Modifier
                    </a>
                @endcan
                <a href="{{ route('objectifs.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        <!-- Ligne 1: KPIs Globaux -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Résultats</p>
                        <p class="text-3xl font-bold text-blue-900 dark:text-blue-100 mt-1">{{ $stats['nb_resultats'] }}
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">stratégiques</p>
                    </div>
                    <div class="rounded-full bg-blue-200 dark:bg-blue-800 p-3">
                        <svg class="w-6 h-6 text-blue-700 dark:text-blue-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-950 dark:to-green-900 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-700 dark:text-green-300">Extrants</p>
                        <p class="text-3xl font-bold text-green-900 dark:text-green-100 mt-1">
                            {{ $stats['nb_extrants'] }}</p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">livrables</p>
                    </div>
                    <div class="rounded-full bg-green-200 dark:bg-green-800 p-3">
                        <svg class="w-6 h-6 text-green-700 dark:text-green-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-950 dark:to-purple-900 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-700 dark:text-purple-300">Activités</p>
                        <p class="text-3xl font-bold text-purple-900 dark:text-purple-100 mt-1">
                            {{ $stats['nb_activites'] }}</p>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">réalisées</p>
                    </div>
                    <div class="rounded-full bg-purple-200 dark:bg-purple-800 p-3">
                        <svg class="w-6 h-6 text-purple-700 dark:text-purple-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-950 dark:to-orange-900 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-orange-700 dark:text-orange-300">Budget Total</p>
                        <p class="text-3xl font-bold text-orange-900 dark:text-orange-100 mt-1">
                            {{ number_format($stats['budget_total'] / 1000000, 1) }} M</p>
                        <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">FCFA</p>
                    </div>
                    <div class="rounded-full bg-orange-200 dark:bg-orange-800 p-3">
                        <svg class="w-6 h-6 text-orange-700 dark:text-orange-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-950 dark:to-pink-900 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-pink-700 dark:text-pink-300">Budget Moyen</p>
                        <p class="text-3xl font-bold text-pink-900 dark:text-pink-100 mt-1">
                            {{ $stats['budget_moyen'] ? number_format($stats['budget_moyen'] / 1000000, 1) : '0' }} M
                        </p>
                        <p class="text-xs text-pink-600 dark:text-pink-400 mt-1">par activité</p>
                    </div>
                    <div class="rounded-full bg-pink-200 dark:bg-pink-800 p-3">
                        <svg class="w-6 h-6 text-pink-700 dark:text-pink-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ligne 2: Graphiques -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Graphique: Budget par Résultat -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Budget par Résultat</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Répartition budgétaire (FCFA)</p>
                </div>
                <div class="h-80">
                    <canvas id="budgetResultatChart"></canvas>
                </div>
            </div>

            <!-- Graphique: Distribution des Activités -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Distribution des Activités</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Par résultat</p>
                </div>
                <div class="h-80">
                    <canvas id="distributionActivitesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Ligne 3: Arbre hiérarchique complet -->
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
            <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold dark:text-white">Arbre hiérarchique complet</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Résultats → Extrants → Activités</p>
                    </div>
                    @can('create_resultats')
                        <a href="{{ route('resultats.create', ['objectif_id' => $objectif->id]) }}"
                            class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Ajouter un résultat
                        </a>
                    @endcan
                </div>
            </div>
            <div class="p-6">
                @if ($objectif->resultats->count() > 0)
                    <div class="space-y-6">
                        @foreach ($objectif->resultats as $resultat)
                            <div class="border rounded-lg border-neutral-200 dark:border-neutral-700 overflow-hidden">
                                <!-- Résultat -->
                                <div
                                    class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950 p-4">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-lg font-semibold dark:text-white">{{ $resultat->code }}</span>
                                                <span
                                                    class="px-2 py-0.5 text-xs rounded-full {{ $resultat->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                    {{ $resultat->is_active ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                                {{ $resultat->libelle }}</p>
                                            <div class="flex gap-4 mt-2 text-xs text-gray-500">
                                                <span>📦 {{ $resultat->extrants->count() }} extrants</span>
                                                <span>📝
                                                    {{ $resultat->extrants->sum(fn($e) => $e->activites->count()) }}
                                                    activités</span>
                                                <span>💰
                                                    {{ number_format($resultat->extrants->sum(fn($e) => $e->activites->sum('cout')) / 1000000, 1) }}
                                                    M FCFA</span>
                                            </div>
                                        </div>
                                        <a href="{{ route('resultats.show', $resultat) }}"
                                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>

                                <!-- Extrants -->
                                <div class="p-4 space-y-3">
                                    @foreach ($resultat->extrants as $extrant)
                                        <div class="border-l-4 border-green-500 pl-4 ml-4">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="font-mono text-sm font-semibold dark:text-white">{{ $extrant->code }}</span>
                                                        <span
                                                            class="text-sm text-gray-600 dark:text-gray-300">{{ $extrant->libelle }}</span>
                                                        <span
                                                            class="px-1.5 py-0.5 text-xs rounded-full {{ $extrant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $extrant->is_active ? 'Actif' : 'Inactif' }}
                                                        </span>
                                                    </div>
                                                    <div class="flex gap-3 mt-1 text-xs text-gray-500">
                                                        <span>📝 {{ $extrant->activites->count() }} activités</span>
                                                        <span>💰
                                                            {{ number_format($extrant->activites->sum('cout') / 1000000, 1) }}
                                                            M FCFA</span>
                                                    </div>

                                                    <!-- Activités de l'extrant -->
                                                    @if ($extrant->activites->count() > 0)
                                                        <div class="mt-3 space-y-1">
                                                            @foreach ($extrant->activites->take(3) as $activite)
                                                                <div
                                                                    class="flex items-center gap-2 text-sm border-l-2 border-purple-300 pl-3">
                                                                    <span
                                                                        class="text-xs text-gray-500">{{ $activite->code ?? 'N/A' }}</span>
                                                                    <span
                                                                        class="text-gray-700 dark:text-gray-300">{{ Str::limit($activite->nom_activite, 50) }}</span>
                                                                    <span
                                                                        class="px-1.5 py-0.5 text-xs rounded-full
                                                                        {{ $activite->statut == 'valide'
                                                                            ? 'bg-green-100 text-green-800'
                                                                            : ($activite->statut == 'soumis'
                                                                                ? 'bg-yellow-100 text-yellow-800'
                                                                                : 'bg-gray-100 text-gray-800') }}">
                                                                        {{ ucfirst($activite->statut) }}
                                                                    </span>
                                                                    <a href="{{ route('activites.show', $activite) }}"
                                                                        class="text-blue-500 hover:text-blue-700">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2" d="M9 5l7 7-7 7" />
                                                                        </svg>
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                            @if ($extrant->activites->count() > 3)
                                                                <div class="text-xs text-gray-500 italic ml-3">
                                                                    + {{ $extrant->activites->count() - 3 }} autres
                                                                    activités
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="mt-2 text-xs text-gray-400 italic">
                                                            Aucune activité pour cet extrant
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex gap-2">
                                                    @can('create_activites')
                                                        <a href="{{ route('activites.create', ['extrant_id' => $extrant->id]) }}"
                                                            class="text-green-600 hover:text-green-800"
                                                            title="Ajouter activité">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </a>
                                                    @endcan
                                                    <a href="{{ route('extrants.show', $extrant) }}"
                                                        class="text-indigo-600 hover:text-indigo-800"
                                                        title="Voir extrant">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @can('create_extrants')
                                        <div class="ml-8 mt-2">
                                            <a href="{{ route('extrants.create', ['resultat_id' => $resultat->id]) }}"
                                                class="text-xs text-blue-600 hover:underline inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                                Ajouter un extrant
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-zinc-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-zinc-500 dark:text-zinc-400">Aucun résultat associé à cet objectif</p>
                        @can('create_resultats')
                            <a href="{{ route('resultats.create', ['objectif_id' => $objectif->id]) }}"
                                class="inline-flex items-center px-4 py-2 mt-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Ajouter un résultat
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>

        <!-- Ligne 4: Dernières activités -->
        @if ($stats['nb_activites'] > 0)
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Dernières activités</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Les 10 activités les plus récentes</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-zinc-900">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Code</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Nom de l'activité</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Résultat</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Extrant</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Coût</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Statut</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @php
                                $recentActivites = collect();
                                foreach ($objectif->resultats as $resultat) {
                                    foreach ($resultat->extrants as $extrant) {
                                        $recentActivites = $recentActivites->concat($extrant->activites);
                                    }
                                }
                                $recentActivites = $recentActivites->sortByDesc('created_at')->take(10);
                            @endphp
                            @foreach ($recentActivites as $activite)
                                <tr>
                                    <td class="px-6 py-4 font-mono text-sm dark:text-white">
                                        {{ $activite->code ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 dark:text-white">
                                        {{ Str::limit($activite->nom_activite, 50) }}</td>
                                    <td class="px-6 py-4 text-sm dark:text-white">
                                        {{ $activite->extrant->resultat->code ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm dark:text-white">
                                        {{ $activite->extrant->code ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-right dark:text-white">
                                        {{ number_format($activite->cout, 0, ',', ' ') }} FCFA</td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full
                                    {{ $activite->statut == 'valide'
                                        ? 'bg-green-100 text-green-800'
                                        : ($activite->statut == 'soumis'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($activite->statut) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('activites.show', $activite) }}"
                                            class="text-blue-600 hover:text-blue-800">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-layouts::app>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            function initCharts() {
                // Données pour les graphiques
                var resultats = @json($objectif->resultats);

                // 1. Budget par Résultat
                var budgetCtx = document.getElementById('budgetResultatChart')?.getContext('2d');
                if (budgetCtx && resultats.length > 0) {
                    var budgetLabels = resultats.map(function(r) {
                        return r.code;
                    });
                    var budgetData = resultats.map(function(r) {
                        return r.extrants.reduce(function(sum, e) {
                            return sum + e.activites.reduce(function(s, a) {
                                return s + (a.cout || 0);
                            }, 0);
                        }, 0);
                    });

                    new Chart(budgetCtx, {
                        type: 'bar',
                        data: {
                            labels: budgetLabels,
                            datasets: [{
                                label: 'Budget (FCFA)',
                                data: budgetData,
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
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(v) {
                                            return (v / 1000000).toFixed(1) + 'M';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // 2. Distribution des Activités
                var distribCtx = document.getElementById('distributionActivitesChart')?.getContext('2d');
                if (distribCtx && resultats.length > 0) {
                    var distribLabels = resultats.map(function(r) {
                        return r.code;
                    });
                    var distribData = resultats.map(function(r) {
                        return r.extrants.reduce(function(sum, e) {
                            return sum + e.activites.length;
                        }, 0);
                    });

                    new Chart(distribCtx, {
                        type: 'doughnut',
                        data: {
                            labels: distribLabels,
                            datasets: [{
                                data: distribData,
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
                                }
                            }
                        }
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCharts);
            } else {
                initCharts();
            }
        })();
    </script>
@endpush
