<x-layouts::app title="Créer un Indicateur">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Créer un indicateur</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Définissez un nouvel indicateur de performance ou de gestion
            </p>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
            <form action="{{ route('indicateurs.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Objectif -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Objectif *</label>
                        <select name="objectif_id"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Sélectionnez un objectif</option>
                            @foreach ($objectifs as $objectif)
                                <option value="{{ $objectif->id }}"
                                    {{ old('objectif_id', $selectedObjectif) == $objectif->id ? 'selected' : '' }}>
                                    {{ $objectif->code }} - {{ $objectif->annee }} -
                                    {{ Str::limit($objectif->libelle, 50) }}
                                </option>
                            @endforeach
                        </select>
                        @error('objectif_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Code *</label>
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="ex: IND_PERF_001">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Type *</label>
                        <select name="type"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="performance" {{ old('type') == 'performance' ? 'selected' : '' }}>📊
                                Performance</option>
                            <option value="gestion" {{ old('type') == 'gestion' ? 'selected' : '' }}>📈 Gestion
                            </option>
                            <option value="qualite" {{ old('type') == 'qualite' ? 'selected' : '' }}>⭐ Qualité</option>
                            <option value="efficacite" {{ old('type') == 'efficacite' ? 'selected' : '' }}>⚡ Efficacité
                            </option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Périodicité -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Périodicité *</label>
                        <select name="periodicite"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="mensuel" {{ old('periodicite') == 'mensuel' ? 'selected' : '' }}>Mensuel
                            </option>
                            <option value="trimestriel" {{ old('periodicite') == 'trimestriel' ? 'selected' : '' }}>
                                Trimestriel</option>
                            <option value="semestriel" {{ old('periodicite') == 'semestriel' ? 'selected' : '' }}>
                                Semestriel</option>
                            <option value="annuel" {{ old('periodicite') == 'annuel' ? 'selected' : '' }}>Annuel
                            </option>
                        </select>
                        @error('periodicite')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Unité -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Unité</label>
                        <input type="text" name="unite" value="{{ old('unite') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="%, FCFA, Nombre, Jours">
                        @error('unite')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sens -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Sens de progression</label>
                        <select name="sens"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="hausse" {{ old('sens') == 'hausse' ? 'selected' : '' }}>📈 Hausse (plus
                                c'est haut, mieux c'est)</option>
                            <option value="baisse" {{ old('sens') == 'baisse' ? 'selected' : '' }}>📉 Baisse (plus
                                c'est bas, mieux c'est)</option>
                        </select>
                        @error('sens')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cible -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Valeur cible</label>
                        <input type="number" name="cible" value="{{ old('cible') }}" step="0.01"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Valeur à atteindre">
                        @error('cible')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Seuil d'alerte -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Seuil d'alerte</label>
                        <input type="number" name="seuil_alerte" value="{{ old('seuil_alerte') }}" step="0.01"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Valeur déclenchant une alerte">
                        @error('seuil_alerte')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ordre -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Ordre</label>
                        <input type="number" name="ordre" value="{{ old('ordre', 0) }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('ordre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Statut actif -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Statut</label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="rounded border-neutral-300 dark:border-neutral-600 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 dark:text-white">Actif</span>
                        </label>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Libellé -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Libellé *</label>
                        <textarea name="libelle" rows="3"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Description de l'indicateur">{{ old('libelle') }}</textarea>
                        @error('libelle')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Description détaillée</label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Description détaillée de l'indicateur">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Formule de calcul -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Formule de calcul</label>
                        <input type="text" name="formule_calcul" value="{{ old('formule_calcul') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: (Valeur atteinte / Cible) * 100">
                        @error('formule_calcul')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Source des données -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Source des données</label>
                        <input type="text" name="source_donnee" value="{{ old('source_donnee') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: Activ'Premium, Saisie terrain, SimBudget">
                        @error('source_donnee')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <a href="{{ route('indicateurs.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Créer l'indicateur
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
