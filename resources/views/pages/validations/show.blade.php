<x-layouts::app title="Validation d'activité">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">Validation de l'activité</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-1">{{ Str::limit($activite->nom_activite, 80) }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('validations.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                    Retour à la liste
                </a>
                <button type="button"
                    onclick="openValiderModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->sortByDesc('created_at')->take(5)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})")"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition">
                    Valider
                </button>
                <button type="button"
                    onclick="openRefuserModal({{ $activite->id }}, '{{ addslashes($activite->nom_activite) }}', {{ json_encode($activite->validationHistoriques->sortByDesc('created_at')->take(5)->map(function ($item) {return ['action' => $item->action, 'commentaire' => $item->commentaire, 'utilisateur' => optional($item->utilisateur)->name, 'created_at' => optional($item->created_at)->format('d/m/Y H:i')];})) }})")"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                    Refuser
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div
                class="lg:col-span-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold dark:text-white">Détails de l'activité</h2>
                </div>

                <dl class="grid grid-cols-1 gap-4 text-sm text-zinc-600 dark:text-zinc-300">
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Code activité</dt>
                        <dd>ACT-{{ $activite->id }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Nom</dt>
                        <dd>{{ $activite->nom_activite }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Département</dt>
                        <dd>{{ $activite->departement->nom ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Extrant</dt>
                        <dd>{{ $activite->extrant->code ?? 'N/A' }} -
                            {{ Str::limit($activite->extrant->libelle ?? 'N/A', 60) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Date de soumission</dt>
                        <dd>{{ optional($activite->date_soumission)->format('d/m/Y H:i') ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Coût</dt>
                        <dd>{{ number_format($activite->cout, 0, ',', ' ') }} FCFA</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Trimestres</dt>
                        <dd>{{ $activite->trimestres_selectionnes }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Indicateur</dt>
                        <dd>{{ $activite->indicateur_objectivement_verifiable }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Moyen de vérification</dt>
                        <dd>{{ $activite->moyen_verification }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-800 dark:text-white">Commentaires</dt>
                        <dd>{{ $activite->commentaires ?? 'Aucun commentaire' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                    <h2 class="text-lg font-semibold dark:text-white mb-4">Contributeur</h2>
                    <div class="text-sm text-zinc-500 dark:text-zinc-300">Utilisateur</div>
                    <div class="font-medium dark:text-white">{{ $activite->saisiePar->name ?? 'N/A' }}</div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-300 mt-4">Email</div>
                    <div class="dark:text-white">{{ $activite->saisiePar->email ?? 'N/A' }}</div>
                </div>

                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                    <h2 class="text-lg font-semibold dark:text-white mb-4">Historique de validation</h2>
                    @if ($activite->validationHistoriques->isEmpty())
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Aucune action enregistrée.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($activite->validationHistoriques->sortByDesc('created_at') as $historique)
                                <div class="rounded-lg bg-neutral-50 dark:bg-zinc-900 p-3">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ optional($historique->created_at)->format('d/m/Y H:i') }} -
                                        {{ optional($historique->utilisateur)->name ?? 'Système' }}</div>
                                    <div class="text-sm font-medium dark:text-white">{{ ucfirst($historique->action) }}
                                        → {{ ucfirst($historique->nouveau_statut) }}</div>
                                    @if ($historique->commentaire)
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Motif / commentaire :
                                            {{ $historique->commentaire }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
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
                return '<div class="text-sm text-zinc-500">Aucun historique disponible.</div>';
            }

            return history.map(function(entry) {
                return '<div class="rounded-lg bg-neutral-50 dark:bg-zinc-900 p-3 mb-2">' +
                    '<div class="text-xs text-zinc-500">' + entry.created_at + ' - ' + (entry.utilisateur ||
                    'N/A') + '</div>' +
                    '<div class="text-sm font-medium dark:text-white">' + entry.action.toUpperCase() + ' → ' + entry
                    .commentaire + '</div>' +
                    '</div>';
            }).join('');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</x-layouts::app>
