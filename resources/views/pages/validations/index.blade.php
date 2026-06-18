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
                <button type="button" onclick="openFusionModal()"
                    class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-violet-700">
                    Fusionner sélection
                </button>
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

        <form id="filterForm" method="GET" action="{{ route('validations.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-6">
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
                <a href="{{ route('validations.index') }}" wire:navigate class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">Reset</a>
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
                        @php
                            $arbData = [
                                'id' => $activite->id,
                                'nom_activite' => $activite->nom_activite,
                                'indicateur_objectivement_verifiable' => $activite->indicateur_objectivement_verifiable,
                                'moyen_verification' => $activite->moyen_verification,
                                'cout' => (float) $activite->cout,
                                'trimestre_1' => $activite->trimestre_1,
                                'trimestre_2' => $activite->trimestre_2,
                                'trimestre_3' => $activite->trimestre_3,
                                'trimestre_4' => $activite->trimestre_4,
                                'extrant_id' => $activite->extrant_id,
                                'extrant_code' => $activite->extrant->code ?? '-',
                                'departement_id' => $activite->departement_id,
                                'departement_nom' => $activite->departement->nom ?? '-',
                            ];
                        @endphp
                        <tr class="align-top">
                            <td class="px-5 py-4"><input type="checkbox" class="row-checkbox rounded border-slate-300" value="{{ $activite->id }}" data-activite="{{ json_encode($arbData, JSON_UNESCAPED_UNICODE) }}"></td>
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
                                    <x-actions.view :href="route('validations.show', $activite)" wire:navigate />
                                    <x-action variant="validate" icon="check" type="button" label="Valider"
                                        onclick="openValiderModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->take(3)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})" />
                                    <x-action variant="refuse" icon="x-mark" type="button" label="A Traiter"
                                        onclick="openRefuserModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->take(3)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})" />
                                    <x-action variant="edit" icon="pencil" type="button" label="Modifier"
                                        onclick="openArbitrerModifier({{ $activite->id }})" />
                                    <x-action variant="delete" icon="trash" type="button" label="Supprimer"
                                        onclick="openArbitrerSupprimer({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}')" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Aucune activité en attente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="paginationWrap" class="mt-2">{{ $activites->links() }}</div>

        @include('pages.validations.partials.modal-valider')
        @include('pages.validations.partials.modal-refuser')
        @include('pages.validations.partials.modal-arbitrer-modifier')
        @include('pages.validations.partials.modal-arbitrer-supprimer')
        @include('pages.validations.partials.modal-arbitrer-fusionner')
    </div>

    <style>
        .arb-input { width: 100%; border-radius: 0.5rem; border: 1px solid #cbd5e1; background: #fff; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #334155; outline: none; }
        .arb-input:focus { border-color: #64748b; }
        .dark .arb-input { border-color: #334155; background: #020617; color: #e2e8f0; }
    </style>

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

        // ----- Arbitrage budgétaire -----

        function escHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function selectedActivites() {
            return Array.from(document.querySelectorAll('.row-checkbox:checked'))
                .map((el) => JSON.parse(el.dataset.activite));
        }

        function activiteData(id) {
            const cb = document.querySelector('.row-checkbox[value="' + id + '"]');
            return cb ? JSON.parse(cb.dataset.activite) : null;
        }

        function openArbitrerModifier(id) {
            const a = activiteData(id);
            if (!a) return;
            const form = document.getElementById('arbModifierForm');
            form.action = '{{ url('validations') }}/' + id + '/arbitrer-modifier';
            form.nom_activite.value = a.nom_activite || '';
            form.indicateur_objectivement_verifiable.value = a.indicateur_objectivement_verifiable || '';
            form.moyen_verification.value = a.moyen_verification || '';
            form.cout.value = Math.round(a.cout || 0);
            form.motif.value = '';
            [1, 2, 3, 4].forEach((t) => {
                document.getElementById('arbMod_t' + t).checked = a['trimestre_' + t] === 'oui';
            });
            document.getElementById('arbModifierModal').classList.remove('hidden');
        }

        function openArbitrerSupprimer(id, nom) {
            const form = document.getElementById('arbSupprimerForm');
            form.action = '{{ url('validations') }}/' + id + '/arbitrer-supprimer';
            form.motif.value = '';
            document.getElementById('arbSupprimerNom').textContent = nom;
            document.getElementById('arbSupprimerModal').classList.remove('hidden');
        }

        function openFusionModal() {
            const sources = selectedActivites();
            if (sources.length < 2) {
                alert('Sélectionnez au moins deux activités à fusionner.');
                return;
            }

            // Identifiants des sources
            document.getElementById('fusionIds').innerHTML = sources
                .map((s) => '<input type="hidden" name="activite_ids[]" value="' + s.id + '">')
                .join('');

            const textFields = [
                ['nom_activite', "Nom de l'activité"],
                ['indicateur_objectivement_verifiable', 'Indicateur objectivement vérifiable'],
                ['moyen_verification', 'Moyen de vérification'],
            ];

            let html = '';

            // Champs texte : radios des sources + valeur finale éditable
            textFields.forEach(([field, title]) => {
                const radios = sources.map((s, i) =>
                    '<label class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-300">' +
                    '<input type="radio" name="pick_' + field + '" class="mt-0.5" ' + (i === 0 ? 'checked' : '') +
                    ' data-val="' + escHtml(s[field]) + '"' +
                    ' onclick="document.getElementById(\'fus_' + field + '\').value = this.dataset.val">' +
                    '<span>' + escHtml(s[field]) + '</span></label>'
                ).join('');

                html += '<div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">' +
                    '<p class="text-sm font-medium text-slate-700 dark:text-slate-200">' + title + '</p>' +
                    '<div class="mt-2 space-y-1">' + radios + '</div>' +
                    '<textarea id="fus_' + field + '" name="' + field + '" rows="2" class="arb-input mt-2">' + escHtml(sources[0][field]) + '</textarea>' +
                    '</div>';
            });

            // Coût : radios (chaque source + somme) + valeur finale
            const sum = sources.reduce((acc, s) => acc + Number(s.cout || 0), 0);
            let coutRadios = sources.map((s) =>
                '<label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">' +
                '<input type="radio" name="pick_cout" data-val="' + Math.round(s.cout || 0) + '"' +
                ' onclick="document.getElementById(\'fus_cout\').value = this.dataset.val">' +
                new Intl.NumberFormat('fr-FR').format(Math.round(s.cout || 0)) + '</label>'
            ).join('');
            coutRadios += '<label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-200">' +
                '<input type="radio" name="pick_cout" checked data-val="' + sum + '"' +
                ' onclick="document.getElementById(\'fus_cout\').value = this.dataset.val">' +
                'Somme : ' + new Intl.NumberFormat('fr-FR').format(sum) + '</label>';

            html += '<div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">' +
                '<p class="text-sm font-medium text-slate-700 dark:text-slate-200">Coût (FCFA)</p>' +
                '<div class="mt-2 space-y-1">' + coutRadios + '</div>' +
                '<input type="number" id="fus_cout" name="cout" min="0" step="1" value="' + sum + '" class="arb-input mt-2">' +
                '</div>';

            // Chronogramme : coché si au moins une source a le trimestre
            let trims = [1, 2, 3, 4].map((t) => {
                const anyOui = sources.some((s) => s['trimestre_' + t] === 'oui');
                return '<label class="inline-flex items-center gap-1.5 text-sm text-slate-700 dark:text-slate-200">' +
                    '<input type="hidden" name="trimestre_' + t + '" value="non">' +
                    '<input type="checkbox" name="trimestre_' + t + '" value="oui" class="rounded border-slate-300" ' + (anyOui ? 'checked' : '') + '>' +
                    'T' + t + '</label>';
            }).join('');
            html += '<div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">' +
                '<p class="text-sm font-medium text-slate-700 dark:text-slate-200">Chronogramme</p>' +
                '<div class="mt-2 flex flex-wrap gap-3">' + trims + '</div></div>';

            // Extrant et département : sélection parmi les sources
            const exMap = {}, depMap = {};
            sources.forEach((s) => { exMap[s.extrant_id] = s.extrant_code; depMap[s.departement_id] = s.departement_nom; });
            const exOpts = Object.keys(exMap).map((id, i) => '<option value="' + id + '" ' + (i === 0 ? 'selected' : '') + '>' + escHtml(exMap[id]) + '</option>').join('');
            const depOpts = Object.keys(depMap).map((id, i) => '<option value="' + id + '" ' + (i === 0 ? 'selected' : '') + '>' + escHtml(depMap[id]) + '</option>').join('');

            html += '<div class="grid grid-cols-1 gap-4 md:grid-cols-2">' +
                '<div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">' +
                '<p class="text-sm font-medium text-slate-700 dark:text-slate-200">Extrant</p>' +
                '<select name="extrant_id" class="arb-input mt-2">' + exOpts + '</select></div>' +
                '<div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">' +
                '<p class="text-sm font-medium text-slate-700 dark:text-slate-200">Département responsable</p>' +
                '<select name="departement_id" class="arb-input mt-2">' + depOpts + '</select></div>' +
                '</div>';

            document.getElementById('fusionFields').innerHTML = html;
            document.getElementById('arbFusionnerModal').classList.remove('hidden');
        }

        // ----- Navigation SPA (wire:navigate) : filtres & pagination sans rechargement complet -----
        (function () {
            if (typeof Livewire === 'undefined' || typeof Livewire.navigate !== 'function') {
                return;
            }

            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const params = new URLSearchParams(new FormData(filterForm));
                    // URL propre : on retire les paramètres vides
                    Array.from(params.keys()).forEach((key) => {
                        if (!params.get(key)) {
                            params.delete(key);
                        }
                    });
                    const query = params.toString();
                    Livewire.navigate(filterForm.action + (query ? '?' + query : ''));
                });
            }

            const paginationWrap = document.getElementById('paginationWrap');
            if (paginationWrap) {
                paginationWrap.addEventListener('click', function (event) {
                    const link = event.target.closest('a[href]');
                    if (!link || !paginationWrap.contains(link)) {
                        return;
                    }
                    event.preventDefault();
                    Livewire.navigate(link.href);
                });
            }
        })();
    </script>
</x-layouts::app>
