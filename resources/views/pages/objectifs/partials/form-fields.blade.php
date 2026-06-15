<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Exercice *</label>
        @if ($exercices->isEmpty())
            <p class="text-sm text-amber-700 dark:text-amber-300">Aucun exercice disponible. Créez d'abord un exercice.</p>
            @can('manage_exercices')
                <a href="{{ route('exercices.create') }}" class="mt-2 inline-block text-sm text-sky-600 hover:underline">Créer un exercice</a>
            @endcan
        @else
            <select name="exercice_id" required
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                @foreach ($exercices as $exercice)
                    <option value="{{ $exercice->id }}" @selected((string) old('exercice_id', $objectif->exercice_id ?? $defaultExerciceId ?? '') === (string) $exercice->id)>
                        {{ $exercice->annee }} ({{ $exercice->statut }})
                    </option>
                @endforeach
            </select>
        @endif
        @error('exercice_id')
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
