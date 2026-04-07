<x-layouts::app title="Détails Structure">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">{{ $structure->libelle }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400">Code: {{ $structure->code }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('structures.edit', $structure) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                    Modifier
                </a>
                <a href="{{ route('structures.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Informations générales</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-zinc-500">Type</div>
                        <div class="font-medium dark:text-white">
                            @if ($structure->type == 'direction')
                                Direction
                            @elseif($structure->type == 'departement')
                                Département
                            @elseif($structure->type == 'bureau_regional')
                                Bureau Régional
                            @else
                                {{ $structure->type }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Structure Parente</div>
                        <div class="dark:text-white">
                            @if ($structure->parent)
                                <a href="{{ route('structures.show', $structure->parent) }}"
                                    class="text-blue-600 hover:underline">
                                    {{ $structure->parent->libelle }}
                                </a>
                            @else
                                <span class="text-zinc-500">Aucune (structure racine)</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Statut</div>
                        <div>
                            @if ($structure->is_active)
                                <span class="text-green-600">✓ Actif</span>
                            @else
                                <span class="text-red-600">✗ Inactif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Contact</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-zinc-500">Responsable</div>
                        <div class="font-medium dark:text-white">{{ $structure->responsable_nom ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Email</div>
                        <div class="dark:text-white">{{ $structure->responsable_email ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Téléphone</div>
                        <div class="dark:text-white">{{ $structure->telephone ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Adresse</div>
                        <div class="dark:text-white whitespace-pre-wrap">{{ $structure->adresse ?? 'Non définie' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($structure->enfants->count() > 0)
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Sous-structures
                        ({{ $structure->enfants->count() }})</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($structure->enfants as $enfant)
                            <a href="{{ route('structures.show', $enfant) }}"
                                class="block p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-zinc-700 transition">
                                <div class="font-medium dark:text-white">{{ $enfant->libelle }}</div>
                                <div class="text-sm text-zinc-500 mt-1">
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-800">
                                        @if ($enfant->type == 'direction')
                                            Direction
                                        @elseif($enfant->type == 'departement')
                                            Département
                                        @elseif($enfant->type == 'bureau_regional')
                                            Bureau Régional
                                        @else
                                            {{ $enfant->type }}
                                        @endif
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
