<x-layouts::app title="Détails activité">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $activite->nom_activite }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Extrant: {{ $activite->extrant->code ?? '-' }} • Département: {{ $activite->departement->nom ?? '-' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($activite->estModifiable())
                    @can('edit_activites')
                        <a href="{{ route('activites.edit', $activite) }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-400">Modifier</a>
                    @endcan
                @endif
                @can('submit', $activite)
                    @if ($activite->statut === 'brouillon')
                        <form action="{{ route('activites.soumettre', $activite) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500">Soumettre</button>
                        </form>
                    @endif
                @endcan
                <a href="{{ route('activites.index') }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Retour</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</p>
                <p class="mt-2 text-lg font-semibold {{ $activite->statut === 'valide' ? 'text-emerald-700 dark:text-emerald-300' : ($activite->statut === 'soumis' ? 'text-amber-700 dark:text-amber-300' : 'text-slate-700 dark:text-slate-300') }}">{{ $activite->statut_label }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Coût</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($activite->cout, 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Trimestres</p>
                <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $activite->trimestres_selectionnes ?: '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Saisi par</p>
                <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $activite->saisiePar->name ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Date de saisie</p>
                <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $activite->date_saisie ? $activite->date_saisie->format('d/m/Y') : '-' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Informations détaillées</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Indicateur objectivement vérifiable</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $activite->indicateur_objectivement_verifiable }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Moyen de vérification</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $activite->moyen_verification }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Commentaires</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $activite->commentaires ?: 'Aucun commentaire' }}</dd>
                    </div>
                    @if ($activite->motif_refus)
                        <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 dark:border-rose-800 dark:bg-rose-950/30">
                            <dt class="text-rose-700 dark:text-rose-300">Motif de refus</dt>
                            <dd class="mt-1 font-medium text-rose-800 dark:text-rose-200">{{ $activite->motif_refus }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Historique</h2>
                @if ($activite->validationHistoriques->isEmpty())
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Aucun historique disponible.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($activite->validationHistoriques->sortByDesc('created_at') as $historique)
                            <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/60">
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $historique->created_at->format('d/m/Y H:i') }}</p>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ ucfirst($historique->action) }} par {{ $historique->utilisateur->name ?? 'Système' }}</p>
                                @if ($historique->commentaire)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $historique->commentaire }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
