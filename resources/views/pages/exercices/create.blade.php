<x-layouts::app title="Créer un exercice">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl max-w-2xl">
        <h1 class="text-2xl font-bold dark:text-white">Nouvel exercice</h1>

        <div
            class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
            <form action="{{ route('exercices.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium dark:text-white mb-1">Année *</label>
                    <input type="number" name="annee" value="{{ old('annee', date('Y')) }}"
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white">
                    @error('annee')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-1">Date début *</label>
                        <input type="date" name="date_debut" value="{{ old('date_debut') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white">
                        @error('date_debut')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-1">Date fin *</label>
                        <input type="date" name="date_fin" value="{{ old('date_fin') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white">
                        @error('date_fin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-1">Ouverture de la saisie</label>
                        <input type="date" name="date_ouverture_saisie" value="{{ old('date_ouverture_saisie') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white">
                        <p class="mt-1 text-xs text-neutral-500">Date d'ouverture de la saisie des activités.</p>
                        @error('date_ouverture_saisie')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-1">Date limite de saisie</label>
                        <input type="date" name="date_limite_saisie" value="{{ old('date_limite_saisie') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white">
                        <p class="mt-1 text-xs text-neutral-500">Déclenche les relances J-15, J-10, J-7, J-5, J-3, J-2, J-1, J.</p>
                        @error('date_limite_saisie')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-white mb-1">Statut *</label>
                    <select name="statut"
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white">
                        <option value="brouillon" @selected(old('statut') === 'brouillon')>Brouillon</option>
                        <option value="actif" @selected(old('statut') === 'actif')>Actif</option>
                        <option value="cloture" @selected(old('statut') === 'cloture')>Clôturé</option>
                    </select>
                    @error('statut')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('exercices.index') }}"
                        class="px-4 py-2 rounded-lg bg-zinc-500 text-white hover:bg-zinc-600">Annuler</a>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
