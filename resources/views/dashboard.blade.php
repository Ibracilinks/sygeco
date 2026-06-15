<x-layouts::app title="Tableau de bord">

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <div class="flex h-full w-full flex-1 flex-col gap-6 overflow-y-auto p-6">
        <section class="rounded-2xl border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Pilotage strategique</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">Tableau de bord</h1>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        Vue consolidee des activites, du budget et des points de vigilance pour l'annee {{ $filters['selected_year'] }}.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <select id="annee-select" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        @foreach ($filters['year_options'] as $option)
                            <option value="{{ $option }}" @selected($option === $filters['selected_year'])>{{ $option }}</option>
                        @endforeach
                    </select>
                    <button onclick="window.print()" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                        Exporter PDF
                    </button>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-charts.kpi-card title="Objectifs" :value="$kpis['objectifs']" trend="up" color="blue" icon="document-text" />
            <x-charts.kpi-card title="Extrants" :value="$kpis['extrants']" trend="up" color="green" icon="folder" />
            <x-charts.kpi-card title="Activites" :value="$kpis['activites']" trend="up" color="purple" icon="clipboard-document-list" />
            <x-charts.kpi-card title="Budget total" :value="$kpis['budget_total']" unit="M FCFA" trend="up" color="yellow" icon="currency-dollar" divisor="1000000" decimals="1" />
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/90 p-5 dark:border-emerald-900/60 dark:bg-emerald-950/25">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Qualite execution</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-200">{{ number_format($kpis['taux_realisation'], 1, ',', ' ') }}%</p>
                <p class="mt-1 text-sm text-emerald-700/90 dark:text-emerald-300/90">Taux de realisation global</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50/90 p-5 dark:border-amber-900/60 dark:bg-amber-950/25">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Backlog</p>
                <p class="mt-2 text-3xl font-semibold text-amber-800 dark:text-amber-200">{{ number_format($kpis['en_attente'], 0, ',', ' ') }}</p>
                <p class="mt-1 text-sm text-amber-700/90 dark:text-amber-300/90">Activites en brouillon ou soumises</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50/90 p-5 dark:border-sky-900/60 dark:bg-sky-950/25">
                <p class="text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Soumission moyenne</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-200">{{ number_format($insights['soumission_moyenne'], 1, ',', ' ') }}%</p>
                <p class="mt-1 text-sm text-sky-700/90 dark:text-sky-300/90">Par departement sur l'exercice actif</p>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <article class="lg:col-span-2 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Evolution mensuelle</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Volume des activites creees et dynamique budgetaire</p>
                </div>
                <x-charts.line-chart :labels="$charts['evolution']['labels']" :datasets="[
                    [
                        'label' => 'Activites',
                        'data' => $charts['evolution']['activites'],
                        'borderColor' => '#5c6682',
                        'backgroundColor' => 'rgba(92, 102, 130, 0.15)',
                        'tension' => 0.35,
                        'fill' => true,
                    ],
                    [
                        'label' => 'Budget (M FCFA)',
                        'data' => $charts['evolution']['budget_millions'],
                        'borderColor' => '#0ea5e9',
                        'backgroundColor' => 'rgba(14, 165, 233, 0.08)',
                        'tension' => 0.3,
                        'fill' => false,
                    ],
                ]" :height="300" />
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Realisation</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Part des activites validees</p>
                </div>
                <x-charts.gauge-chart :value="$kpis['taux_realisation']" title="Taux de completion" unit="%" :size="210" />
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Budget par objectif</h2>
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
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Top extrants</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Distribution des activites par extrant</p>
                </div>
                <x-charts.pie-chart :labels="$charts['top_extrants']['labels']" :data="$charts['top_extrants']['values']" type="pie" :height="300" />
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Top activites par cout</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Les activites les plus consommatrices du budget</p>
                </div>
                <div class="space-y-3">
                    @forelse ($tables['top_activites'] as $item)
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                <p class="truncate text-slate-700 dark:text-slate-100">{{ $item['code'] }} - {{ Str::limit($item['nom_activite'], 42) }}</p>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ number_format($item['cout_millions'], 1, ',', ' ') }} M</p>
                            </div>
                            <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-2 rounded-full bg-linear-to-r from-slate-500 to-slate-700 dark:from-slate-300 dark:to-slate-100" style="width: {{ $item['ratio'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucune activite disponible.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Distribution budgetaire</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Nombre d'activites par tranche de cout</p>
                </div>
                <x-charts.pie-chart :labels="$charts['distribution_budgetaire']['labels']" :data="$charts['distribution_budgetaire']['values']" type="donut" :height="300" />
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Activites par statut</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Distribution de l'etat d'avancement</p>
                </div>
                <x-charts.bar-chart :labels="$charts['activites_statut']['labels']" :datasets="[
                    [
                        'label' => 'Activites',
                        'data' => $charts['activites_statut']['values'],
                        'backgroundColor' => ['#f59e0b', '#3b82f6', '#10b981'],
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Activites par trimestre</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Planification trimestrielle declaree</p>
                </div>
                <x-charts.bar-chart :labels="$charts['activites_trimestre']['labels']" :datasets="[
                    [
                        'label' => 'Activites',
                        'data' => $charts['activites_trimestre']['values'],
                        'backgroundColor' => '#0f766e',
                        'borderRadius' => 8,
                    ],
                ]" :height="300" />
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <article class="xl:col-span-2 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Soumission par departement</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Progression des activites soumises ou validees</p>
                </div>
                <div class="space-y-3">
                    @forelse ($tables['soumission_departements'] as $row)
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                <p class="truncate text-slate-700 dark:text-slate-100">{{ $row['nom'] }}</p>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $row['soumises'] }}/{{ $row['total'] }} ({{ number_format($row['pct'], 1, ',', ' ') }}%)</p>
                            </div>
                            <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-2 rounded-full bg-emerald-500" style="width: {{ min(100, $row['pct']) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucune donnee de soumission disponible.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 border-b border-slate-200 pb-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Departements en retard</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $insights['departements_en_retard'] }} departement(s) a suivre</p>
                </div>
                @forelse ($tables['departements_en_retard'] as $retard)
                    <div class="mb-2 flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2 text-sm dark:bg-amber-900/20">
                        <p class="text-slate-700 dark:text-slate-100">{{ $retard['nom'] }}</p>
                        <span class="rounded-full bg-amber-200 px-2 py-0.5 text-xs font-semibold text-amber-900 dark:bg-amber-800/70 dark:text-amber-100">{{ $retard['nb_brouillon'] }} brouillon(s)</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucun departement en retard sur cet exercice.</p>
                @endforelse
            </article>
        </section>
    </div>

    <script>
        const anneeSelect = document.getElementById('annee-select');

        if (anneeSelect) {
            anneeSelect.addEventListener('change', (event) => {
                const url = new URL(window.location.href);
                url.searchParams.set('annee', event.target.value);
                window.location.href = url.toString();
            });
        }
    </script>
</x-layouts::app>
