<x-layouts::app :title="__('Comparaison budgétaire')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Comparaison budgétaire</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Comparer les budgets entre différents objectifs stratégiques
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('analyse-budgetaire.dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        {{-- Formulaire de sélection --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
            <form method="GET" action="{{ route('analyse-budgetaire.comparaison') }}" class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                        Type de comparaison
                    </label>
                    <select name="type"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="objectif" {{ request('type', 'objectif') == 'objectif' ? 'selected' : '' }}>Par
                            objectif stratégique</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                        Sélectionner les objectifs à comparer
                    </label>
                    <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($objectifs as $objectif)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="objectifs[]" value="{{ $objectif->id }}"
                                    {{ in_array($objectif->id, $selectedObjectifs) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $objectif->code }} -
                                    {{ Str::limit($objectif->libelle, 50) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Comparer
                    </button>
                </div>
            </form>
        </div>

        {{-- Résultats de la comparaison --}}
        @if (!empty($comparaisonData))
            <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b p-4">
                    <h3 class="text-lg font-semibold">Résultats de la comparaison</h3>
                </div>
                <div class="p-6">
                    @php
                        $maxBudget = max(array_column($comparaisonData, 'budget'));
                    @endphp

                    @foreach ($comparaisonData as $item)
                        <div class="mb-4">
                            <div class="flex justify-between mb-1">
                                <div>
                                    <span class="font-medium">{{ $item['code'] }}</span>
                                    <span
                                        class="text-sm text-gray-500 ml-2">{{ Str::limit($item['libelle'], 60) }}</span>
                                </div>
                                <span
                                    class="font-bold text-indigo-600">{{ number_format($item['budget'], 0, ',', ' ') }}
                                    FCFA</span>
                            </div>
                            <div class="h-2 w-full bg-gray-200 rounded-full">
                                @php $pourcentage = $maxBudget > 0 ? ($item['budget'] / $maxBudget) * 100 : 0; @endphp
                                <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $pourcentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif(request()->has('objectifs') && empty($comparaisonData))
            <div
                class="rounded-xl border border-neutral-200 bg-white p-6 text-center dark:border-neutral-700 dark:bg-neutral-900">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-gray-500">Aucune donnée à comparer</p>
            </div>
        @endif
    </div>
</x-layouts::app>
