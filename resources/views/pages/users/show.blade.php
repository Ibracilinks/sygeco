<x-layouts::app title="Détails utilisateur">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">{{ $user->name }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('users.edit', $user) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                    Modifier
                </a>
                <a href="{{ route('users.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Informations</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-zinc-500">Département</div>
                        <div class="dark:text-white">
                            @if ($user->departement)
                                <a href="{{ route('departements.show', $user->departement) }}"
                                    class="text-blue-600 hover:underline">{{ $user->departement->nom }}</a>
                            @else
                                <span class="text-zinc-500">Aucun</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Poste</div>
                        <div class="dark:text-white">{{ $user->poste ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Téléphone</div>
                        <div class="dark:text-white">{{ $user->telephone ?? 'Non défini' }}</div>
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Rôles</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if ($user->roles->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->roles as $role)
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-zinc-500 dark:text-zinc-400">Aucun rôle attribué</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
