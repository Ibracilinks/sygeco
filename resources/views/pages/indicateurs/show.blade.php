<x-layouts::app title="Détails Indicateur">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">{{ $indicateur->libelle }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-1">Code: {{ $indicateur->code }}</p>
            </div>
            <div class="flex gap-2">
                @can('edit_indicateurs')
                    <a href="{{ route('indicateurs.edit', $indicateur) }}"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                        Modifier
                    </a>
                @endcan
                <a href="{{ route('indicateurs.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informations principales -->
            <div
                class="lg:col-span-2 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Informations générales</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-zinc-500">Objectif associé</div>
                        <div class="dark:text-white">
                            <a href="{{ route('objectifs.show', $indicateur->objectif) }}"
                                class="text-blue-600 hover:underline">
                                {{ $indicateur->objectif->code }} -
                                {{ Str::limit($indicateur->objectif->libelle, 100) }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Type</div>
                        <div>
                            <span
                                class="px-2 py-1 text-xs rounded-full
                                {{ $indicateur->type == 'performance'
                                    ? 'bg-blue-100 text-blue-800'
                                    : ($indicateur->type == 'gestion'
                                        ? 'bg-green-100 text-green-800'
                                        : ($indicateur->type == 'qualite'
                                            ? 'bg-purple-100 text-purple-800'
                                            : 'bg-orange-100 text-orange-800')) }}">
                                {{ $indicateur->type_label }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Description</div>
                        <div class="dark:text-white">{{ $indicateur->description ?? 'Aucune description' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Formule de calcul</div>
                        <div class="font-mono text-sm dark:text-white">
                            {{ $indicateur->formule_calcul ?? 'Non définie' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Source des données</div>
                        <div class="dark:text-white">{{ $indicateur->source_donnee ?? 'Non définie' }}</div>
                    </div>
                </div>
            </div>

            <!-- Métriques et cibles -->
            <div class="space-y-6">
                <!-- Cibles -->
                <div
                    class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                    <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                        <h2 class="text-lg font-semibold dark:text-white">Objectifs</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="text-center p-4 rounded-lg bg-blue-50 dark:bg-blue-950">
                            <div class="text-sm text-zinc-500">Valeur cible</div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ $indicateur->cible ? number_format($indicateur->cible, 0, ',', ' ') . ' ' . ($indicateur->unite ?? '') : 'Non définie' }}
                            </div>
                        </div>
                        <div class="text-center p-4 rounded-lg bg-orange-50 dark:bg-orange-950">
                            <div class="text-sm text-zinc-500">Seuil d'alerte</div>
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                {{ $indicateur->seuil_alerte ? number_format($indicateur->seuil_alerte, 0, ',', ' ') . ' ' . ($indicateur->unite ?? '') : 'Non défini' }}
                            </div>
                        </div>
                        <div class="text-center p-4 rounded-lg bg-green-50 dark:bg-green-950">
                            <div class="text-sm text-zinc-500">Périodicité</div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ $indicateur->periodicite_label }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div
                    class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                    <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                        <h2 class="text-lg font-semibold dark:text-white">Statistiques</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="text-center p-4 rounded-lg bg-purple-50 dark:bg-purple-950">
                            <div class="text-sm text-zinc-500">Nombre de saisies</div>
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ $stats['nb_valeurs'] }}</div>
                        </div>
                        <div class="text-center p-4 rounded-lg bg-teal-50 dark:bg-teal-950">
                            <div class="text-sm text-zinc-500">Taux de réalisation moyen</div>
                            <div class="text-2xl font-bold text-teal-600 dark:text-teal-400">
                                {{ $stats['taux_moyen'] ? number_format($stats['taux_moyen'], 1) . '%' : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières valeurs saisies -->
        @if ($indicateur->valeurs->count() > 0)
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold dark:text-white">Historique des valeurs</h2>
                        @can('create_indicateurs')
                            <a href="{{ route('indicateurs.saisie-valeurs', $indicateur) }}"
                                class="text-sm text-blue-600 hover:underline">
                                + Saisir des valeurs
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Période</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Département</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Valeur réalisée</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Taux</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                @foreach ($indicateur->valeurs->sortByDesc('periode')->take(10) as $valeur)
                                    <tr>
                                        <td class="px-4 py-3 text-sm dark:text-white">{{ $valeur->periode }}</td>
                                        <td class="px-4 py-3 text-sm dark:text-white">
                                            {{ $valeur->departement->nom ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm dark:text-white">
                                            {{ number_format($valeur->valeur_realisee, 0, ',', ' ') }}
                                            {{ $indicateur->unite ?? '' }}</td>
                                        <td class="px-4 py-3">
                                            @php
                                                $taux = $valeur->taux_realisation;
                                                $color =
                                                    $taux >= 100
                                                        ? 'text-green-600'
                                                        : ($taux >= 75
                                                            ? 'text-blue-600'
                                                            : ($taux >= 50
                                                                ? 'text-yellow-600'
                                                                : 'text-red-600'));
                                            @endphp
                                            <span
                                                class="font-semibold {{ $color }}">{{ $taux ? number_format($taux, 1) . '%' : 'N/A' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-1 text-xs rounded-full {{ $valeur->statut == 'valide' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $valeur->statut_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                    <p class="text-zinc-500 dark:text-zinc-400">Aucune valeur saisie pour cet indicateur</p>
                    @can('create_indicateurs')
                        <a href="{{ route('indicateurs.saisie-valeurs', $indicateur) }}"
                            class="inline-flex items-center px-4 py-2 mt-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Saisir des valeurs
                        </a>
                    @endcan
                </div>
            </div>
        @endif

    </div>
</x-layouts::app>
