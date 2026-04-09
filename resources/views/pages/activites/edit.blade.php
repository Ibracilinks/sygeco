<x-layouts::app :title="__('Modifier activité : ' . $activite->code)">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Modifier l'activité</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $activite->code }} - {{ $activite->libelle }}
                </p>
            </div>
            <a href="{{ route('activites.show', $activite) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
        </div>

        <form action="{{ route('activites.update', $activite) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Extrant associé --}}
            <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Extrant associé</h3>
                </div>
                <div class="p-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                            Extrant <span class="text-red-600">*</span>
                        </label>
                        <select name="extrant_id"
                            class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                            <option value="">Sélectionner un extrant</option>
                            @foreach ($extrants as $extrant)
                                <option value="{{ $extrant->id }}"
                                    {{ old('extrant_id', $activite->extrant_id) == $extrant->id ? 'selected' : '' }}>
                                    {{ $extrant->code }} - {{ Str::limit($extrant->libelle, 50) }}
                                </option>
                            @endforeach
                        </select>
                        @error('extrant_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Informations générales --}}
            <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informations générales</h3>
                </div>
                <div class="p-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Code <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="code" value="{{ old('code', $activite->code) }}"
                                class="w-full rounded-lg border border-gray-300 p-2.5 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            @error('code')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Ordre
                            </label>
                            <input type="number" name="ordre" value="{{ old('ordre', $activite->ordre) }}"
                                class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('ordre')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Libellé <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="libelle" value="{{ old('libelle', $activite->libelle) }}"
                                class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            @error('libelle')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Description
                            </label>
                            <textarea name="description" rows="3"
                                class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $activite->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Budget et planning --}}
            <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Budget et planning</h3>
                </div>
                <div class="p-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Budget prévisionnel (FCFA) <span class="text-red-600">*</span>
                            </label>
                            <input type="number" name="budget_previsionnel_global"
                                value="{{ old('budget_previsionnel_global', $activite->budget_previsionnel_global) }}"
                                class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            @error('budget_previsionnel_global')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Statut
                            </label>
                            <select name="is_active"
                                class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="1"
                                    {{ old('is_active', $activite->is_active) == '1' ? 'selected' : '' }}>Actif
                                </option>
                                <option value="0"
                                    {{ old('is_active', $activite->is_active) == '0' ? 'selected' : '' }}>Inactif
                                </option>
                            </select>
                            @error('is_active')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Date de début prévue
                            </label>
                            <input type="date" name="date_debut_prevue"
                                value="{{ old('date_debut_prevue', $activite->date_debut_prevue ? \Carbon\Carbon::parse($activite->date_debut_prevue)->format('Y-m-d') : '') }}"
                                class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('date_debut_prevue')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Date de fin prévue
                            </label>
                            <input type="date" name="date_fin_prevue"
                                value="{{ old('date_fin_prevue', $activite->date_fin_prevue ? \Carbon\Carbon::parse($activite->date_fin_prevue)->format('Y-m-d') : '') }}"
                                class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('date_fin_prevue')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('activites.show', $activite) }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
