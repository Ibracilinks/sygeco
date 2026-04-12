<x-layouts::app title="Modifier un Département">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Modifier le département</h1>
            <p class="text-zinc-500 dark:text-zinc-400">{{ $departement->nom }}</p>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
            <form action="{{ route('departements.update', $departement) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Code *</label>
                        <input type="text" name="code" value="{{ old('code', $departement->code) }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="ex: DEP_001">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Nom *</label>
                        <input type="text" name="nom" value="{{ old('nom', $departement->nom) }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Nom du département">
                        @error('nom')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Description</label>
                        <textarea name="description" rows="4"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Description du département">{{ old('description', $departement->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Responsable</label>
                        <select name="responsable_id"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Aucun</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ (string) old('responsable_id', $departement->responsable_id) === (string) $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} — {{ $user->email }}</option>
                            @endforeach
                        </select>
                        @error('responsable_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Ordre</label>
                        <input type="number" name="ordre" value="{{ old('ordre', $departement->ordre) }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="1">
                        @error('ordre')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $departement->is_active) ? 'checked' : '' }}
                                class="rounded border-neutral-300 dark:border-neutral-600 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 dark:text-white">Actif</span>
                        </label>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <a href="{{ route('departements.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
