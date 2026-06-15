<x-layouts::app title="Validation activité">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Validation de l'activité</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">ACT-{{ $activite->id }} • {{ Str::limit($activite->nom_activite, 90) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('validations.index') }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Retour</a>
                <button type="button" onclick="openValiderModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->sortByDesc('created_at')->take(5)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Valider</button>
                <button type="button" onclick="openRefuserModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->sortByDesc('created_at')->take(5)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})" class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-500">Refuser</button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Détails de l'activité</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                    <div><dt class="text-slate-500 dark:text-slate-400">Nom</dt><dd class="font-medium text-slate-900 dark:text-white">{{ $activite->nom_activite }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Coût</dt><dd class="font-medium text-slate-900 dark:text-white">{{ number_format($activite->cout, 0, ',', ' ') }} FCFA</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Département</dt><dd class="font-medium text-slate-900 dark:text-white">{{ $activite->departement->nom ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Extrant</dt><dd class="font-medium text-slate-900 dark:text-white">{{ $activite->extrant->code ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Date de soumission</dt><dd class="font-medium text-slate-900 dark:text-white">{{ optional($activite->date_soumission)->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Saisi par</dt><dd class="font-medium text-slate-900 dark:text-white">{{ $activite->saisiePar->name ?? '-' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Indicateur</dt><dd class="font-medium text-slate-900 dark:text-white">{{ $activite->indicateur_objectivement_verifiable }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Moyen de vérification</dt><dd class="font-medium text-slate-900 dark:text-white">{{ $activite->moyen_verification }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Commentaires</dt><dd class="font-medium text-slate-900 dark:text-white">{{ $activite->commentaires ?: 'Aucun commentaire' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Historique de validation</h2>
                @if ($activite->validationHistoriques->isEmpty())
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Aucun historique disponible.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($activite->validationHistoriques->sortByDesc('created_at') as $historique)
                            <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/60">
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ optional($historique->created_at)->format('d/m/Y H:i') }}</p>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ ucfirst($historique->action) }} • {{ $historique->utilisateur->name ?? 'Système' }}</p>
                                @if ($historique->commentaire)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $historique->commentaire }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @include('pages.validations.partials.modal-valider')
        @include('pages.validations.partials.modal-refuser')
    </div>

    <script>
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
