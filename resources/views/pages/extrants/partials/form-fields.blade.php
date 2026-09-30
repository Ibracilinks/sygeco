<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="resultat_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Résultat *</label>
        <select id="resultat_id" name="resultat_id"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Sélectionnez un résultat</option>
            @foreach ($resultats as $resultat)
                <option value="{{ $resultat->id }}" @selected((string) old('resultat_id', $extrant->resultat_id ?? $selectedResultat ?? '') === (string) $resultat->id)>
                    {{ $resultat->code }} — {{ Str::limit($resultat->libelle, 50) }}@if ($resultat->objectif) (Objectif {{ $resultat->objectif->code }})@endif
                </option>
            @endforeach
        </select>
        @error('resultat_id')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">L'objectif de rattachement est déterminé automatiquement par le résultat choisi.</p>
    </div>

    <div>
        <label for="code" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Code *</label>
        <input id="code" type="text" name="code" value="{{ old('code', $extrant->code ?? '') }}" placeholder="ex: EXT_001"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('code')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="ordre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Ordre</label>
        <input id="ordre" type="number" name="ordre" value="{{ old('ordre', $extrant->ordre ?? 0) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('ordre')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <p class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Statut</p>
        <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-100">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $extrant->is_active ?? true))
                class="rounded border-slate-300 text-slate-800 focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            Actif
        </label>
        @error('is_active')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="libelle" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Libelle *</label>
        <textarea id="libelle" name="libelle" rows="3"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">{{ old('libelle', $extrant->libelle ?? '') }}</textarea>
        @error('libelle')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Description (optionnelle)</label>
        <textarea id="description" name="description" rows="3"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">{{ old('description', $extrant->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>
</div>
