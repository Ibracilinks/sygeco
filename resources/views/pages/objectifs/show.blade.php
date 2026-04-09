<x-layouts::app title="Détails Objectif Stratégique">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">{{ Str::limit($objectif->libelle, 100) }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400">Code: {{ $objectif->code }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('objectifs.edit', $objectif) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                    Modifier
                </a>
                <a href="{{ route('objectifs.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Informations générales</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-zinc-500">Libellé complet</div>
                        <div class="dark:text-white">{{ $objectif->libelle }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Description</div>
                        <div class="dark:text-white">{{ $objectif->description ?? 'Aucune description' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Ordre</div>
                        <div class="dark:text-white">{{ $objectif->ordre }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Statut</div>
                        <div>
                            @if ($objectif->is_active)
                                <span class="text-green-600">✓ Actif</span>
                            @else
                                <span class="text-red-600">✗ Inactif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($objectif->resultatsStrategiques->count() > 0)
                <div
                    class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                    <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                        <h2 class="text-lg font-semibold dark:text-white">Résultats Stratégiques associés
                            ({{ $objectif->resultatsStrategiques->count() }})</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4">
                            @foreach ($objectif->resultatsStrategiques as $resultat)
                                <a href="{{ route('resultats.show', $resultat) }}"
                                    class="block p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-zinc-700 transition">
                                    <div class="font-medium dark:text-white">{{ $resultat->code }} -
                                        {{ $resultat->libelle }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
