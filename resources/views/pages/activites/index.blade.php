<x-layouts::app title="Activités">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">Activités</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-1">Gestion des activités saisies par les départements</p>
            </div>
            @can('create_activites')
                <a href="{{ route('activites.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nouvelle Activité
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
            <div class="flex flex-wrap gap-4">
                <select name="extrant_id"
                    onchange="window.location.href=updateQueryStringParameter(window.location.href, 'extrant_id', this.value)"
                    class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                    <option value="">Tous les extrants</option>
                    @foreach ($extrants as $extrant)
                        <option value="{{ $extrant->id }}"
                            {{ request('extrant_id') == $extrant->id ? 'selected' : '' }}>
                            {{ $extrant->code }} - {{ Str::limit($extrant->libelle, 40) }}
                        </option>
                    @endforeach
                </select>

                <select name="departement_id"
                    onchange="window.location.href=updateQueryStringParameter(window.location.href, 'departement_id', this.value)"
                    class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                    <option value="">Tous les départements</option>
                    @foreach ($departements as $departement)
                        <option value="{{ $departement->id }}"
                            {{ request('departement_id') == $departement->id ? 'selected' : '' }}>
                            {{ $departement->nom }}
                        </option>
                    @endforeach
                </select>

                <select name="statut"
                    onchange="window.location.href=updateQueryStringParameter(window.location.href, 'statut', this.value)"
                    class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                    <option value="">Tous les statuts</option>
                    <option value="brouillon" {{ request('statut') == 'brouillon' ? 'selected' : '' }}>Brouillon
                    </option>
                    <option value="soumis" {{ request('statut') == 'soumis' ? 'selected' : '' }}>Soumis</option>
                    <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validé</option>
                </select>

                <select name="trimestre"
                    onchange="window.location.href=updateQueryStringParameter(window.location.href, 'trimestre', this.value)"
                    class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                    <option value="">Tous les trimestres</option>
                    <option value="1" {{ request('trimestre') == '1' ? 'selected' : '' }}>T1</option>
                    <option value="2" {{ request('trimestre') == '2' ? 'selected' : '' }}>T2</option>
                    <option value="3" {{ request('trimestre') == '3' ? 'selected' : '' }}>T3</option>
                    <option value="4" {{ request('trimestre') == '4' ? 'selected' : '' }}>T4</option>
                </select>

                <form method="GET" action="{{ route('activites.index') }}" class="relative">
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
            class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Activité</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Extrant</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Département</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Coût</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Trimestres</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Statut</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($activites as $activite)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium dark:text-white">{{ Str::limit($activite->nom_activite, 50) }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ Str::limit($activite->indicateur_objectivement_verifiable, 40) }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm dark:text-white">{{ $activite->extrant->code ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm dark:text-white">{{ $activite->departement->nom ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm dark:text-white">
                                {{ number_format($activite->cout, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-sm dark:text-white">{{ $activite->trimestres_selectionnes }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs rounded-full
                                    {{ $activite->statut == 'valide'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : ($activite->statut == 'soumis'
                                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300') }}">
                                    {{ $activite->statut_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-2">
                                    <a href="{{ route('activites.show', $activite) }}"
                                        class="text-blue-600 hover:text-blue-800" title="Voir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>

                                    @if ($activite->estModifiable())
                                        @can('edit_activites')
                                            <a href="{{ route('activites.edit', $activite) }}"
                                                class="text-yellow-600 hover:text-yellow-800" title="Modifier">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('delete_activites')
                                            <form action="{{ route('activites.destroy', $activite) }}" method="POST"
                                                class="inline" onsubmit="return confirm('Confirmer la suppression ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800"
                                                    title="Supprimer">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    @endif

                                    @if ($activite->statut == 'brouillon')
                                        <form action="{{ route('activites.soumettre', $activite) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit" class="text-blue-500 hover:text-blue-700"
                                                title="Soumettre">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if ($activite->statut == 'soumis' && auth()->user()->hasRole('dbcgoq'))
                                        <form action="{{ route('activites.valider', $activite) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-500 hover:text-green-700"
                                                title="Valider">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                Aucune activité trouvée
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $activites->links() }}
        </div>
    </div>
</x-layouts::app>

@push('scripts')
    <script>
        function updateQueryStringParameter(uri, key, value) {
            if (!value) {
                var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
                return uri.replace(re, '$1$2');
            }
            var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
            var separator = uri.indexOf('?') !== -1 ? "&" : "?";
            if (uri.match(re)) {
                return uri.replace(re, '$1' + key + "=" + value + '$2');
            } else {
                return uri + separator + key + "=" + value;
            }
        }
    </script>
@endpush
