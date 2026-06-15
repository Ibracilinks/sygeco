<x-layouts::app title="Validations">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Activités en attente de validation</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ number_format($compteur) }} activité(s) soumise(s) à contrôler.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('validations.exporter', request()->query()) }}"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500">
                    Exporter CSV
                </a>
                <button type="button" onclick="submitBulkValidation()"
                    class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Valider sélection
                </button>
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

        <form method="GET" action="{{ route('validations.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-6">
            <select name="departement_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous départements</option>
                @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}" @selected((string) request('departement_id') === (string) $departement->id)>{{ $departement->nom }}</option>
                @endforeach
            </select>

            <select name="extrant_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous extrants</option>
                @foreach ($extrants as $extrant)
                    <option value="{{ $extrant->id }}" @selected((string) request('extrant_id') === (string) $extrant->id)>{{ $extrant->code }}</option>
                @endforeach
            </select>

            <select name="trimestre" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous trimestres</option>
                <option value="1" @selected(request('trimestre') === '1')>T1</option>
                <option value="2" @selected(request('trimestre') === '2')>T2</option>
                <option value="3" @selected(request('trimestre') === '3')>T3</option>
                <option value="4" @selected(request('trimestre') === '4')>T4</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">

            <div class="flex gap-2">
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Filtrer</button>
                <a href="{{ route('validations.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">Reset</a>
            </div>
        </form>

        <form id="bulkValidateForm" action="{{ route('validations.valider-plusieurs') }}" method="POST" class="hidden">@csrf</form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left"><input type="checkbox" id="selectAll" class="rounded border-slate-300" onchange="toggleAll(this)"></th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Activité</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Département</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Extrant</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Soumise le</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Coût</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($activites as $activite)
                        <tr class="align-top">
                            <td class="px-5 py-4"><input type="checkbox" class="row-checkbox rounded border-slate-300" value="{{ $activite->id }}"></td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">ACT-{{ $activite->id }} • {{ Str::limit($activite->nom_activite, 80) }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ Str::limit($activite->indicateur_objectivement_verifiable, 80) }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $activite->departement->nom ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $activite->extrant->code ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ optional($activite->date_soumission)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ number_format($activite->cout, 0, ',', ' ') }} FCFA</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-1">
                                    <x-actions.view :href="route('validations.show', $activite)" />
                                    <x-action variant="validate" icon="check" type="button" label="Valider"
                                        onclick="openValiderModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->take(3)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})" />
                                    <x-action variant="refuse" icon="x-mark" type="button" label="A Traiter"
                                        onclick="openRefuserModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->take(3)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Aucune activité en attente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $activites->links() }}</div>

        @include('pages.validations.partials.modal-valider')
        @include('pages.validations.partials.modal-refuser')
    </div>

    <script>
        function toggleAll(source) {
            document.querySelectorAll('.row-checkbox').forEach((checkbox) => checkbox.checked = source.checked);
        }

        function submitBulkValidation() {
            const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map((el) => el.value);
            if (selected.length === 0) {
                alert('Sélectionnez au moins une activité à valider.');
                return;
            }

            const form = document.getElementById('bulkValidateForm');
            form.querySelectorAll('input[name="activite_ids[]"]').forEach((input) => input.remove());

            selected.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'activite_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            form.submit();
        }

        function openValiderModal(id, title, history) {
            document.getElementById('validerModalTitle').textContent = title;
            document.getElementById('validerForm').action = '{{ url('validations') }}/' + id + '/valider';
            document.getElementById('validerHistory').innerHTML = buildHistoryHtml(history);
            document.getElementById('validerModal').classList.remove('hidden');
        }

        function openRefuserModal(id, title, history) {
            document.getElementById('refuserModalTitle').textContent = title;
            document.getElementById('refuserForm').action = '{{ url('validations') }}/' + id + '/refuser';
            document.getElementById('refuserHistory').innerHTML = buildHistoryHtml(history);
            document.getElementById('refuserModal').classList.remove('hidden');
        }

        function buildHistoryHtml(history) {
            if (!history || history.length === 0) {
                return '<div class="text-sm text-slate-500">Aucun historique disponible.</div>';
            }

            return history.map((entry) => {
                return '<div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/60 mb-2">' +
                    '<div class="text-xs text-slate-500">' + entry.created_at + ' • ' + (entry.utilisateur || 'N/A') + '</div>' +
                    '<div class="text-sm font-medium text-slate-900 dark:text-white">' + entry.action.toUpperCase() + '</div>' +
                    '<div class="text-xs text-slate-500 mt-1">' + (entry.commentaire || 'Pas de commentaire') + '</div>' +
                '</div>';
            }).join('');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</x-layouts::app>
