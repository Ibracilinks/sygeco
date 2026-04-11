<x-layouts::app title="Détails Département">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">{{ $departement->nom }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400">Code: {{ $departement->code }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('departements.edit', $departement) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                    Modifier
                </a>
                <a href="{{ route('departements.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Informations générales</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-zinc-500">Description</div>
                        <div class="dark:text-white">{{ $departement->description ?? 'Aucune description' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Ordre</div>
                        <div class="dark:text-white">{{ $departement->ordre ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Statut</div>
                        <div>
                            @if ($departement->is_active)
                                <span class="text-green-600">✓ Actif</span>
                            @else
                                <span class="text-red-600">✗ Inactif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Contact</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-zinc-500">Responsable</div>
                        <div class="dark:text-white">{{ $departement->responsable_nom ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Email</div>
                        <div class="dark:text-white">{{ $departement->responsable_email ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Téléphone</div>
                        <div class="dark:text-white">{{ $departement->telephone ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Utilisateurs</div>
                        <div class="dark:text-white">{{ $departement->users->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if ($departement->users->count() > 0)
            <div
                class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Utilisateurs associés
                        ({{ $departement->users->count() }})</h2>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-zinc-900">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Nom</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Email</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach ($departement->users as $user)
                                <tr>
                                    <td class="px-6 py-4 dark:text-white">
                                        {{ $user->name ?? ($user->nom ?? 'Utilisateur') }}</td>
                                    <td class="px-6 py-4 dark:text-white">{{ $user->email }}</td>
                                    <td class="px-6 py-4 dark:text-white">
                                        {{ $user->roles?->pluck('name')->join(', ') ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
