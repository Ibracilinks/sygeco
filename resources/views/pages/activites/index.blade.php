<x-layouts::app title="Activités">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Activités</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Suivi opérationnel des activités par extrant, département et statut.</p>
            </div>
            @can('create_activites')
                <a href="{{ route('activites.create') }}" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Nouvelle activité
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['total'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Brouillon</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['brouillon'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/25">
                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Soumis</p>
                <p class="mt-2 text-3xl font-semibold text-amber-800 dark:text-amber-100">{{ number_format($summary['soumis'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/25">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Validé</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($summary['valide'] ?? 0) }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/40">
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 dark:border-rose-800 dark:bg-rose-950/40">
                <p class="text-sm font-medium text-rose-800 dark:text-rose-200">{{ session('error') }}</p>
            </div>
        @endif

        <form method="GET" action="{{ route('activites.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-6">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nom, indicateur"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">

            <select name="extrant_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous extrants</option>
                @foreach ($extrants as $extrant)
                    <option value="{{ $extrant->id }}" @selected((string) ($filters['extrant_id'] ?? '') === (string) $extrant->id)>
                        {{ $extrant->code }}
                    </option>
                @endforeach
            </select>

            <select name="departement_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous départements</option>
                @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}" @selected((string) ($filters['departement_id'] ?? '') === (string) $departement->id)>
                        {{ $departement->nom }}
                    </option>
                @endforeach
            </select>

            <select name="statut" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous statuts</option>
                @foreach ($statuts as $statut)
                    <option value="{{ $statut }}" @selected(($filters['statut'] ?? '') === $statut)>{{ ucfirst($statut) }}</option>
                @endforeach
            </select>

            <select name="trimestre" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous trimestres</option>
                <option value="1" @selected(($filters['trimestre'] ?? '') === '1')>T1</option>
                <option value="2" @selected(($filters['trimestre'] ?? '') === '2')>T2</option>
                <option value="3" @selected(($filters['trimestre'] ?? '') === '3')>T3</option>
                <option value="4" @selected(($filters['trimestre'] ?? '') === '4')>T4</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Filtrer</button>
                <a href="{{ route('activites.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Activité</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Extrant</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Département</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Coût</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($activites as $activite)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ Str::limit($activite->nom_activite, 90) }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ Str::limit($activite->indicateur_objectivement_verifiable, 90) }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Trimestres: {{ $activite->trimestres_selectionnes ?: '-' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $activite->extrant->code ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $activite->departement->nom ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ number_format($activite->cout, 0, ',', ' ') }} FCFA</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold
                                {{ $activite->statut == 'valide'
                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200'
                                    : ($activite->statut == 'soumis'
                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'
                                        : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200') }}">
                                    {{ $activite->statut_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-3 text-sm">
                                    <a href="{{ route('activites.show', $activite) }}" class="font-medium text-sky-700 hover:text-sky-600 dark:text-sky-300">Voir</a>

                                    @if ($activite->estModifiable())
                                        @can('edit_activites')
                                            <a href="{{ route('activites.edit', $activite) }}" class="font-medium text-amber-700 hover:text-amber-600 dark:text-amber-300">Modifier</a>
                                        @endcan
                                        @can('delete_activites')
                                            <form action="{{ route('activites.destroy', $activite) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer la suppression ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-rose-700 hover:text-rose-600 dark:text-rose-300">Supprimer</button>
                                            </form>
                                        @endcan
                                    @endif

                                    @can('submit_activites')
                                        @if ($activite->statut === 'brouillon')
                                            <form action="{{ route('activites.soumettre', $activite) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="font-medium text-indigo-700 hover:text-indigo-600 dark:text-indigo-300">Soumettre</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('validate_activites')
                                        @if ($activite->statut === 'soumis')
                                            <form action="{{ route('activites.valider', $activite) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="font-medium text-emerald-700 hover:text-emerald-600 dark:text-emerald-300">Valider</button>
                                            </form>
                                            <button type="button" onclick="openRefusModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}')" class="font-medium text-rose-700 hover:text-rose-600 dark:text-rose-300">Refuser</button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Aucune activité trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $activites->links() }}</div>
    </div>

    <div id="refusModal" class="fixed inset-0 z-50 hidden bg-slate-900/50">
        <div class="mx-auto mt-24 w-full max-w-lg rounded-xl border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Refuser l'activité</h3>
                <button onclick="closeRefusModal()" class="text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-100">✕</button>
            </div>
            <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">Activité: <span id="activiteNom" class="font-medium text-slate-900 dark:text-white"></span></p>
            <form id="refusForm" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="motif_refus" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Motif du refus *</label>
                    <textarea id="motif_refus" name="motif_refus" rows="4" required minlength="10"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRefusModal()" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-100">Annuler</button>
                    <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-500">Refuser</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openRefusModal(activiteId, activiteNom) {
                document.getElementById('activiteNom').textContent = activiteNom;
                document.getElementById('refusForm').action = `/activites/${activiteId}/refuser`;
                document.getElementById('refusModal').classList.remove('hidden');
            }

            function closeRefusModal() {
                document.getElementById('refusModal').classList.add('hidden');
                document.getElementById('motif_refus').value = '';
            }
        </script>
    @endpush
</x-layouts::app>
