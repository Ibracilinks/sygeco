<x-layouts::app :title="__('Gestion des activités')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Activités</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Gestion des activités opérationnelles
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('activites.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouvelle activité
                </a>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
            <form method="GET" action="{{ route('activites.index') }}" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Rechercher par code, libellé..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>

                <select name="statut"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Tous statuts</option>
                    <option value="1" {{ request('statut') == '1' ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ request('statut') == '0' ? 'selected' : '' }}>Inactif</option>
                </select>

                <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium hover:bg-gray-200">
                    Filtrer
                </button>

                @if (request()->anyFilled(['search', 'statut']))
                    <a href="{{ route('activites.index') }}" class="text-red-600 hover:text-red-700 px-4 py-2 text-sm">
                        Réinitialiser
                    </a>
                @endif
            </form>
        </div>

        {{-- Tableau --}}
        <div
            class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Code</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Libellé</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Extrant</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Budget</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Période</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Ordre</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-neutral-900">
                        @forelse($activites as $activite)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-sm font-medium text-indigo-600">
                                    {{ $activite->code }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $activite->libelle }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($activite->description, 50) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $activite->extrant->code ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium">
                                    {{ number_format($activite->budget_previsionnel_global, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    {{ \Carbon\Carbon::parse($activite->date_debut_prevue)->format('d/m/Y') }}<br>
                                    <span class="text-xs text-gray-500">→
                                        {{ \Carbon\Carbon::parse($activite->date_fin_prevue)->format('d/m/Y') }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    {{ $activite->ordre }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($activite->is_active)
                                        <span
                                            class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Actif</span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Inactif</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('activites.show', $activite) }}"
                                            class="text-indigo-600 hover:text-indigo-900" title="Voir">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('activites.edit', $activite) }}"
                                            class="text-blue-600 hover:text-blue-900" title="Modifier">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('activites.destroy', $activite) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Supprimer cette activité ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                title="Supprimer">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                    Aucune activité trouvée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                {{ $activites->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
    