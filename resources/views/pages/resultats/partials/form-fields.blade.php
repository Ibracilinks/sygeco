@php
    /** @var \App\Models\Resultat|null $resultat */
    $resultat = $resultat ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Objectif *</label>
        <select name="objectif_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Sélectionnez un objectif</option>
            @foreach ($objectifs as $objectif)
                <option value="{{ $objectif->id }}" @selected((string) old('objectif_id', $resultat?->objectif_id ?? $selectedObjectif ?? '') === (string) $objectif->id)>
                    {{ $objectif->code }} - {{ $objectif->annee }} - {{ Str::limit($objectif->libelle, 60) }}
                </option>
            @endforeach
        </select>
        @error('objectif_id')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Code *</label>
        <input type="text" name="code" value="{{ old('code', $resultat?->code) }}" placeholder="RS_001"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @error('code')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Ordre</label>
        <input type="number" min="0" name="ordre" value="{{ old('ordre', $resultat?->ordre ?? 0) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @error('ordre')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Libellé *</label>
        <textarea name="libelle" rows="3" placeholder="Description du résultat"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('libelle', $resultat?->libelle) }}</textarea>
        @error('libelle')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Description</label>
        <textarea name="description" rows="3" placeholder="Description détaillée"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('description', $resultat?->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $resultat?->is_active ?? true))
                class="h-4 w-4 rounded border-slate-300 text-slate-700 focus:ring-slate-500 dark:border-slate-700">
            <span>Résultat actif</span>
        </label>
        @error('is_active')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
