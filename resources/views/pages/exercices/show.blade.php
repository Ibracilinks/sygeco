<x-layouts::app title="Exercice {{ $exercice->annee }}">
    @php
        $statutColors = [
            'brouillon' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-100',
            'actif' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
            'cloture' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-100',
        ];
        $jours = $exercice->joursAvantLimite();
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        {{-- En-tête --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Exercice {{ $exercice->annee }}</h1>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statutColors[$exercice->statut] ?? $statutColors['brouillon'] }}">{{ ucfirst($exercice->statut) }}</span>
                    @if ($isActiveContext)
                        <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-800 dark:bg-sky-900/40 dark:text-sky-200">Contexte actif</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Du {{ $exercice->date_debut->format('d/m/Y') }} au {{ $exercice->date_fin->format('d/m/Y') }}
                    • {{ $exercice->date_debut->diffInDays($exercice->date_fin) + 1 }} jours
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form action="{{ route('exercices.activate', $exercice) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500">Utiliser cet exercice</button>
                </form>
                <a href="{{ route('exercices.export', $exercice) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Exporter (Excel)
                </a>
                @can('manage_exercices')
                    <a href="{{ route('exercices.edit', $exercice) }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-400">Modifier</a>
                @endcan
                <a href="{{ route('exercices.index') }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Retour</a>
            </div>
        </div>

        {{-- Cartes KPI --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Objectifs</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $stats['nb_objectifs'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Résultats</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $stats['nb_resultats'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Extrants</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $stats['nb_extrants'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $stats['nb_activites'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 col-span-2">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Budget total</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($stats['budget_total'], 0, ',', ' ') }} <span class="text-sm font-normal text-slate-500">FCFA</span></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            {{-- Colonne principale --}}
            <div class="flex flex-col gap-5 xl:col-span-2">
                {{-- Fenêtre de saisie --}}
                <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Fenêtre de saisie</h2>
                    <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Ouverture</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ optional($exercice->date_ouverture_saisie)->format('d/m/Y') ?? 'Non définie' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Date limite</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ optional($exercice->date_limite_saisie)->format('d/m/Y') ?? 'Non définie' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Échéance</dt>
                            <dd class="mt-1 text-sm font-medium {{ $jours !== null && $jours < 0 ? 'text-rose-700 dark:text-rose-300' : 'text-slate-900 dark:text-white' }}">
                                @if ($jours === null)
                                    —
                                @elseif ($jours < 0)
                                    Délai dépassé
                                @elseif ($jours === 0)
                                    Dernier jour
                                @else
                                    J-{{ $jours }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                        Notification d'ouverture :
                        @if ($exercice->ouverture_notifiee_le)
                            envoyée le {{ $exercice->ouverture_notifiee_le->format('d/m/Y à H:i') }}
                        @else
                            non envoyée
                        @endif
                    </p>

                    {{-- Relances --}}
                    <div class="mt-5">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Relances envoyées</h3>
                        @if ($relances->isEmpty())
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Aucune relance émise pour le moment.</p>
                        @else
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($relances as $relance)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-950/40 dark:text-slate-200">
                                        <span class="font-semibold">{{ $relance->palier === 0 ? 'Jour J' : 'J-' . $relance->palier }}</span>
                                        @if ($relance->envoye_le)
                                            • {{ $relance->envoye_le->format('d/m/Y') }} • {{ $relance->destinataires }} dest.
                                        @else
                                            • marqué (non envoyé)
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Objectifs --}}
                <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Objectifs de l'exercice</h2>
                    @if ($objectifs->isEmpty())
                        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Aucun objectif rattaché à cet exercice.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($objectifs as $objectif)
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/60">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $objectif->code }}</p>
                                            <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ Str::limit($objectif->libelle, 120) }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $objectif->resultats_count }} résultats • {{ $objectif->extrants_count }} extrants</p>
                                        </div>
                                        <x-actions.view :href="route('objectifs.show', $objectif)" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Colonne latérale --}}
            <div class="flex flex-col gap-5">
                {{-- Répartition des activités --}}
                <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Activités</h2>
                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300"><span class="h-2 w-2 rounded-full bg-slate-400"></span>Brouillon</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $stats['activites_par_statut']['brouillon'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300"><span class="h-2 w-2 rounded-full bg-blue-500"></span>Soumis</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $stats['activites_par_statut']['soumis'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Validé</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $stats['activites_par_statut']['valide'] }}</span>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Avancement</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Réalisé</span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $stats['execution']['realise'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300"><span class="h-2 w-2 rounded-full bg-amber-500"></span>En cours</span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $stats['execution']['en_cours'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300"><span class="h-2 w-2 rounded-full bg-slate-400"></span>Non réalisé</span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $stats['execution']['non_realise'] }}</span>
                            </div>
                        </div>
                    </div>
                    <dl class="mt-4 border-t border-slate-200 pt-4 text-sm dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Taux de réalisation</dt>
                            <dd class="font-semibold text-emerald-700 dark:text-emerald-300">{{ $stats['taux_realisation'] }}%</dd>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Budget moyen / activité</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">{{ $stats['nb_activites'] > 0 ? number_format($stats['budget_total'] / $stats['nb_activites'], 0, ',', ' ') : '0' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Informations --}}
                <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Informations</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Année</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">{{ $exercice->annee }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Statut</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">{{ ucfirst($exercice->statut) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Créé le</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">{{ optional($exercice->created_at)->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Mis à jour le</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">{{ optional($exercice->updated_at)->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Analyse graphique --}}
        @php
            $hasActivites = $stats['nb_activites'] > 0;
            $extrantData = collect($charts['activites_par_extrant']['labels'])
                ->map(fn ($l, $i) => ['label' => $l, 'value' => $charts['activites_par_extrant']['values'][$i]])
                ->all();
            $palette = ['#5c6682', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];
        @endphp

        <div class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-700">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Analyse graphique</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Représentation des objectifs, résultats, extrants et activités de l'exercice.</p>
            </div>
        </div>

        @unless ($hasActivites)
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950/40 dark:text-slate-400">
                Aucune activité rattachée à cet exercice : les graphiques basés sur les activités s'afficheront dès la première saisie.
            </div>
        @endunless

        {{-- Structure + répartitions principales --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Structure de l'exercice</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Volume par niveau du cadre logique</p>
                </div>
                <x-charts.bar-chart :labels="$charts['structure']['labels']" :datasets="[
                    [
                        'label' => 'Nombre',
                        'data' => $charts['structure']['values'],
                        'backgroundColor' => ['#5c6682', '#0ea5e9', '#10b981', '#8b5cf6'],
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Extrants par résultat</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Nombre d'extrants rattachés à chaque résultat stratégique</p>
                </div>
                <x-charts.bar-chart :labels="$charts['extrants_par_resultat']['labels']" :datasets="[
                    [
                        'label' => 'Extrants',
                        'data' => $charts['extrants_par_resultat']['values'],
                        'backgroundColor' => '#0ea5e9',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </article>
        </section>

        {{-- Budgets --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Budget par objectif</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Montants en millions FCFA</p>
                </div>
                <x-charts.bar-chart :labels="$charts['budget_par_objectif']['labels']" :datasets="[
                    [
                        'label' => 'Budget (M FCFA)',
                        'data' => $charts['budget_par_objectif']['values'],
                        'backgroundColor' => '#5c6682',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Budget par résultat</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Répartition du budget en millions FCFA</p>
                </div>
                <x-charts.pie-chart :labels="$charts['budget_par_resultat']['labels']" :data="$charts['budget_par_resultat']['values']" type="donut" :colors="$palette" :height="300" />
            </article>
        </section>

        {{-- Activités --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Activités par statut</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">État d'avancement de la saisie</p>
                </div>
                <x-charts.pie-chart :labels="$charts['activites_statut']['labels']" :data="$charts['activites_statut']['values']" type="donut" :colors="['#94a3b8', '#3b82f6', '#10b981']" :height="300" />
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Activités par trimestre</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Planification trimestrielle déclarée</p>
                </div>
                <x-charts.bar-chart :labels="$charts['activites_trimestre']['labels']" :datasets="[
                    [
                        'label' => 'Activités',
                        'data' => $charts['activites_trimestre']['values'],
                        'backgroundColor' => '#8b5cf6',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </article>
        </section>

        {{-- Avancement (suivi d'exécution) --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Avancement des activités</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Suivi d'exécution : réalisé / en cours / non réalisé</p>
                </div>
                <x-charts.pie-chart :labels="$charts['avancement']['labels']" :data="$charts['avancement']['values']" type="donut" :colors="['#10b981', '#f59e0b', '#94a3b8']" :height="300" />
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Taux de réalisation</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Part des activités réalisées</p>
                </div>
                <x-charts.gauge-chart :value="$stats['taux_realisation']" title="Réalisé" unit="%" :size="200" />
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Activités par extrant</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Top 10 des extrants par nombre d'activités</p>
                </div>
                @if (count($extrantData))
                    <x-charts.horizontal-bar :data="$extrantData" :height="360" />
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucune donnée.</p>
                @endif
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Distribution budgétaire</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Nombre d'activités par tranche de coût</p>
                </div>
                <x-charts.pie-chart :labels="$charts['distribution_budgetaire']['labels']" :data="$charts['distribution_budgetaire']['values']" type="pie" :colors="$palette" :height="300" />
            </article>
        </section>
    </div>
</x-layouts::app>
