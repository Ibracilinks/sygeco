<x-layouts::app title="Fiche de programmation">
    @php
        $exercice = $activite->exercice();
        $chronogramme = [
            1 => $activite->trimestre_1 === 'oui',
            2 => $activite->trimestre_2 === 'oui',
            3 => $activite->trimestre_3 === 'oui',
            4 => $activite->trimestre_4 === 'oui',
        ];
        $nbTrimestres = collect($chronogramme)->filter()->count();
        $coutParTrimestre = $nbTrimestres > 0 ? (float) $activite->cout / $nbTrimestres : null;
        $hierarchie = $activite->departement
            ? $activite->departement->ancetres()->push($activite->departement)->pluck('nom')
            : collect();
        $statutClasses = match ($activite->statut) {
            'valide' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-200',
            'rejete' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-200',
            'en_attente', 'soumis' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200',
            default => 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-950/40 dark:text-slate-200',
        };
    @endphp

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

        {{-- En-tête : rattachement stratégique + actions --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            Programmation
                        </span>
                        @if ($activite->non_programmee)
                            <span class="rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-800 dark:bg-orange-900/40 dark:text-orange-200">
                                Activité non programmée
                            </span>
                        @endif
                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statutClasses }}">
                            {{ $activite->statut_label }}
                        </span>
                        @if ($exercice)
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                Exercice {{ $exercice->annee }}
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">{{ $activite->nom_activite }}</h1>

                    {{-- Chaîne de résultats : objectif → résultat → extrant --}}
                    <nav class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                        @if ($activite->extrant?->objectif)
                            <a href="{{ route('objectifs.show', $activite->extrant->objectif) }}" class="hover:text-sky-700 hover:underline dark:hover:text-sky-300">
                                Objectif {{ $activite->extrant->objectif->code }}
                            </a>
                            <span>›</span>
                        @endif
                        @if ($activite->extrant?->resultat)
                            <a href="{{ route('resultats.show', $activite->extrant->resultat) }}" class="hover:text-sky-700 hover:underline dark:hover:text-sky-300">
                                Résultat {{ $activite->extrant->resultat->code }}
                            </a>
                            <span>›</span>
                        @endif
                        @if ($activite->extrant)
                            <a href="{{ route('extrants.show', $activite->extrant) }}" class="font-medium text-slate-700 hover:text-sky-700 hover:underline dark:text-slate-200 dark:hover:text-sky-300">
                                Extrant {{ $activite->extrant->code }} — {{ Str::limit($activite->extrant->libelle, 70) }}
                            </a>
                        @else
                            <span>Aucun extrant rattaché</span>
                        @endif
                    </nav>
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
                    <a href="{{ route('evaluations.index', 'mi-parcours') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Évaluation</a>
                    <a href="{{ route('activites.index') }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Retour</a>
                </div>
            </div>

            @if ($activite->statut === 'rejete' && $activite->motif_refus)
                <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/60 dark:bg-rose-950/30">
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">Motif du rejet</p>
                    <p class="mt-1 text-sm text-rose-800 dark:text-rose-200">{{ $activite->motif_refus }}</p>
                    <p class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                        Rejeté {{ $activite->refuse_le ? 'le '.$activite->refuse_le->format('d/m/Y à H:i') : '' }}
                        @if ($activite->refusePar) par {{ $activite->refusePar->name }} @endif
                    </p>
                </div>
            @endif
        </div>

        {{-- Chiffres clés de la programmation --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Coût programmé</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($activite->cout, 0, ',', ' ') }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">FCFA</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Chronogramme</p>
                <div class="mt-2 flex gap-1.5">
                    @foreach ($chronogramme as $trimestre => $actif)
                        <span class="flex h-9 flex-1 items-center justify-center rounded-lg text-sm font-semibold
                            {{ $actif
                                ? 'bg-sky-100 text-sky-800 ring-1 ring-sky-300 dark:bg-sky-900/40 dark:text-sky-200 dark:ring-sky-800'
                                : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600' }}">
                            T{{ $trimestre }}
                        </span>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    {{ $nbTrimestres > 0 ? $nbTrimestres.' trimestre'.($nbTrimestres > 1 ? 's' : '').' planifié'.($nbTrimestres > 1 ? 's' : '') : 'Aucun trimestre planifié' }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Coût moyen / trimestre</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ $coutParTrimestre !== null ? number_format($coutParTrimestre, 0, ',', ' ') : '—' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Répartition indicative</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Structure responsable</p>
                <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white">{{ $activite->departement->nom ?? '—' }}</p>
                @if ($hierarchie->count() > 1)
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hierarchie->join(' › ') }}</p>
                @endif
                @if ($activite->departement?->responsable)
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Responsable : {{ $activite->departement->responsable->name }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            {{-- Fiche de programmation --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Fiche de programmation</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Éléments saisis lors de la planification de l'activité.</p>

                <dl class="mt-5 grid grid-cols-1 gap-x-6 gap-y-5 text-sm sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Intitulé de l'activité</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $activite->nom_activite }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Indicateur objectivement vérifiable</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $activite->indicateur_objectivement_verifiable ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Moyen de vérification</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $activite->moyen_verification ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Coût programmé</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ number_format($activite->cout, 0, ',', ' ') }} FCFA</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Trimestres planifiés</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $activite->trimestres_selectionnes ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Extrant de rattachement</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">
                            {{ $activite->extrant ? $activite->extrant->code.' — '.$activite->extrant->libelle : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Structures intervenantes</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">
                            {{ $activite->departements->isNotEmpty() ? $activite->departements->pluck('nom')->join(' / ') : '—' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Commentaires</dt>
                        <dd class="mt-1 whitespace-pre-line font-medium text-slate-900 dark:text-white">{{ $activite->commentaires ?: 'Aucun commentaire' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Traçabilité de la saisie --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Saisie &amp; validation</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Qui a saisi, soumis et validé cette programmation.</p>

                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Saisie</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $activite->saisiePar->name ?? '—' }}</dd>
                        <dd class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $activite->date_saisie ? 'le '.$activite->date_saisie->format('d/m/Y') : 'date non renseignée' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Soumission</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">
                            {{ $activite->date_soumission ? $activite->date_soumission->format('d/m/Y à H:i') : 'Non soumise' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Validation</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">
                            {{ $activite->validePar->name ?? ($activite->statut === 'valide' ? 'Validée' : 'En attente') }}
                        </dd>
                        <dd class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $activite->date_validation ? 'le '.$activite->date_validation->format('d/m/Y à H:i') : '' }}
                        </dd>
                    </div>
                    <div class="border-t border-slate-200 pt-4 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        <p>Créée le {{ $activite->created_at?->format('d/m/Y à H:i') }}</p>
                        <p>Dernière modification le {{ $activite->updated_at?->format('d/m/Y à H:i') }}</p>
                    </div>
                </dl>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            {{-- Pièces jointes --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Pièces jointes</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Documents justificatifs associés à l'activité.</p>

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

                @if (auth()->user()->canAny(['edit_activites', 'validate_activites', 'evaluate_activites']))
                    <form action="{{ route('activites.pieces-jointes.store', $activite) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                        @csrf
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                <label for="fichier" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Ajouter un fichier <span class="text-xs font-normal text-slate-500">(max 10 Mo)</span></label>
                                <input id="fichier" type="file" name="fichier" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                @error('fichier')
                                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="description" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Observation liée au fichier</label>
                                <input id="description" type="text" name="description" maxlength="255" value="{{ old('description') }}"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                    placeholder="Exemple : PV signé, rapport de mission">
                                @error('description')
                                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                            Envoyer le fichier
                        </button>
                    </form>
                @endif
            </div>

            {{-- Historique --}}
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
