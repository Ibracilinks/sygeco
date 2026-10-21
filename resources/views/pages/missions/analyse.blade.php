<x-layouts::app title="Analyse des missions">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Analyse des missions</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Toutes les missions de {{ $filters['annee'] }}, toutes structures : destinations, carburant, dépenses et doublons potentiels.</p>
            </div>
            <form method="GET" action="{{ route('missions.analyse') }}" class="flex flex-wrap items-center gap-2">
                <select name="annee" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    @foreach ($annees as $annee)
                        <option value="{{ $annee }}" @selected($filters['annee'] === $annee)>{{ $annee }}</option>
                    @endforeach
                </select>
                <select name="type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    <option value="">Tous les types</option>
                    @foreach (\App\Models\Mission::TYPES as $code => $libelle)
                        <option value="{{ $code }}" @selected($filters['type'] === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
                <select name="statut" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    <option value="">Tous les statuts</option>
                    @foreach (\App\Models\Mission::STATUTS as $code => $libelle)
                        <option value="{{ $code }}" @selected($filters['statut'] === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Filtrer</button>
            </form>
        </div>

        {{-- Chiffres clés --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            @foreach ([
                ['Missions', number_format($stats['nombre'], 0, ',', ' '), $stats['finalisees'].' finalisée(s) · '.$stats['brouillons'].' brouillon(s)', 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900'],
                ['Budget engagé (FCFA)', number_format($stats['budget_engage'], 0, ',', ' '), 'missions finalisées · '.number_format($stats['budget_prevu'], 0, ',', ' ').' en préparation', 'border-sky-200 bg-sky-50 dark:border-sky-900/70 dark:bg-sky-950/30'],
                ['Coût moyen par mission', number_format($stats['cout_moyen_mission'], 0, ',', ' '), 'FCFA', 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900'],
                ['Jours-personnes', number_format($stats['jours_personnes'], 0, ',', ' '), number_format($stats['cout_jour_personne'], 0, ',', ' ').' FCFA par jour-personne · '.$stats['participants'].' participant(s)', 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900'],
                ['Doublons potentiels', $doublons->count(), $doublons->where('niveau', 'fort')->count().' à vérifier en priorité', $doublons->isNotEmpty() ? 'border-rose-200 bg-rose-50 dark:border-rose-900/70 dark:bg-rose-950/30' : 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/70 dark:bg-emerald-950/30'],
            ] as [$libelle, $valeur, $aide, $classes])
                <div class="rounded-xl border p-4 {{ $classes }}">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $libelle }}</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ $valeur }}</p>
                    @if ($aide)<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $aide }}</p>@endif
                </div>
            @endforeach
        </div>

        {{-- Carburant et tickets --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Tickets carburant', number_format($stats['tickets_carburant'], 0, ',', ' '), 'missions même ville'],
                ['Carburant trajet', number_format($stats['carburant']['montant_trajet'], 0, ',', ' ').' FCFA', number_format($stats['carburant']['litres_trajet'], 0, ',', ' ').' L · '.number_format($stats['carburant']['distance'], 0, ',', ' ').' km'],
                ['Carburant ville', number_format($stats['carburant']['montant_ville'], 0, ',', ' ').' FCFA', number_format($stats['carburant']['litres_ville'], 0, ',', ' ').' L'],
                ['Carburant total', number_format($stats['carburant']['montant_trajet'] + $stats['carburant']['montant_ville'], 0, ',', ' ').' FCFA', 'missions à l\'intérieur'],
            ] as [$libelle, $valeur, $aide])
                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $libelle }}</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900 dark:text-white">{{ $valeur }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $aide }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            {{-- Dépenses par poste --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <h2 class="border-b border-slate-200 px-5 py-3 font-semibold text-slate-900 dark:border-slate-700 dark:text-white">Dépenses par poste</h2>
                @php $totalDepenses = max(1, $stats['depenses']->sum()); @endphp
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($stats['depenses'] as $poste => $montant)
                            <tr>
                                <td class="px-5 py-2 text-slate-800 dark:text-slate-100">
                                    {{ $poste }}
                                    <div class="mt-1 h-1.5 rounded bg-slate-100 dark:bg-slate-800"><div class="h-1.5 rounded bg-sky-500" style="width: {{ round($montant / $totalDepenses * 100) }}%"></div></div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-2 text-right">{{ number_format($montant, 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-5 py-4 text-slate-500">Aucune dépense.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Destinations à l'intérieur --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <h2 class="border-b border-slate-200 px-5 py-3 font-semibold text-slate-900 dark:border-slate-700 dark:text-white">Destinations à l'intérieur</h2>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400"><tr><th class="px-5 py-2">Localité</th><th class="px-5 py-2 text-right">Missions</th><th class="px-5 py-2 text-right">Jours</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($stats['destinations_interieur'] as $ligne)
                            <tr><td class="px-5 py-2 text-slate-800 dark:text-slate-100">{{ $ligne['localite'] }} <span class="text-xs text-slate-500">({{ $ligne['type'] }})</span></td><td class="px-5 py-2 text-right">{{ $ligne['missions'] }}</td><td class="px-5 py-2 text-right">{{ $ligne['jours'] }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-4 text-slate-500">Aucune mission à l'intérieur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <p class="px-5 py-2 text-xs text-slate-500 dark:text-slate-400">D'après les étapes des missions.</p>
            </div>

            {{-- Destinations à l'étranger --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <h2 class="border-b border-slate-200 px-5 py-3 font-semibold text-slate-900 dark:border-slate-700 dark:text-white">Destinations à l'étranger</h2>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400"><tr><th class="px-5 py-2">Destination</th><th class="px-5 py-2 text-right">Missions</th><th class="px-5 py-2 text-right">Budget (FCFA)</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($stats['destinations_etranger'] as $ligne)
                            <tr><td class="px-5 py-2 text-slate-800 dark:text-slate-100">{{ $ligne['destination'] }}@if ($ligne['zone'])<span class="block text-xs text-slate-500">{{ $ligne['zone'] }}</span>@endif</td><td class="px-5 py-2 text-right">{{ $ligne['missions'] }}</td><td class="px-5 py-2 text-right">{{ number_format($ligne['budget'], 0, ',', ' ') }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-4 text-slate-500">Aucune mission à l'étranger.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Doublons potentiels --}}
        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-5 py-3 dark:border-slate-700">
                <h2 class="font-semibold text-slate-900 dark:text-white">Doublons potentiels</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Une même personne sur deux missions simultanées, ou deux missions qui se ressemblent fortement. À vérifier : ce n'est pas forcément une erreur.</p>
            </div>
            @if ($doublons->isEmpty())
                <p class="px-5 py-6 text-sm text-emerald-700 dark:text-emerald-300">Aucun doublon potentiel détecté.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                            <tr><th class="px-5 py-2">Niveau</th><th class="px-5 py-2">Motif</th><th class="px-5 py-2">Détail</th><th class="px-5 py-2">Missions</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($doublons as $alerte)
                                <tr class="align-top">
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $alerte['niveau'] === 'fort' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' }}">{{ $alerte['niveau'] === 'fort' ? 'Fort' : 'Moyen' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-800 dark:text-slate-100">{{ $alerte['motif'] }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ Str::limit($alerte['detail'], 120) }}</td>
                                    <td class="px-5 py-3">
                                        @foreach ($alerte['missions'] as $mission)
                                            <a href="{{ route('missions.show', $mission) }}" class="block font-medium text-sky-700 hover:underline dark:text-sky-300">
                                                {{ $mission->reference }}
                                            </a>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400">{{ \App\Models\Mission::TYPES[$mission->type] ?? $mission->type }} · {{ $mission->date_depart?->format('d/m/Y') }} → {{ $mission->date_retour?->format('d/m/Y') }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Répartitions --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <h2 class="border-b border-slate-200 px-5 py-3 font-semibold text-slate-900 dark:border-slate-700 dark:text-white">Par type de mission</h2>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400"><tr><th class="px-5 py-2">Type</th><th class="px-5 py-2 text-right">Missions</th><th class="px-5 py-2 text-right">Budget (FCFA)</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($stats['par_type'] as $ligne)
                            <tr><td class="px-5 py-2 text-slate-800 dark:text-slate-100">{{ $ligne['libelle'] }}</td><td class="px-5 py-2 text-right">{{ $ligne['nombre'] }}</td><td class="px-5 py-2 text-right">{{ number_format($ligne['budget'], 0, ',', ' ') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <h2 class="border-b border-slate-200 px-5 py-3 font-semibold text-slate-900 dark:border-slate-700 dark:text-white">Par structure demandeuse</h2>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400"><tr><th class="px-5 py-2">Structure</th><th class="px-5 py-2 text-right">Missions</th><th class="px-5 py-2 text-right">Budget (FCFA)</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($stats['par_structure'] as $ligne)
                            <tr><td class="px-5 py-2 text-slate-800 dark:text-slate-100">{{ $ligne['structure'] }}</td><td class="px-5 py-2 text-right">{{ $ligne['nombre'] }}</td><td class="px-5 py-2 text-right">{{ number_format($ligne['budget'], 0, ',', ' ') }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-4 text-slate-500">Aucune mission.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <p class="px-5 py-2 text-xs text-slate-500 dark:text-slate-400">Une mission demandée par plusieurs structures compte pour chacune.</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
            <h2 class="font-semibold text-slate-900 dark:text-white">Missions par mois de départ</h2>
            @php $max = max(1, $stats['par_mois']->max()); @endphp
            <div class="mt-4 grid grid-cols-12 items-end gap-2" style="height: 9rem">
                @foreach ($stats['par_mois'] as $mois => $nombre)
                    <div class="flex h-full flex-col items-center justify-end gap-1">
                        <span class="text-xs text-slate-600 dark:text-slate-300">{{ $nombre ?: '' }}</span>
                        <div class="w-full rounded-t bg-sky-500/80" style="height: {{ $nombre ? max(4, round($nombre / $max * 100)) : 0 }}%"></div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Carbon::create(null, $mois, 1)->locale('fr')->isoFormat('MMM') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Toutes les missions --}}
        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <h2 class="border-b border-slate-200 px-5 py-3 font-semibold text-slate-900 dark:border-slate-700 dark:text-white">Toutes les missions ({{ $missions->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                        <tr><th class="px-5 py-2">Référence</th><th class="px-5 py-2">Objet</th><th class="px-5 py-2">Type</th><th class="px-5 py-2">Structure</th><th class="px-5 py-2">Période</th><th class="px-5 py-2 text-right">Participants</th><th class="px-5 py-2 text-right">Montant (FCFA)</th><th class="px-5 py-2">Statut</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($missions as $mission)
                            <tr class="{{ isset($enDoublon[$mission->id]) ? 'bg-rose-50/60 dark:bg-rose-950/20' : '' }}">
                                <td class="px-5 py-2">
                                    <a href="{{ route('missions.show', $mission) }}" class="font-medium text-sky-700 hover:underline dark:text-sky-300">{{ $mission->reference }}</a>
                                    @isset($enDoublon[$mission->id])<span class="ml-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-semibold text-rose-700 dark:bg-rose-900/40 dark:text-rose-200" title="Doublon potentiel">doublon ?</span>@endisset
                                </td>
                                <td class="px-5 py-2 text-slate-700 dark:text-slate-200">{{ Str::limit($mission->objet, 70) }}</td>
                                <td class="px-5 py-2">{{ \App\Models\Mission::TYPES[$mission->type] ?? $mission->type }}</td>
                                <td class="px-5 py-2">{{ ($mission->departements->isNotEmpty() ? $mission->departements : collect([$mission->departement])->filter())->map(fn ($s) => $s->code ?: $s->nom)->join(' / ') }}</td>
                                <td class="whitespace-nowrap px-5 py-2">{{ $mission->date_depart?->format('d/m/Y') }} → {{ $mission->date_retour?->format('d/m/Y') }}</td>
                                <td class="px-5 py-2 text-right">{{ $mission->participants->count() }}</td>
                                <td class="px-5 py-2 text-right">{{ number_format((float) $mission->montant_total, 0, ',', ' ') }}</td>
                                <td class="px-5 py-2">{{ \App\Models\Mission::STATUTS[$mission->statut] ?? $mission->statut }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">Aucune mission pour cette période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
