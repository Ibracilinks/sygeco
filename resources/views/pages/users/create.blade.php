<x-layouts::app title="Créer un utilisateur">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Créer un nouvel utilisateur</h1>
            <p class="text-zinc-500 dark:text-zinc-400">Ajoutez un utilisateur et ses informations de connexion</p>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
            <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Nom *</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Nom complet">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="email@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Mot de passe *</label>
                        <input type="password" name="password"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Mot de passe">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Confirmation du mot de passe
                            *</label>
                        <input type="password" name="password_confirmation"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Confirmez le mot de passe">
                    </div>

                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Département</label>
                        <select name="departement_id"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Aucun</option>
                            @foreach ($departements as $departement)
                                <option value="{{ $departement->id }}"
                                    {{ old('departement_id') == $departement->id ? 'selected' : '' }}>
                                    {{ $departement->nom }}</option>
                            @endforeach
                        </select>
                        @error('departement_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Poste</label>
                        <input type="text" name="poste" value="{{ old('poste') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Poste">
                        @error('poste')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Téléphone</label>
                        <input type="text" name="telephone" value="{{ old('telephone') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="+225 XX XX XX XX">
                        @error('telephone')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium dark:text-white">Rôles *</label>
                            <span class="text-xs text-zinc-500">Sélectionnez au moins un rôle</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($roles as $role)
                                <label
                                    class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-neutral-700 dark:text-white">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                        {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                                    <span>{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('roles')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <a href="{{ route('users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Créer l'utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
