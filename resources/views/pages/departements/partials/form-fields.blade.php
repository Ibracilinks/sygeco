@php
    /** @var \App\Models\Departement|null $departement */
    $departement = $departement ?? null;
    $parents = $parents ?? collect();
    $typeCourant = old('type', $departement?->type ?? \App\Models\Departement::TYPE_DEPARTEMENT);
    $parentCourant = old('parent_id', $departement?->parent_id);

    // Types de parent autorisés par niveau, partagés avec Alpine pour filtrer la liste.
    $parentsAutorises = [
        \App\Models\Departement::TYPE_DEPARTEMENT => [\App\Models\Departement::TYPE_DIRECTION],
        \App\Models\Departement::TYPE_AGENCE_COMPTABLE => [\App\Models\Departement::TYPE_DIRECTION],
        \App\Models\Departement::TYPE_BUREAU_REGIONAL => [\App\Models\Departement::TYPE_DIRECTION],
        \App\Models\Departement::TYPE_SERVICE => [\App\Models\Departement::TYPE_DEPARTEMENT, \App\Models\Departement::TYPE_DIRECTION],
    ];

    // La Direction Générale est unique : on ne propose son niveau que si elle n'existe
    // pas encore (ou si c'est justement l'entité en cours d'édition).
    $dgDejaCreee = \App\Models\Departement::query()
        ->where('type', \App\Models\Departement::TYPE_DIRECTION)
        ->when($departement, fn ($query) => $query->whereKeyNot($departement->id))
        ->exists();

    $aidesRattachement = [
        \App\Models\Departement::TYPE_SERVICE => '(direction centrale ou Direction Générale)',
    ];
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Code *</label>
        <input
            type="text"
            name="code"
            value="{{ old('code', $departement?->code) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="ex: DEP_001"
        >
        @error('code')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Nom *</label>
        <input
            type="text"
            name="nom"
            value="{{ old('nom', $departement?->nom) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="Nom de l'entité"
        >
        @error('nom')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2" x-data="{
        type: '{{ $typeCourant }}',
        parentsAutorises: {{ \Illuminate\Support\Js::from($parentsAutorises) }},
        aides: {{ \Illuminate\Support\Js::from($aidesRattachement) }},
        accepte(typeParent) { return (this.parentsAutorises[this.type] ?? []).includes(typeParent) },
    }">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Type d'entité *</label>
                <select
                    name="type"
                    x-model="type"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                >
                    @foreach (\App\Models\Departement::TYPE_LABELS as $valeur => $libelle)
                        @continue($valeur === \App\Models\Departement::TYPE_DIRECTION && $dgDejaCreee)
                        <option value="{{ $valeur }}" @selected($typeCourant === $valeur)>{{ $libelle }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="type !== '{{ \App\Models\Departement::TYPE_DIRECTION }}'" x-cloak>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                    Rattaché à
                    <span x-text="aides[type] ?? '(Direction Générale)'"
                          class="text-slate-400"></span>
                    *
                </label>
                <select
                    name="parent_id"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                >
                    <option value="">— Sélectionner —</option>
                    @foreach ($parents as $parent)
                        <option
                            value="{{ $parent->id }}"
                            x-show="accepte('{{ $parent->type }}')"
                            @selected((string) $parentCourant === (string) $parent->id)
                        >
                            {{ $parent->nom }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Description</label>
        <textarea
            name="description"
            rows="4"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="Description fonctionnelle de l'entité"
        >{{ old('description', $departement?->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Responsable</label>
        <select
            name="responsable_id"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
        >
            <option value="">Aucun responsable</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('responsable_id', $departement?->responsable_id) === (string) $user->id)>
                    {{ $user->name }} - {{ $user->email }}
                </option>
            @endforeach
        </select>
        @error('responsable_id')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Ordre d'affichage</label>
        <input
            type="number"
            min="1"
            name="ordre"
            value="{{ old('ordre', $departement?->ordre) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="1"
        >
        @error('ordre')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-end">
        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $departement?->is_active ?? true))
                class="rounded border-slate-300 text-slate-700 focus:ring-slate-500 dark:border-slate-700"
            >
            Entité active
        </label>
    </div>
</div>
