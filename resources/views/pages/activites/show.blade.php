<x-layouts::app :title="__('Activité : ' . $activite->code)">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div
                        class="h-16 w-16 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xl font-bold">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activite->code }}</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $activite->libelle }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('activites.edit', $activite) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('activites.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            {{-- Informations générales --}}
            <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informations générales</h3>
                </div>
                <div class="divide-y divide-gray-200 p-4 dark:divide-gray-700">
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Code</span>
                        <span class="font-mono font-bold text-indigo-600">{{ $activite->code }}</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Libellé</span>
                        <span>{{ $activite->libelle }}</span>
                    </div>
                    <div class="py-2">
                        <span class="text-gray-500">Description</span>
                        <p class="mt-1 text-gray-700 dark:text-gray-300">{{ $activite->description ?? '-' }}</p>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Ordre</span>
                        <span>{{ $activite->ordre }}</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Statut</span>
                        @if ($activite->is_active)
                            <span
                                class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Actif</span>
                        @else
                            <span
                                class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Inactif</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Budget et planning --}}
            <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Budget et planning</h3>
                </div>
                <div class="divide-y divide-gray-200 p-4 dark:divide-gray-700">
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Budget prévisionnel</span>
                        <span
                            class="font-bold text-indigo-600">{{ number_format($activite->budget_previsionnel_global, 0, ',', ' ') }}
                            FCFA</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Date de début prévue</span>
                        <span>{{ $activite->date_debut_prevue ? \Carbon\Carbon::parse($activite->date_debut_prevue)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Date de fin prévue</span>
                        <span>{{ $activite->date_fin_prevue ? \Carbon\Carbon::parse($activite->date_fin_prevue)->format('d/m/Y') : '-' }}</span>
                    </div>
                    @if ($activite->date_debut_prevue && $activite->date_fin_prevue)
                        <div class="py-2 flex justify-between">
                            <span class="text-gray-500">Durée prévue</span>
                            <span>
                                @php
                                    $debut = \Carbon\Carbon::parse($activite->date_debut_prevue);
                                    $fin = \Carbon\Carbon::parse($activite->date_fin_prevue);
                                    $duree = $debut->diffInDays($fin);
                                @endphp
                                {{ $duree }} jours
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Extrant associé --}}
            <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Extrant associé</h3>
                </div>
                <div class="divide-y divide-gray-200 p-4 dark:divide-gray-700">
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Code</span>
                        <span class="font-mono">{{ $activite->extrant->code ?? '-' }}</span>
                    </div>
                    <div class="py-2">
                        <span class="text-gray-500">Libellé</span>
                        <p class="mt-1">{{ $activite->extrant->libelle ?? '-' }}</p>
                    </div>
                    @if ($activite->extrant)
                        <div class="py-2">
                            <a href="{{ route('extrants.show', $activite->extrant) }}"
                                class="text-indigo-600 hover:underline text-sm">
                                Voir l'extrant →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Métadonnées --}}
            <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Métadonnées</h3>
                </div>
                <div class="divide-y divide-gray-200 p-4 dark:divide-gray-700">
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Créé le</span>
                        <span>{{ $activite->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Dernière modification</span>
                        <span>{{ $activite->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
