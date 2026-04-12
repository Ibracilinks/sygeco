<x-layouts::app :title="__('Activité : ' . $activite->nom_activite)">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl overflow-y-auto p-6">

        <!-- En-tête avec navigation -->
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="h-16 w-16 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $activite->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ $activite->is_active ? '✓ Actif' : '✗ Inactif' }}
                        </span>
                        <span
                            class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            Ordre {{ $activite->ordre }}
                        </span>
                    </div>
                    <h1 class="text-2xl font-bold dark:text-white">{{ $activite->nom_activite }}</h1>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-1">Code: {{ $activite->code }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                @can('edit_activites')
                    <a href="{{ route('activites.edit', $activite) }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Modifier
                    </a>
                @endcan
                <a href="{{ route('activites.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        <!-- Ligne 1: KPIs de l'activité -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Budget</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">
                            {{ number_format($activite->cout / 1000000, 1) }} M</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">FCFA</p>
                    </div>
                    <div class="rounded-full bg-blue-200 dark:bg-blue-800 p-3">
                        <svg class="w-6 h-6 text-blue-700 dark:text-blue-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-950 dark:to-green-900 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-700 dark:text-green-300">Indicateur</p>
                        <p class="text-lg font-bold text-green-900 dark:text-green-100 mt-1">
                            {{ Str::limit($activite->indicateur_objectivement_verifiable, 40) }}</p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">Objectivement vérifiable</p>
                    </div>
                    <div class="rounded-full bg-green-200 dark:bg-green-800 p-3">
                        <svg class="w-6 h-6 text-green-700 dark:text-green-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-950 dark:to-purple-900 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-700 dark:text-purple-300">Statut</p>
                        <div class="mt-1">
                            <span
                                class="px-3 py-1 text-sm rounded-full
                                {{ $activite->statut == 'valide'
                                    ? 'bg-green-500 text-white'
                                    : ($activite->statut == 'soumis'
                                        ? 'bg-yellow-500 text-white'
                                        : 'bg-gray-500 text-white') }}">
                                {{ $activite->statut_label }}
                            </span>
                        </div>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mt-2">État d'avancement</p>
                    </div>
                    <div class="rounded-full bg-purple-200 dark:bg-purple-800 p-3">
                        <svg class="w-6 h-6 text-purple-700 dark:text-purple-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-950 dark:to-orange-900 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-orange-700 dark:text-orange-300">Trimestres</p>
                        <div class="flex gap-1 mt-1">
                            @if ($activite->trimestre_1 == 'oui')
                                <span
                                    class="px-2 py-1 text-xs rounded bg-orange-200 dark:bg-orange-800 text-orange-800 dark:text-orange-200">T1</span>
                            @endif
                            @if ($activite->trimestre_2 == 'oui')
                                <span
                                    class="px-2 py-1 text-xs rounded bg-orange-200 dark:bg-orange-800 text-orange-800 dark:text-orange-200">T2</span>
                            @endif
                            @if ($activite->trimestre_3 == 'oui')
                                <span
                                    class="px-2 py-1 text-xs rounded bg-orange-200 dark:bg-orange-800 text-orange-800 dark:text-orange-200">T3</span>
                            @endif
                            @if ($activite->trimestre_4 == 'oui')
                                <span
                                    class="px-2 py-1 text-xs rounded bg-orange-200 dark:bg-orange-800 text-orange-800 dark:text-orange-200">T4</span>
                            @endif
                        </div>
                        <p class="text-xs text-orange-600 dark:text-orange-400 mt-2">Périodes concernées</p>
                    </div>
                    <div class="rounded-full bg-orange-200 dark:bg-orange-800 p-3">
                        <svg class="w-6 h-6 text-orange-700 dark:text-orange-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ligne 2: Informations détaillées -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Description et indicateurs -->
            <div
                class="lg:col-span-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Description détaillée</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-zinc-500">Libellé complet</div>
                        <div class="dark:text-white">{{ $activite->libelle }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Description</div>
                        <div class="dark:text-white">{{ $activite->description ?? 'Aucune description' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Indicateur objectivement vérifiable</div>
                        <div class="dark:text-white font-medium text-indigo-600 dark:text-indigo-400">
                            {{ $activite->indicateur_objectivement_verifiable }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-zinc-500">Moyen de vérification</div>
                        <div class="dark:text-white">{{ $activite->moyen_verification }}</div>
                    </div>
                    @if ($activite->commentaires)
                        <div>
                            <div class="text-sm text-zinc-500">Commentaires</div>
                            <div class="dark:text-white">{{ $activite->commentaires }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Hiérarchie -->
            <div class="space-y-6">
                <!-- Extrant -->
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                    <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                        <h2 class="text-lg font-semibold dark:text-white">Extrant associé</h2>
                    </div>
                    <div class="p-6">
                        @if ($activite->extrant)
                            <div class="text-sm text-zinc-500">Code</div>
                            <div class="font-mono dark:text-white">{{ $activite->extrant->code }}</div>
                            <div class="text-sm text-zinc-500 mt-3">Libellé</div>
                            <div class="dark:text-white">{{ Str::limit($activite->extrant->libelle, 80) }}</div>
                            <div class="mt-4">
                                <a href="{{ route('extrants.show', $activite->extrant) }}"
                                    class="text-indigo-600 hover:underline text-sm inline-flex items-center gap-1">
                                    Voir l'extrant →
                                </a>
                            </div>
                        @else
                            <p class="text-zinc-500">Non associé</p>
                        @endif
                    </div>
                </div>

                <!-- Département -->
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                    <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                        <h2 class="text-lg font-semibold dark:text-white">Département responsable</h2>
                    </div>
                    <div class="p-6">
                        @if ($activite->departement)
                            <div class="text-sm text-zinc-500">Nom</div>
                            <div class="font-medium dark:text-white">{{ $activite->departement->nom }}</div>
                            @if ($activite->departement->responsable)
                                <div class="text-sm text-zinc-500 mt-3">Responsable</div>
                                <div class="dark:text-white">{{ $activite->departement->responsable->name }}</div>
                            @endif
                        @else
                            <p class="text-zinc-500">Non assigné</p>
                        @endif
                    </div>
                </div>

                <!-- Métadonnées -->
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                    <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                        <h2 class="text-lg font-semibold dark:text-white">Métadonnées</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500">Saisi par</span>
                            <span class="text-sm dark:text-white">{{ $activite->saisiePar->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500">Date de saisie</span>
                            <span
                                class="text-sm dark:text-white">{{ $activite->date_saisie ? $activite->date_saisie->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500">Créé le</span>
                            <span
                                class="text-sm dark:text-white">{{ $activite->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500">Dernière modification</span>
                            <span
                                class="text-sm dark:text-white">{{ $activite->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ligne 3: Actions selon statut -->
        @if ($activite->statut == 'brouillon')
            <div
                class="rounded-xl border border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-950 p-6">
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-yellow-100 dark:bg-yellow-900 p-2">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold dark:text-white">Cette activité est en brouillon</h3>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">Soumettez-la pour validation par la
                                DBCGOQ</p>
                        </div>
                    </div>
                    <form action="{{ route('activites.soumettre', $activite) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Soumettre l'activité
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if ($activite->statut == 'soumis' && auth()->user()->hasRole('dbcgoq'))
            <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950 p-6">
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-2">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold dark:text-white">Activité en attente de validation</h3>
                            <p class="text-sm text-blue-700 dark:text-blue-300">Validez cette activité pour finaliser
                                le processus</p>
                        </div>
                    </div>
                    <form action="{{ route('activites.valider', $activite) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Valider l'activité
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Ligne 4: Objectif parent -->
        @if ($activite->extrant && $activite->extrant->objectif)
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Objectif stratégique associé</h2>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="text-sm text-zinc-500">Code</div>
                            <div class="font-mono dark:text-white">{{ $activite->extrant->objectif->code ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-zinc-500 mt-3">Libellé</div>
                            <div class="dark:text-white">{{ $activite->extrant->objectif->libelle ?? 'N/A' }}</div>
                            <div class="text-sm text-zinc-500 mt-3">Année</div>
                            <div class="dark:text-white">{{ $activite->extrant->objectif->annee ?? 'N/A' }}</div>
                        </div>
                        <a href="{{ route('objectifs.show', $activite->extrant->objectif_id) }}"
                            class="text-indigo-600 hover:underline text-sm inline-flex items-center gap-1">
                            Voir l'objectif →
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ligne 5: Résultat parent -->
        @if ($activite->extrant && $activite->extrant->resultat)
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                    <h2 class="text-lg font-semibold dark:text-white">Résultat stratégique associé</h2>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="text-sm text-zinc-500">Code</div>
                            <div class="font-mono dark:text-white">{{ $activite->extrant->resultat->code ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-zinc-500 mt-3">Libellé</div>
                            <div class="dark:text-white">{{ $activite->extrant->resultat->libelle ?? 'N/A' }}</div>
                        </div>
                        <a href="{{ route('resultats.show', $activite->extrant->resultat_id) }}"
                            class="text-indigo-600 hover:underline text-sm inline-flex items-center gap-1">
                            Voir le résultat →
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layouts::app>
