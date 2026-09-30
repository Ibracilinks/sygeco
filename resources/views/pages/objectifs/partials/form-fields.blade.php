<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Exercices couverts *</label>
        <p class="mb-2 text-xs text-slate-500 dark:text-slate-400">
            Cochez chaque exercice couvert par l'objectif. Un objectif de plan stratégique
            en couvre plusieurs (ex. 2026 à 2030) ; un objectif annuel n'en couvre qu'un.
        </p>
        @if ($exercices->isEmpty())
            <p class="text-sm text-amber-700 dark:text-amber-300">Aucun exercice disponible. Créez d'abord un exercice.</p>
            @can('manage_exercices')
                <a href="{{ route('exercices.create') }}" class="mt-2 inline-block text-sm text-sky-600 hover:underline">Créer un exercice</a>
            @endcan
        @else
            @php($coches = collect(old('exercice_ids', $selectedExerciceIds ?? []))->map(fn ($id) => (string) $id))
            <div class="flex flex-wrap gap-2 rounded-lg border border-slate-300 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                @foreach ($exercices as $exercice)
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-700 transition hover:bg-slate-50 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 dark:has-[:checked]:bg-sky-950/40">
                        <input type="checkbox" name="exercice_ids[]" value="{{ $exercice->id }}"
                            @checked($coches->contains((string) $exercice->id))
                            class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-800">
                        <span>{{ $exercice->annee }}</span>
                        <span class="text-xs text-slate-400">({{ $exercice->statut }})</span>
                    </label>
                @endforeach
            </div>
        @endif
        @error('exercice_ids')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        @error('exercice_ids.*')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Code *</label>
        <input type="text" name="code" value="{{ old('code', $objectif->code ?? '') }}" placeholder="ex: OS_001"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @error('code')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Ordre</label>
        <input type="number" min="0" name="ordre" value="{{ old('ordre', $objectif->ordre ?? 0) }}" placeholder="Ordre d'affichage"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @error('ordre')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Libellé *</label>
        <textarea name="libelle" rows="4" placeholder="Description de l'objectif stratégique"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('libelle', $objectif->libelle ?? '') }}</textarea>
        @error('libelle')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Statut *</label>
        <select name="statut"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="actif" @selected(old('statut', $objectif->statut ?? 'actif') === 'actif')>Actif</option>
            <option value="inactif" @selected(old('statut', $objectif->statut ?? 'actif') === 'inactif')>Inactif</option>
        </select>
        @error('statut')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Description</label>
        <textarea name="description" rows="3" placeholder="Description détaillée"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('description', $objectif->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
