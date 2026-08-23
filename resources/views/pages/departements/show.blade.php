<x-layouts::app title="Détails de l'entité">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                @php $ancetres = $departement->ancetres(); @endphp
                @if ($ancetres->isNotEmpty())
                    <nav class="mb-1 flex flex-wrap items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                        @foreach ($ancetres as $ancetre)
                            <a href="{{ route('departements.show', $ancetre) }}" class="hover:underline">{{ $ancetre->nom }}</a>
                            <span class="text-slate-300 dark:text-slate-600">›</span>
                        @endforeach
                        <span class="text-slate-700 dark:text-slate-300">{{ $departement->nom }}</span>
                    </nav>
                @endif
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $departement->nom }}</h1>
                    @include('pages.departements.partials.type-badge', ['type' => $departement->type])
                </div>
                <p class="font-mono text-sm text-slate-500 dark:text-slate-400">{{ $departement->code }}</p>
            </div>
            <div class="flex gap-2">
                @can('edit_departements')
                    <a href="{{ route('departements.edit', $departement) }}"
                        class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-500">
                        Modifier
                    </a>
                @endcan
                <a href="{{ route('departements.index') }}"
                    class="inline-flex items-center rounded-lg bg-slate-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600">
                    Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Utilisateurs</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($departement->users_count) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($departement->activites_count) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</p>
                <p class="mt-2 text-sm font-semibold {{ $departement->is_active ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                    {{ $departement->is_active ? 'Actif' : 'Inactif' }}
                </p>
            </div>
        </div>

        {{-- Analytique de l'exercice actif, agrégée sur le sous-arbre de l'entité --}}
        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-1 border-b border-slate-200 px-6 py-4 dark:border-slate-700 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                        Analytique — exercice {{ $analytique['exercice']->annee ?? '—' }}
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Périmètre : cette entité et ses {{ $analytique['nb_entites'] - 1 }} entité(s) rattachée(s).
                    </p>
                </div>
            </div>

            @if (! $analytique['exercice'])
                <p class="p-6 text-sm text-slate-500 dark:text-slate-400">Aucun exercice actif : sélectionnez un exercice pour afficher l'analytique.</p>
            @elseif ($analytique['nb_activites'] === 0)
                <p class="p-6 text-sm text-slate-500 dark:text-slate-400">Aucune activité programmée sur cet exercice pour ce périmètre.</p>
            @else
                {{-- Chaîne du cadre logique effectivement portée par le périmètre --}}
                <div class="grid grid-cols-2 gap-4 border-b border-slate-200 p-6 dark:border-slate-700 lg:grid-cols-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Résultats couverts</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $analytique['nb_resultats'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Extrants couverts</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $analytique['nb_extrants'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $analytique['nb_activites'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Taux de validation</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($analytique['taux_validation'], 1, ',', ' ') }}%</p>
                    </div>
                </div>

                {{-- Budget et circuit de validation --}}
                <div class="grid grid-cols-1 gap-6 border-b border-slate-200 p-6 dark:border-slate-700 lg:grid-cols-2">
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Budget</p>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Planifié</dt>
                                <dd class="font-semibold text-slate-900 dark:text-white">{{ number_format($analytique['budget_planifie'], 0, ',', ' ') }} FCFA</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Consommé (évalué)</dt>
                                <dd class="font-semibold text-slate-900 dark:text-white">{{ number_format($analytique['budget_consomme'], 0, ',', ' ') }} FCFA</dd>
                            </div>
                        </dl>
                        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, $analytique['taux_consommation']) }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($analytique['taux_consommation'], 1, ',', ' ') }}% du budget planifié.</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Circuit de validation</p>
                        @php
                            $libellesStatut = ['brouillon' => 'Brouillon', 'en_attente' => 'En attente', 'valide' => 'Validé', 'rejete' => 'Rejeté'];
                            $libellesExecution = ['realise' => 'Réalisée', 'en_cours' => 'En cours', 'non_realise' => 'Non réalisée', 'non_evaluee' => 'Non évaluée'];
                        @endphp
                        <dl class="mt-3 space-y-2 text-sm">
                            @foreach ($analytique['statuts'] as $statut => $nombre)
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-slate-400">{{ $libellesStatut[$statut] }}</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">{{ $nombre }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <p class="mt-4 text-sm font-medium text-slate-700 dark:text-slate-200">Exécution des validées (fin d'année)</p>
                        <dl class="mt-3 space-y-2 text-sm">
                            @foreach ($analytique['execution'] as $statut => $nombre)
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-slate-400">{{ $libellesExecution[$statut] }}</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">{{ $nombre }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>

                {{-- Contribution de chaque entité du sous-arbre --}}
                @if ($analytique['par_entite']->count() > 1)
                    <div class="overflow-x-auto p-6">
                        <p class="mb-3 text-sm font-medium text-slate-700 dark:text-slate-200">Ventilation par entité</p>
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Entité</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Validées</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Budget</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($analytique['par_entite'] as $ligne)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-100">{{ $ligne['nom'] }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $ligne['nb_activites'] }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $ligne['validees'] }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ number_format($ligne['budget'], 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Informations générales</h2>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Description</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->description ?: 'Aucune description' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Ordre d'affichage</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->ordre ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Créé le</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ optional($departement->created_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Mis à jour le</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ optional($departement->updated_at)->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Responsable</h2>
                </div>
                <div class="space-y-4 p-6 text-slate-800 dark:text-slate-100">
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Nom</div>
                        <div>{{ $departement->responsable?->name ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Email</div>
                        <div>{{ $departement->responsable?->email ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Téléphone</div>
                        <div>{{ $departement->responsable?->telephone ?? 'Non défini' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if ($departement->peutAvoirDesEnfants())
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                        Sous-entités ({{ $departement->enfants_count }})
                    </h2>
                </div>
                <div class="p-6">
                    @if ($departement->enfants->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucune entité rattachée pour le moment.</p>
                    @else
                        <ul class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($departement->enfants as $enfant)
                                <li class="flex flex-wrap items-center gap-2 py-2">
                                    <span class="font-mono text-xs text-slate-400 dark:text-slate-500">{{ $enfant->code }}</span>
                                    <a href="{{ route('departements.show', $enfant) }}" class="text-sm font-medium text-slate-800 hover:underline dark:text-slate-100">{{ $enfant->nom }}</a>
                                    @include('pages.departements.partials.type-badge', ['type' => $enfant->type])
                                    @unless ($enfant->is_active)
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Inactif</span>
                                    @endunless
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Activités récentes</h2>
            </div>
            <div class="p-6">
                @if ($recentActivites->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucune activité associée pour le moment.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Activité</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Coût</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Création</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($recentActivites as $activite)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-100">{{ \Illuminate\Support\Str::limit($activite->nom_activite, 80) }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ ucfirst($activite->statut) }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ number_format((float) $activite->cout, 0, ',', ' ') }} FCFA</td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ optional($activite->created_at)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        @if ($departement->users->isNotEmpty())
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Utilisateurs associés ({{ $departement->users->count() }})</h2>
                </div>
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-950">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Nom</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($departement->users as $user)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-100">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $user->email }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
