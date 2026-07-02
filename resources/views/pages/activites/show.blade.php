<x-layouts::app title="Détails activité">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
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

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $activite->nom_activite }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Extrant: {{ $activite->extrant->code ?? '-' }} • Responsables: {{ $activite->departements->isNotEmpty() ? $activite->departements->pluck('nom')->join(' / ') : ($activite->departement->nom ?? '-') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($activite->estModifiable())
                    @can('edit_activites')
                        <a href="{{ route('activites.edit', $activite) }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-400">Modifier</a>
                    @endcan
                @endif
                @can('submit', $activite)
                    @if ($activite->peutEtreSoumis())
                        <form action="{{ route('activites.soumettre', $activite) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500">{{ $activite->statut === 'rejete' ? 'Re-soumettre' : 'Soumettre' }}</button>
                        </form>
                    @endif
                @endcan
                <a href="{{ route('activites.index') }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Retour</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</p>
                <p class="mt-2 text-lg font-semibold {{ $activite->statut === 'valide' ? 'text-emerald-700 dark:text-emerald-300' : ($activite->statut === 'rejete' ? 'text-rose-700 dark:text-rose-300' : (in_array($activite->statut, ['en_attente', 'soumis']) ? 'text-amber-700 dark:text-amber-300' : 'text-slate-700 dark:text-slate-300')) }}">{{ $activite->statut_label }}</p>
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

        {{-- Suivi d'exécution (Track Activité) --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Suivi d'exécution</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">État d'avancement de l'activité (mi-parcours).</p>
                </div>
                <x-execution-badge :statut="$activite->statut_execution" class="px-3 py-1 text-sm" />
            </div>

            @can('edit_activites')
                <form action="{{ route('activites.execution', $activite) }}" method="POST" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-[200px_1fr_auto]">
                    @csrf
                    <select name="statut_execution" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        @foreach (\App\Models\Activite::STATUTS_EXECUTION as $val => $label)
                            <option value="{{ $val }}" @selected($activite->statut_execution === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="execution_commentaire" maxlength="1000"
                        value="{{ old('execution_commentaire', $activite->execution_commentaire) }}"
                        placeholder="Commentaire de suivi (optionnel)"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Mettre à jour</button>
                </form>
            @elseif ($activite->execution_commentaire)
                <p class="mt-3 text-sm text-slate-700 dark:text-slate-200">{{ $activite->execution_commentaire }}</p>
            @endcan

            @if ($activite->execution_maj_le)
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                    Dernière mise à jour : {{ $activite->execution_maj_le->format('d/m/Y H:i') }}
                    @if ($activite->executionMajPar) par {{ $activite->executionMajPar->name }} @endif
                </p>
            @endif
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

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Pièces jointes</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Documents de preuve et fichiers associés au suivi de l'activité.</p>
                    </div>
                </div>

                @if ($activite->piecesJointes->isEmpty())
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Aucun fichier joint pour le moment.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($activite->piecesJointes as $pieceJointe)
                            <div class="flex flex-col gap-3 rounded-lg border border-slate-200 p-4 dark:border-slate-700 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <a href="{{ route('activites.pieces-jointes.download', [$activite, $pieceJointe]) }}" class="text-sm font-semibold text-slate-900 transition hover:text-sky-700 hover:underline dark:text-white dark:hover:text-sky-300">
                                        {{ $pieceJointe->nom_original }}
                                    </a>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Ajouté le {{ $pieceJointe->created_at->format('d/m/Y H:i') }}
                                        @if ($pieceJointe->auteur)
                                            par {{ $pieceJointe->auteur->name }}
                                        @endif
                                        • {{ $pieceJointe->taille_lisible }}
                                    </p>
                                    @if ($pieceJointe->description)
                                        <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">{{ $pieceJointe->description }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('activites.pieces-jointes.download', [$activite, $pieceJointe]) }}" class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                                    Télécharger
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Ajouter un fichier</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Taille maximale : 10 Mo.</p>

                @if (auth()->user()->can('edit_activites') || auth()->user()->can('validate_activites'))
                    <form action="{{ route('activites.pieces-jointes.store', $activite) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label for="fichier" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Fichier</label>
                            <input id="fichier" type="file" name="fichier" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            @error('fichier')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="description" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Observation liée au fichier</label>
                            <textarea id="description" name="description" rows="3" maxlength="255"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                placeholder="Exemple : rapport de mission, photo de preuve, PV signé">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                            Envoyer le fichier
                        </button>
                    </form>
                @else
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Vous pouvez consulter les fichiers joints, mais vous n'avez pas le droit d'en ajouter.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
