<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="extrant_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Extrant *</label>
        <select id="extrant_id" name="extrant_id"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Sélectionnez un extrant</option>
            @foreach ($extrants as $extrant)
                <option value="{{ $extrant->id }}" @selected((string) old('extrant_id', $activite->extrant_id ?? $selectedExtrant ?? '') === (string) $extrant->id)>
                    {{ $extrant->code }} - {{ Str::limit($extrant->libelle, 60) }}
                </option>
            @endforeach
        </select>
        @error('extrant_id')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="departement_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Structure *</label>
        @if (auth()->user()->hasRole('chef') && auth()->user()->departement_id)
            <input type="hidden" name="departement_id" value="{{ auth()->user()->departement_id }}">
            <div class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                {{ auth()->user()->departement->nom ?? 'Votre structure' }}
            </div>
        @else
            <select id="departement_id" name="departement_id"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Sélectionnez une structure</option>
                @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}" @selected((string) old('departement_id', $activite->departement_id ?? $departementId ?? '') === (string) $departement->id)>
                        {{ $departement->nom }}
                    </option>
                @endforeach
            </select>
        @endif
        @error('departement_id')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="nom_activite" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nom de l'activité *</label>
        <textarea id="nom_activite" name="nom_activite" rows="3"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">{{ old('nom_activite', $activite->nom_activite ?? '') }}</textarea>
        @error('nom_activite')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="indicateur_objectivement_verifiable" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Indicateur objectivement vérifiable *</label>
        <input id="indicateur_objectivement_verifiable" type="text" name="indicateur_objectivement_verifiable" value="{{ old('indicateur_objectivement_verifiable', $activite->indicateur_objectivement_verifiable ?? '') }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('indicateur_objectivement_verifiable')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="moyen_verification" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Moyen de vérification *</label>
        <input id="moyen_verification" type="text" name="moyen_verification" value="{{ old('moyen_verification', $activite->moyen_verification ?? '') }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('moyen_verification')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="cout" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Coût (FCFA) *</label>
        <input id="cout" type="number" name="cout" value="{{ old('cout', $activite->cout ?? '') }}" step="0.01"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('cout')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <p class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Chronogramme</p>
        <div class="grid grid-cols-2 gap-2 text-sm text-slate-700 dark:text-slate-200">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="trimestre_1" value="on" @checked(old('trimestre_1', isset($activite) ? $activite->trimestre_1 === 'oui' : false)) class="rounded border-slate-300 text-slate-800 dark:border-slate-700">T1</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="trimestre_2" value="on" @checked(old('trimestre_2', isset($activite) ? $activite->trimestre_2 === 'oui' : false)) class="rounded border-slate-300 text-slate-800 dark:border-slate-700">T2</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="trimestre_3" value="on" @checked(old('trimestre_3', isset($activite) ? $activite->trimestre_3 === 'oui' : false)) class="rounded border-slate-300 text-slate-800 dark:border-slate-700">T3</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="trimestre_4" value="on" @checked(old('trimestre_4', isset($activite) ? $activite->trimestre_4 === 'oui' : false)) class="rounded border-slate-300 text-slate-800 dark:border-slate-700">T4</label>
        </div>
    </div>

    <div class="md:col-span-2">
        <label for="commentaires" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Commentaires</label>
        <textarea id="commentaires" name="commentaires" rows="2"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">{{ old('commentaires', $activite->commentaires ?? '') }}</textarea>
        @error('commentaires')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>
</div>
