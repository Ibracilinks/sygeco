@php
    $sac = [
        'filters'  => $filters ?? ['selected_year' => null, 'year_options' => []],
        'kpis'     => $kpis ?? [],
        'insights' => $insights ?? [],
        'charts'   => $charts ?? [],
        'tables'   => $tables ?? [],
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SAP Analytics Cloud — {{ config('app.name') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --sac-shell: #354a5f;
            --sac-shell-2: #2b3c4e;
            --sac-brand: #0a6ed1;
            --sac-brand-d: #0854a0;
            --sac-bg: #f5f6f7;
            --sac-tile: #ffffff;
            --sac-border: #e3e4e6;
            --sac-border-strong: #d1d3d6;
            --sac-text: #32363a;
            --sac-muted: #6a6d70;
            --sac-green: #107e3e;
            --sac-orange: #e9730c;
            --sac-red: #bb0000;
            --sac-radius: 8px;
            --sac-shadow: 0 1px 2px rgba(0,0,0,.06), 0 0 1px rgba(0,0,0,.08);
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: "72", "72full", "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            background: var(--sac-bg);
            color: var(--sac-text);
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
        }

        /* ---------- Shell bar ---------- */
        .sac-shell {
            height: 44px;
            background: var(--sac-shell);
            color: #fff;
            display: flex;
            align-items: center;
            padding: 0 12px;
            gap: 12px;
            position: sticky; top: 0; z-index: 50;
        }
        .sac-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            letter-spacing: .5px;
            font-size: 15px;
            background: linear-gradient(180deg, #1f9bf0, #0a6ed1 55%, #0854a0);
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.18);
        }
        .sac-product { font-size: 14px; font-weight: 600; opacity: .96; white-space: nowrap; }
        .sac-product small { font-weight: 400; opacity: .7; }
        .sac-shell .sac-search {
            flex: 1;
            max-width: 460px;
            margin: 0 auto;
        }
        .sac-shell .sac-search input {
            width: 100%;
            height: 28px;
            border-radius: 14px;
            border: none;
            padding: 0 14px;
            background: rgba(255,255,255,.14);
            color: #fff;
            font-size: 13px;
            outline: none;
        }
        .sac-shell .sac-search input::placeholder { color: rgba(255,255,255,.6); }
        .sac-shell-actions { display: flex; align-items: center; gap: 6px; margin-left: auto; }
        .sac-icon-btn {
            width: 32px; height: 32px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; background: transparent; border: none; cursor: pointer;
        }
        .sac-icon-btn:hover { background: rgba(255,255,255,.12); }
        .sac-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: #e9730c; color: #fff; font-weight: 600; font-size: 12px;
            display: inline-flex; align-items: center; justify-content: center;
        }

        /* ---------- Story header / toolbar ---------- */
        .sac-storybar {
            background: #fff;
            border-bottom: 1px solid var(--sac-border);
            padding: 0 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            height: 48px;
            position: sticky; top: 44px; z-index: 40;
        }
        .sac-story-title { font-size: 15px; font-weight: 700; color: var(--sac-text); white-space: nowrap; }
        .sac-story-title span { display:block; font-size: 11px; font-weight: 400; color: var(--sac-muted); }
        .sac-tabs { display: flex; align-items: stretch; height: 100%; margin-left: 8px; }
        .sac-tab {
            border: none; background: transparent; cursor: pointer;
            padding: 0 16px; height: 100%;
            font-size: 13px; color: var(--sac-muted);
            border-bottom: 3px solid transparent;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .sac-tab:hover { color: var(--sac-text); background: #f7f7f7; }
        .sac-tab.active { color: var(--sac-brand); border-bottom-color: var(--sac-brand); font-weight: 600; }
        .sac-toolbar { display: flex; align-items: center; gap: 8px; margin-left: auto; }
        .sac-select, .sac-btn {
            height: 30px; border: 1px solid var(--sac-border-strong); background: #fff;
            border-radius: 6px; font-size: 13px; color: var(--sac-text); padding: 0 10px; cursor: pointer;
        }
        .sac-btn:hover, .sac-select:hover { border-color: var(--sac-brand); }
        .sac-btn.primary { background: var(--sac-brand); border-color: var(--sac-brand); color: #fff; }
        .sac-btn.primary:hover { background: var(--sac-brand-d); }

        /* ---------- Filter chips bar ---------- */
        .sac-filterbar {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 16px; background: #eef0f2; border-bottom: 1px solid var(--sac-border);
            font-size: 12px; color: var(--sac-muted);
        }
        .sac-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fff; border: 1px solid var(--sac-border-strong);
            border-radius: 14px; padding: 3px 10px; color: var(--sac-text);
        }
        .sac-chip b { color: var(--sac-brand); }

        /* ---------- Canvas ---------- */
        .sac-canvas { padding: 16px; }
        .sac-page { display: none; }
        .sac-page.active { display: block; }
        .sac-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 14px;
        }
        .sac-tile {
            background: var(--sac-tile);
            border: 1px solid var(--sac-border);
            border-radius: var(--sac-radius);
            box-shadow: var(--sac-shadow);
            display: flex; flex-direction: column;
            min-height: 0;
        }
        .sac-tile-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 14px; border-bottom: 1px solid var(--sac-border);
        }
        .sac-tile-head h3 { margin: 0; font-size: 13px; font-weight: 600; color: var(--sac-text); }
        .sac-tile-head .sub { font-size: 11px; color: var(--sac-muted); }
        .sac-tile-body { padding: 14px; flex: 1; position: relative; }
        .sac-tile-body.chart { height: 280px; }

        .col-2 { grid-column: span 2; } .col-3 { grid-column: span 3; }
        .col-4 { grid-column: span 4; } .col-5 { grid-column: span 5; }
        .col-6 { grid-column: span 6; } .col-7 { grid-column: span 7; }
        .col-8 { grid-column: span 8; } .col-12 { grid-column: span 12; }
        @media (max-width: 1100px) { .sac-grid > [class^="col-"] { grid-column: span 6; } }
        @media (max-width: 680px) { .sac-grid > [class^="col-"] { grid-column: span 12; } }

        /* ---------- KPI numeric point ---------- */
        .sac-kpi { padding: 14px 16px; display: flex; flex-direction: column; gap: 2px; }
        .sac-kpi .label { font-size: 12px; color: var(--sac-muted); }
        .sac-kpi .value { font-size: 30px; font-weight: 700; line-height: 1.1; color: var(--sac-text); }
        .sac-kpi .value small { font-size: 14px; font-weight: 600; color: var(--sac-muted); margin-left: 4px; }
        .sac-kpi .trend { font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .sac-kpi .trend.up { color: var(--sac-green); }
        .sac-kpi .trend.warn { color: var(--sac-orange); }
        .sac-kpi .bar { height: 4px; border-radius: 2px; background: #eef0f2; margin-top: 8px; overflow: hidden; }
        .sac-kpi .bar > span { display: block; height: 100%; background: var(--sac-brand); }

        /* ---------- Table ---------- */
        table.sac-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.sac-table th {
            text-align: left; color: var(--sac-muted); font-weight: 600; font-size: 11px;
            text-transform: uppercase; letter-spacing: .3px;
            padding: 8px 10px; border-bottom: 1px solid var(--sac-border-strong);
        }
        table.sac-table td { padding: 9px 10px; border-bottom: 1px solid var(--sac-border); }
        table.sac-table tr:last-child td { border-bottom: none; }
        table.sac-table td.num { text-align: right; font-variant-numeric: tabular-nums; }
        .sac-progress { height: 6px; border-radius: 3px; background: #eef0f2; overflow: hidden; min-width: 80px; }
        .sac-progress > span { display: block; height: 100%; background: var(--sac-green); }
        .sac-pill { display:inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; }
        .sac-pill.green { background: #ebf5eb; color: var(--sac-green); }
        .sac-pill.orange { background: #fdf2e6; color: var(--sac-orange); }
        .sac-pill.red { background: #fbeaea; color: var(--sac-red); }
        .sac-empty { color: var(--sac-muted); font-size: 13px; padding: 24px; text-align: center; }
        a.sac-back { color: #fff; text-decoration: none; font-size: 12px; opacity: .85; }
        a.sac-back:hover { opacity: 1; text-decoration: underline; }
    </style>
</head>
<body>
    {{-- ===== Shell bar ===== --}}
    <header class="sac-shell">
        <span class="sac-logo">SAP</span>
        <span class="sac-product">Analytics Cloud <small>· {{ config('app.name') }}</small></span>
        <div class="sac-search">
            <input type="search" placeholder="Rechercher des histoires, modèles, dimensions…" disabled>
        </div>
        <div class="sac-shell-actions">
            <a class="sac-back" href="{{ route('dashboard') }}">← Retour à l'application</a>
            <button class="sac-icon-btn" title="Notifications" aria-label="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
            </button>
            <span class="sac-avatar">{{ auth()->user()?->initials() }}</span>
        </div>
    </header>

    {{-- ===== Story toolbar ===== --}}
    <div class="sac-storybar">
        <div class="sac-story-title">
            Plan de Travail Annuel
            <span>CANAM — Suivi budgétaire &amp; exécution</span>
        </div>
        <nav class="sac-tabs" id="sac-tabs">
            <button class="sac-tab active" data-page="overview">Vue d'ensemble</button>
            <button class="sac-tab" data-page="budget">Budget</button>
            <button class="sac-tab" data-page="execution">Exécution</button>
        </nav>
        <div class="sac-toolbar">
            <form method="GET" action="{{ route('sap.analytics') }}" id="sac-year-form">
                <select class="sac-select" name="annee" onchange="document.getElementById('sac-year-form').submit()">
                    @foreach (($filters['year_options'] ?? []) as $year)
                        <option value="{{ $year }}" @selected(($filters['selected_year'] ?? null) == $year)>Exercice {{ $year }}</option>
                    @endforeach
                </select>
            </form>
            <button class="sac-btn" onclick="window.location.reload()" title="Actualiser">↻</button>
            <button class="sac-btn primary" onclick="window.print()">Exporter</button>
        </div>
    </div>

    {{-- ===== Filter chips ===== --}}
    <div class="sac-filterbar">
        <span>Filtres&nbsp;:</span>
        <span class="sac-chip">Exercice <b>{{ $filters['selected_year'] ?? '—' }}</b></span>
        <span class="sac-chip">Source <b>SYGECO · PTA</b></span>
        <span class="sac-chip">Devise <b>FCFA</b></span>
    </div>

    {{-- ===== Canvas ===== --}}
    <main class="sac-canvas">

        {{-- ---------- PAGE : VUE D'ENSEMBLE ---------- --}}
        <section class="sac-page active" id="page-overview">
            <div class="sac-grid">
                @php
                    $budgetM = round((float) ($kpis['budget_total'] ?? 0) / 1000000, 1);
                    $kpiTiles = [
                        ['Objectifs stratégiques', number_format($kpis['objectifs'] ?? 0, 0, ',', ' '), '', null],
                        ['Extrants', number_format($kpis['extrants'] ?? 0, 0, ',', ' '), '', null],
                        ['Activités', number_format($kpis['activites'] ?? 0, 0, ',', ' '), '', null],
                        ['Budget total', number_format($budgetM, 1, ',', ' '), 'M FCFA', null],
                        ['Taux de réalisation', number_format($kpis['taux_realisation'] ?? 0, 1, ',', ' '), '%', (float) ($kpis['taux_realisation'] ?? 0)],
                        ['En attente de validation', number_format($kpis['en_attente'] ?? 0, 0, ',', ' '), '', null],
                    ];
                @endphp
                @foreach ($kpiTiles as $tile)
                    <div class="sac-tile col-2">
                        <div class="sac-kpi">
                            <span class="label">{{ $tile[0] }}</span>
                            <span class="value">{{ $tile[1] }}@if($tile[2])<small>{{ $tile[2] }}</small>@endif</span>
                            @if (! is_null($tile[3]))
                                <div class="bar"><span style="width: {{ min(100, max(0, $tile[3])) }}%"></span></div>
                            @else
                                <span class="trend up">&nbsp;</span>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="sac-tile col-8">
                    <div class="sac-tile-head"><h3>Évolution mensuelle</h3><span class="sub">Activités &amp; budget (M FCFA)</span></div>
                    <div class="sac-tile-body chart"><canvas data-chart="evolution"></canvas></div>
                </div>
                <div class="sac-tile col-4">
                    <div class="sac-tile-head"><h3>Répartition par statut</h3><span class="sub">Activités</span></div>
                    <div class="sac-tile-body chart"><canvas data-chart="activites_statut"></canvas></div>
                </div>

                <div class="sac-tile col-7">
                    <div class="sac-tile-head"><h3>Budget par objectif</h3><span class="sub">M FCFA</span></div>
                    <div class="sac-tile-body chart"><canvas data-chart="budget_par_objectif"></canvas></div>
                </div>
                <div class="sac-tile col-5">
                    <div class="sac-tile-head"><h3>Distribution budgétaire</h3><span class="sub">Par tranche</span></div>
                    <div class="sac-tile-body chart"><canvas data-chart="distribution_budgetaire"></canvas></div>
                </div>
            </div>
        </section>

        {{-- ---------- PAGE : BUDGET ---------- --}}
        <section class="sac-page" id="page-budget">
            <div class="sac-grid">
                <div class="sac-tile col-7">
                    <div class="sac-tile-head"><h3>Budget par objectif</h3><span class="sub">M FCFA</span></div>
                    <div class="sac-tile-body chart"><canvas data-chart="budget_par_objectif_2"></canvas></div>
                </div>
                <div class="sac-tile col-5">
                    <div class="sac-tile-head"><h3>Distribution budgétaire</h3><span class="sub">Par tranche</span></div>
                    <div class="sac-tile-body chart"><canvas data-chart="distribution_budgetaire_2"></canvas></div>
                </div>

                <div class="sac-tile col-6">
                    <div class="sac-tile-head"><h3>Budget par département</h3><span class="sub">Top 8 · M FCFA</span></div>
                    <div class="sac-tile-body">
                        <table class="sac-table">
                            <thead><tr><th>Département</th><th class="num">Budget (M FCFA)</th></tr></thead>
                            <tbody>
                            @forelse (($tables['budget_departements'] ?? []) as $row)
                                <tr><td>{{ $row['nom'] }}</td><td class="num">{{ number_format($row['budget_millions'] ?? 0, 1, ',', ' ') }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="sac-empty">Aucune donnée</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="sac-tile col-6">
                    <div class="sac-tile-head"><h3>Top activités par coût</h3><span class="sub">M FCFA</span></div>
                    <div class="sac-tile-body">
                        <table class="sac-table">
                            <thead><tr><th>Code</th><th>Activité</th><th class="num">Coût (M)</th></tr></thead>
                            <tbody>
                            @forelse (($tables['top_activites'] ?? []) as $row)
                                <tr>
                                    <td>{{ $row['code'] }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($row['nom_activite'] ?? '', 42) }}</td>
                                    <td class="num">{{ number_format($row['cout_millions'] ?? 0, 1, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="sac-empty">Aucune donnée</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        {{-- ---------- PAGE : EXÉCUTION ---------- --}}
        <section class="sac-page" id="page-execution">
            <div class="sac-grid">
                <div class="sac-tile col-4">
                    <div class="sac-tile-head"><h3>Activités par statut</h3><span class="sub">Cycle de validation</span></div>
                    <div class="sac-tile-body chart"><canvas data-chart="activites_statut_2"></canvas></div>
                </div>
                <div class="sac-tile col-4">
                    <div class="sac-tile-head"><h3>Activités par trimestre</h3><span class="sub">Planification</span></div>
                    <div class="sac-tile-body chart"><canvas data-chart="activites_trimestre"></canvas></div>
                </div>
                <div class="sac-tile col-4">
                    <div class="sac-tile-head"><h3>Top extrants</h3><span class="sub">Nombre d'activités</span></div>
                    <div class="sac-tile-body chart"><canvas data-chart="top_extrants"></canvas></div>
                </div>

                <div class="sac-tile col-7">
                    <div class="sac-tile-head"><h3>Taux de soumission par département</h3><span class="sub">Activités soumises</span></div>
                    <div class="sac-tile-body">
                        <table class="sac-table">
                            <thead><tr><th>Département</th><th class="num">Total</th><th class="num">Soumises</th><th>Avancement</th></tr></thead>
                            <tbody>
                            @forelse (($tables['soumission_departements'] ?? []) as $row)
                                <tr>
                                    <td>{{ $row['nom'] }}</td>
                                    <td class="num">{{ $row['total'] ?? 0 }}</td>
                                    <td class="num">{{ $row['soumises'] ?? 0 }}</td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px">
                                            <div class="sac-progress"><span style="width: {{ min(100, $row['pct'] ?? 0) }}%"></span></div>
                                            <span style="font-variant-numeric:tabular-nums;color:var(--sac-muted)">{{ $row['pct'] ?? 0 }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="sac-empty">Aucune donnée</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="sac-tile col-5">
                    <div class="sac-tile-head"><h3>Départements en retard</h3><span class="sub">Brouillons non soumis</span></div>
                    <div class="sac-tile-body">
                        <table class="sac-table">
                            <thead><tr><th>Département</th><th class="num">Brouillons</th><th>Statut</th></tr></thead>
                            <tbody>
                            @forelse (($tables['departements_en_retard'] ?? []) as $row)
                                <tr>
                                    <td>{{ $row['nom'] }}</td>
                                    <td class="num">{{ $row['nb_brouillon'] ?? 0 }}</td>
                                    <td><span class="sac-pill {{ ($row['nb_brouillon'] ?? 0) > 5 ? 'red' : 'orange' }}">À relancer</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="sac-empty">Aucun retard 🎉</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script type="application/json" id="sac-data">{!! json_encode($sac, JSON_UNESCAPED_UNICODE) !!}</script>
    <script>
        (function () {
            const DATA = JSON.parse(document.getElementById('sac-data').textContent);
            const PALETTE = ['#0070F2', '#188918', '#E76500', '#7858FF', '#FA4F96', '#1B90FF', '#049F9A', '#C87A00', '#5B738B'];
            const GRID = '#eaecee', FONT = { family: '"72","Segoe UI",Arial,sans-serif', size: 11 };
            Chart.defaults.font.family = FONT.family;
            Chart.defaults.color = '#6a6d70';

            const built = {};

            function baseOpts(extra) {
                return Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { boxWidth: 12, boxHeight: 12, padding: 12 } } },
                }, extra || {});
            }

            // Builders keyed by canvas data-chart attribute
            const BUILDERS = {
                evolution() {
                    const c = DATA.charts.evolution || {};
                    return {
                        type: 'bar',
                        data: {
                            labels: c.labels || [],
                            datasets: [
                                { type: 'line', label: 'Activités', data: c.activites || [], borderColor: PALETTE[0], backgroundColor: PALETTE[0], tension: .35, yAxisID: 'y', pointRadius: 3, borderWidth: 2 },
                                { type: 'bar', label: 'Budget (M FCFA)', data: c.budget_millions || [], backgroundColor: 'rgba(24,137,24,.35)', borderColor: PALETTE[1], borderWidth: 1, yAxisID: 'y1' },
                            ],
                        },
                        options: baseOpts({
                            scales: {
                                y: { position: 'left', grid: { color: GRID }, title: { display: true, text: 'Activités' } },
                                y1: { position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'M FCFA' } },
                                x: { grid: { display: false } },
                            },
                        }),
                    };
                },
                _donut(chartKey) {
                    const c = DATA.charts[chartKey] || {};
                    return {
                        type: 'doughnut',
                        data: { labels: c.labels || [], datasets: [{ data: c.values || [], backgroundColor: PALETTE, borderWidth: 2, borderColor: '#fff' }] },
                        options: baseOpts({ cutout: '62%', plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 10 } } } }),
                    };
                },
                _barH(chartKey, color) {
                    const c = DATA.charts[chartKey] || {};
                    return {
                        type: 'bar',
                        data: { labels: c.labels || [], datasets: [{ label: 'Valeur', data: c.values || [], backgroundColor: color || PALETTE[0], borderRadius: 3, maxBarThickness: 22 }] },
                        options: baseOpts({ indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { grid: { color: GRID } }, y: { grid: { display: false } } } }),
                    };
                },
                _barV(chartKey, color) {
                    const c = DATA.charts[chartKey] || {};
                    return {
                        type: 'bar',
                        data: { labels: c.labels || [], datasets: [{ label: 'Valeur', data: c.values || [], backgroundColor: color || PALETTE[0], borderRadius: 3, maxBarThickness: 40 }] },
                        options: baseOpts({ plugins: { legend: { display: false } }, scales: { y: { grid: { color: GRID }, beginAtZero: true }, x: { grid: { display: false } } } }),
                    };
                },
                activites_statut() { return BUILDERS._donut('activites_statut'); },
                activites_statut_2() { return BUILDERS._donut('activites_statut'); },
                distribution_budgetaire() { return BUILDERS._donut('distribution_budgetaire'); },
                distribution_budgetaire_2() { return BUILDERS._donut('distribution_budgetaire'); },
                budget_par_objectif() { return BUILDERS._barH('budget_par_objectif', PALETTE[0]); },
                budget_par_objectif_2() { return BUILDERS._barH('budget_par_objectif', PALETTE[0]); },
                top_extrants() { return BUILDERS._barH('top_extrants', PALETTE[3]); },
                activites_trimestre() { return BUILDERS._barV('activites_trimestre', PALETTE[2]); },
            };

            function buildCanvas(canvas) {
                const key = canvas.getAttribute('data-chart');
                if (built[key] || !BUILDERS[key]) return;
                const cfg = BUILDERS[key]();
                if (!cfg) return;
                built[key] = new Chart(canvas.getContext('2d'), cfg);
            }

            function activatePage(page) {
                document.querySelectorAll('.sac-tab').forEach(t => t.classList.toggle('active', t.dataset.page === page));
                document.querySelectorAll('.sac-page').forEach(p => p.classList.toggle('active', p.id === 'page-' + page));
                // lazily build charts of the now-visible page (avoids 0-size canvas)
                document.querySelectorAll('#page-' + page + ' canvas[data-chart]').forEach(buildCanvas);
                Object.values(built).forEach(ch => ch.resize());
            }

            document.getElementById('sac-tabs').addEventListener('click', function (e) {
                const btn = e.target.closest('.sac-tab');
                if (btn) activatePage(btn.dataset.page);
            });

            // initial page
            activatePage('overview');
        })();
    </script>
</body>
</html>
