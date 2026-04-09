<x-layouts::app title="Détails Résultat Stratégique">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">{{ $resultat->libelle }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400">Code: {{ $resultat->code }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('resultats.edit', $resultat) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                    Modifier
                </a>
                <a href="{{ route('resultats.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Informations</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-zinc-500">Objectif Stratégique</div>
                        <div class="font-medium dark:text-white">
                            <a href="{{ route('objectifs.show', $resultat->objectifStrategique) }}"
                                class="text-blue-600 hover:underline">
                                {{ $resultat->objectifStrategique->code }} -
                                {{ Str::limit($resultat->objectifStrategique->libelle, 100) }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Description</div>
                        <div class="dark:text-white">{{ $resultat->description ?? 'Aucune description' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Ordre</div>
                        <div class="dark:text-white">{{ $resultat->ordre }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Statut</div>
                        <div>
                            @if ($resultat->is_active)
                                <span class="text-green-600">✓ Actif</span>
                            @else
                                <span class="text-red-600">✗ Inactif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($resultat->extrants->count() > 0)
                <div
                    class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                    <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                        <h2 class="text-lg font-semibold dark:text-white">Extrants associés
                            ({{ $resultat->extrants->count() }})</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4">
                            @foreach ($resultat->extrants as $extrant)
                                <a href="{{ route('extrants.show', $extrant) }}"
                                    class="block p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-zinc-700 transition">
                                    <div class="font-medium dark:text-white">{{ $extrant->code }} -
                                        {{ $extrant->libelle }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
