@php
    /** @var \App\Models\User|null $user */
    $user = $user ?? null;
    $isEdit = $isEdit ?? false;
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Nom *</label>
        <input
            type="text"
            name="name"
            value="{{ old('name', $user?->name) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="Nom complet"
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Email *</label>
        <input
            type="email"
            name="email"
            value="{{ old('email', $user?->email) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="email@canam.ci"
        >
        @error('email')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
            {{ $isEdit ? 'Nouveau mot de passe' : 'Mot de passe *' }}
        </label>
        <input
            type="password"
            name="password"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="{{ $isEdit ? 'Laisser vide pour conserver' : 'Mot de passe sécurisé' }}"
        >
        @error('password')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
            {{ $isEdit ? 'Confirmation mot de passe' : 'Confirmation mot de passe *' }}
        </label>
        <input
            type="password"
            name="password_confirmation"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="Confirme le mot de passe"
        >
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Département</label>
        <select
            name="departement_id"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
        >
            <option value="">Aucun</option>
            @foreach ($departements as $departement)
                <option value="{{ $departement->id }}" @selected((string) old('departement_id', $user?->departement_id) === (string) $departement->id)>
                    {{ $departement->nom }}
                </option>
            @endforeach
        </select>
        @error('departement_id')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Poste</label>
        <input
            type="text"
            name="poste"
            value="{{ old('poste', $user?->poste) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="Fonction de l'utilisateur"
        >
        @error('poste')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Téléphone</label>
        <input
            type="text"
            name="telephone"
            value="{{ old('telephone', $user?->telephone) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="+225 XX XX XX XX"
        >
        @error('telephone')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <div class="mb-2 flex items-center justify-between">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Rôles *</label>
            <span class="text-xs text-slate-500 dark:text-slate-400">Sélectionner au moins un rôle</span>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($roles as $role)
                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    <input
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->id }}"
                        @checked(in_array((int) $role->id, array_map('intval', old('roles', $user?->roles?->pluck('id')->toArray() ?? [])), true))
                        class="h-4 w-4 rounded border-slate-300 text-slate-700 focus:ring-slate-500 dark:border-slate-700"
                    >
                    <span>{{ $role->name }}</span>
                </label>
            @endforeach
        </div>
        @error('roles')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
