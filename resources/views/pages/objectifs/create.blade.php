<x-layouts::app title="Créer un Objectif Stratégique">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Créer un objectif stratégique</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Ajoutez un nouvel objectif stratégique pour la DBCGOQ</p>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
            <form action="{{ route('objectifs.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Exercice -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Exercice *</label>
                        @if ($exercices->isEmpty())
                            <p class="text-sm text-amber-700 dark:text-amber-300">Aucun exercice en base. Créez d’abord un
                                exercice.</p>
                            @can('manage_exercices')
                                <a href="{{ route('exercices.create') }}"
                                    class="mt-2 inline-block text-sm text-blue-600 hover:underline">Créer un exercice</a>
                            @endcan
                        @else
                            <select name="exercice_id" required
                                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($exercices as $ex)
                                    <option value="{{ $ex->id }}"
                                        {{ (string) old('exercice_id', $defaultExerciceId) === (string) $ex->id ? 'selected' : '' }}>
                                        {{ $ex->annee }} ({{ $ex->statut }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('exercice_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-zinc-500">L'année de l'objectif est celle de l'exercice choisi.</p>
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Code *</label>
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="ex: OS_001">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ordre -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Ordre</label>
                        <input type="number" name="ordre" value="{{ old('ordre', 0) }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ordre d'affichage">
                        @error('ordre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Statut -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Statut</label>
                        <select name="statut"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="actif" {{ old('statut', 'actif') == 'actif' ? 'selected' : '' }}>Actif
                            </option>
                            <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </select>
                        @error('statut')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Libellé -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Libellé *</label>
                        <textarea name="libelle" rows="4"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Description de l'objectif stratégique">{{ old('libelle') }}</textarea>
                        @error('libelle')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Description (optionnelle)</label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Description détaillée">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <a href="{{ route('objectifs.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Créer l'objectif
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
