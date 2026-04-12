<x-layouts::app title="Activités en attente de validation">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">Activités en attente de validation</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-1">{{ $compteur }} activité(s) en statut « soumis »</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('validations.exporter', request()->query()) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition">
                    Exporter Excel
                </a>
                <button type="button" onclick="submitBulkValidation()"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                    Valider sélection
                </button>
            </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <select id="departement-filter" onchange="updateFilter('departement_id', this.value)"
                class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                <option value="">Tous les départements</option>
                @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}"
                        {{ request('departement_id') == $departement->id ? 'selected' : '' }}>
                        {{ $departement->nom }}
                    </option>
                @endforeach
            </select>

            <select id="extrant-filter" onchange="updateFilter('extrant_id', this.value)"
                class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                <option value="">Tous les extrants</option>
                @foreach ($extrants as $extrant)
                    <option value="{{ $extrant->id }}" {{ request('extrant_id') == $extrant->id ? 'selected' : '' }}>
                        {{ $extrant->code }} - {{ Str::limit($extrant->libelle, 40) }}
                    </option>
                @endforeach
            </select>

            <select id="trimestre-filter" onchange="updateFilter('trimestre', this.value)"
                class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm dark:text-white">
                <option value="">Tous les trimestres</option>
                <option value="1" {{ request('trimestre') == '1' ? 'selected' : '' }}>T1</option>
                <option value="2" {{ request('trimestre') == '2' ? 'selected' : '' }}>T2</option>
                <option value="3" {{ request('trimestre') == '3' ? 'selected' : '' }}>T3</option>
                <option value="4" {{ request('trimestre') == '4' ? 'selected' : '' }}>T4</option>
            </select>

            <div class="grid grid-cols-2 gap-2">
                <input type="date" id="date-from" value="{{ request('date_from') }}"
                    onchange="updateFilter('date_from', this.value)"
                    class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm text-zinc-900 dark:text-white">
                <input type="date" id="date-to" value="{{ request('date_to') }}"
                    onchange="updateFilter('date_to', this.value)"
                    class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm text-zinc-900 dark:text-white">
            </div>
        </div>

        <form id="bulkValidateForm" action="{{ route('validations.valider-plusieurs') }}" method="POST">
            @csrf
            <input type="hidden" name="activite_ids" id="activite_ids" />
        </form>

        <div
            class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Code</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Activité</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Département</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Extrant</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Soumise le</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Coût</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Indicateur</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($activites as $activite)
                        <tr>
                            <td class="px-6 py-4">
                                <input type="checkbox" class="row-checkbox" value="{{ $activite->id }}">
                            </td>
                            <td class="px-6 py-4 text-sm dark:text-white">ACT-{{ $activite->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium dark:text-white">{{ Str::limit($activite->nom_activite, 60) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm dark:text-white">{{ $activite->departement->nom ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm dark:text-white">{{ $activite->extrant->code ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm dark:text-white">
                                {{ optional($activite->date_soumission)->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm dark:text-white">
                                {{ number_format($activite->cout, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-sm dark:text-white">
                                {{ Str::limit($activite->indicateur_objectivement_verifiable, 50) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('validations.show', $activite) }}"
                                        class="inline-flex items-center rounded-md bg-neutral-100 px-3 py-2 text-xs font-medium text-neutral-700 hover:bg-neutral-200 dark:bg-zinc-900 dark:text-white dark:hover:bg-zinc-800">
                                        Voir
                                    </a>
                                    <button type="button"
                                        onclick="openValiderModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->take(3)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})")"
                                        class="inline-flex items-center rounded-md bg-green-100 px-3 py-2 text-xs font-medium text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800">
                                        Valider
                                    </button>
                                    <button type="button"
                                        onclick="openRefuserModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->take(3)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})")"
                                        class="inline-flex items-center rounded-md bg-red-100 px-3 py-2 text-xs font-medium text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800">
                                        Refuser
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">Aucune
                                activité en attente</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $activites->links() }}</div>

        @include('pages.validations.partials.modal-valider')
        @include('pages.validations.partials.modal-refuser')
    </div>

    <script>
        function updateFilter(name, value) {
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set(name, value);
            } else {
                url.searchParams.delete(name);
            }
            window.location.href = url.toString();
        }

        function toggleAll(source) {
            document.querySelectorAll('.row-checkbox').forEach(function(checkbox) {
                checkbox.checked = source.checked;
            });
        }

        function submitBulkValidation() {
            const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(el => el.value);
            if (selected.length === 0) {
                alert('Sélectionnez au moins une activité à valider.');
                return;
            }
            document.getElementById('activite_ids').value = selected.join(',');
            document.getElementById('bulkValidateForm').submit();
        }

        function openValiderModal(id, title, history) {
            const modal = document.getElementById('validerModal');
            const form = document.getElementById('validerForm');
            const titleEl = document.getElementById('validerModalTitle');
            const historyEl = document.getElementById('validerHistory');

            titleEl.textContent = title;
            form.action = '{{ url('validations') }}/' + id + '/valider';
            historyEl.innerHTML = buildHistoryHtml(history);
            modal.classList.remove('hidden');
        }

        function openRefuserModal(id, title, history) {
            const modal = document.getElementById('refuserModal');
            const form = document.getElementById('refuserForm');
            const titleEl = document.getElementById('refuserModalTitle');
            const historyEl = document.getElementById('refuserHistory');

            titleEl.textContent = title;
            form.action = '{{ url('validations') }}/' + id + '/refuser';
            historyEl.innerHTML = buildHistoryHtml(history);
            modal.classList.remove('hidden');
        }

        function buildHistoryHtml(history) {
            if (!history || history.length === 0) {
                return '<div class="text-sm text-zinc-500">Aucun historique disponible.</div>';
            }

            return history.map(function(entry) {
                return '<div class="rounded-lg bg-neutral-50 dark:bg-zinc-900 p-3 mb-2">' +
                    '<div class="text-xs text-zinc-500">' + entry.created_at + ' par ' + (entry.utilisateur ||
                        'N/A') + '</div>' +
                    '<div class="text-sm font-medium dark:text-white">' + entry.action.toUpperCase() + '</div>' +
                    '<div class="text-sm text-zinc-500 mt-1">' + (entry.commentaire || 'Pas de commentaire') +
                    '</div>' +
                    '</div>';
            }).join('');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</x-layouts::app>
