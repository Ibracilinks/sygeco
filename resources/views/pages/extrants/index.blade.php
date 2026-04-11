<x-layouts::app title="Extrants">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">Extrants</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-1">Gestion des extrants par objectif stratégique</p>
            </div>
            @can('create_extrants')
                <a href="{{ route('extrants.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nouvel Extrant
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 p-4">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 p-4">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Filtres -->
        <div class="flex flex-wrap gap-4 items-center justify-between">
            <div class="flex gap-4">
                <select id="filter-objectif" onchange="window.location.href=this.value"
                    class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                    <option value="{{ route('extrants.index') }}">Tous les objectifs</option>
                    @foreach ($objectifs as $objectif)
                        <option value="{{ route('extrants.index', ['objectif_id' => $objectif->id]) }}"
                            {{ request('objectif_id') == $objectif->id ? 'selected' : '' }}>
                            {{ $objectif->code }} - {{ $objectif->annee }}
                        </option>
                    @endforeach
                </select>

                <select id="filter-statut" onchange="window.location.href=this.value"
                    class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                    <option value="{{ route('extrants.index') }}">Tous les statuts</option>
                    <option value="{{ route('extrants.index', ['is_active' => 1]) }}"
                        {{ request('is_active') == '1' ? 'selected' : '' }}>Actifs</option>
                    <option value="{{ route('extrants.index', ['is_active' => 0]) }}"
                        {{ request('is_active') == '0' ? 'selected' : '' }}>Inactifs</option>
                </select>

                <form method="GET" action="{{ route('extrants.index') }}" class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                        class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 pr-10 text-sm dark:text-white w-64">
                    <button type="submit" class="absolute right-2 top-2.5">
                        <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Tableau -->
        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Code</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Libellé</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Objectif</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Ordre</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Statut</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($extrants as $extrant)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm dark:text-white">
                                {{ $extrant->code }}</td>
                            <td class="px-6 py-4 dark:text-white">{{ Str::limit($extrant->libelle, 60) }}</td>
                            <td class="px-6 py-4 dark:text-white">{{ $extrant->objectif->code ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap dark:text-white">{{ $extrant->ordre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs rounded-full {{ $extrant->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $extrant->is_active ? '✅ Actif' : '❌ Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-2">
                                    <a href="{{ route('extrants.show', $extrant) }}"
                                        class="text-blue-600 hover:text-blue-800" title="Voir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    @can('edit_extrants')
                                        <a href="{{ route('extrants.edit', $extrant) }}"
                                            class="text-yellow-600 hover:text-yellow-800" title="Modifier">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                    @endcan
                                    @can('delete_extrants')
                                        <form action="{{ route('extrants.destroy', $extrant) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Confirmer la suppression ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800"
                                                title="Supprimer">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                    @can('edit_extrants')
                                        <form action="{{ route('extrants.toggle-status', $extrant) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="{{ $extrant->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700' }}"
                                                title="{{ $extrant->is_active ? 'Désactiver' : 'Activer' }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                Aucun extrant trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $extrants->links() }}
        </div>
    </div>
</x-layouts::app>
