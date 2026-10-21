<x-layouts::app title="Analyse des activités">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Analyse des activités</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Programmation de l'exercice {{ $exercice?->annee ?? '—' }}, toutes entités : indicateurs et doublons potentiels.
            </p>
        </div>

        @php
            $statuts = ['brouillon' => 'Brouillons', 'rejete' => 'Rejetées', 'en_attente' => 'En attente', 'valide' => 'Validées'];
        @endphp

        {{-- Indicateurs clés --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            @foreach ([
                ['Activités', number_format($indicateurs['nombre'], 0, ',', ' '), $indicateurs['entites'].' entité(s) · '.$indicateurs['non_programmees'].' non programmée(s)', 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900'],
                ['Budget programmé (FCFA)', number_format($indicateurs['budget_programme'], 0, ',', ' '), $indicateurs['pour_memoire'].' activité(s) pour mémoire', 'border-sky-200 bg-sky-50 dark:border-sky-900/70 dark:bg-sky-950/30'],
                ['Budget validé (FCFA)', number_format($indicateurs['budget_valide'], 0, ',', ' '), $indicateurs['budget_programme'] > 0 ? round($indicateurs['budget_valide'] / $indicateurs['budget_programme'] * 100, 1).' % du programmé' : '—', 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/70 dark:bg-emerald-950/30'],
                ['Taux de validation', $indicateurs['taux_validation'].' %', 'des activités soumises', 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900'],
                ['Doublons potentiels', $doublons->count(), $doublons->where('niveau', 'fort')->count().' à vérifier en priorité', $doublons->isNotEmpty() ? 'border-rose-200 bg-rose-50 dark:border-rose-900/70 dark:bg-rose-950/30' : 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/70 dark:bg-emerald-950/30'],
            ] as [$libelle, $valeur, $aide, $classes])
                <div class="rounded-xl border p-4 {{ $classes }}">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $libelle }}</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ $valeur }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $aide }}</p>
                </div>
            @endforeach
        </div>

        {{-- Avancement par statut --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
            <h2 class="font-semibold text-slate-900 dark:text-white">Avancement de la programmation</h2>
            @php $total = max(1, $indicateurs['nombre']); @endphp
            <div class="mt-3 flex h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                @foreach (['brouillon' => 'bg-slate-400', 'rejete' => 'bg-rose-500', 'en_attente' => 'bg-amber-400', 'valide' => 'bg-emerald-500'] as $statut => $couleur)
                    <div class="{{ $couleur }}" style="width: {{ $indicateurs['par_statut'][$statut] / $total * 100 }}%"></div>
                @endforeach
            </div>
            <div class="mt-3 flex flex-wrap gap-4 text-sm">
                @foreach (['brouillon' => 'bg-slate-400', 'rejete' => 'bg-rose-500', 'en_attente' => 'bg-amber-400', 'valide' => 'bg-emerald-500'] as $statut => $couleur)
                    <span class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-200"><span class="h-2.5 w-2.5 rounded-full {{ $couleur }}"></span>{{ $statuts[$statut] }} : <strong>{{ $indicateurs['par_statut'][$statut] }}</strong></span>
                @endforeach
            </div>
        </div>

        {{-- Doublons potentiels --}}
        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-5 py-3 dark:border-slate-700">
                <h2 class="font-semibold text-slate-900 dark:text-white">Doublons potentiels</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Même intitulé (aux accents et à la casse près), ou intitulés très proches sous un même extrant. À vérifier : ce n'est pas forcément une erreur.</p>
            </div>
            @if ($doublons->isEmpty())
                <p class="px-5 py-6 text-sm text-emerald-700 dark:text-emerald-300">Aucun doublon potentiel détecté.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                            <tr><th class="px-5 py-2">Niveau</th><th class="px-5 py-2">Motif</th><th class="px-5 py-2">Activités</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($doublons as $alerte)
                                <tr class="align-top">
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $alerte['niveau'] === 'fort' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' }}">{{ $alerte['niveau'] === 'fort' ? 'Fort' : 'Moyen' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-800 dark:text-slate-100">{{ $alerte['motif'] }}</td>
                                    <td class="px-5 py-3">
                                        @foreach ($alerte['activites'] as $activite)
                                            <a href="{{ route('activites.show', $activite) }}" class="block font-medium text-sky-700 hover:underline dark:text-sky-300">{{ Str::limit($activite->nom_activite, 110) }}</a>
                                            <span class="mb-1 block text-xs text-slate-500 dark:text-slate-400">
                                                {{ $activite->departement?->code ?: $activite->departement?->nom }} · {{ $activite->extrant?->code ?? 'Non programmée' }} · {{ $statuts[$activite->statut] ?? $activite->statut }} · {{ $activite->pour_memoire ? 'PM' : number_format((float) $activite->cout, 0, ',', ' ').' FCFA' }}
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            {{-- Par entité --}}
            <div class="rounded-xl border border-slate-200 bg-white xl:col-span-2 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="border-b border-slate-200 px-5 py-3 font-semibold text-slate-900 dark:border-slate-700 dark:text-white">Par entité porteuse</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <tr><th class="px-5 py-2">Entité</th><th class="px-5 py-2 text-right">Activités</th><th class="px-5 py-2 text-right">Brouillons</th><th class="px-5 py-2 text-right">En attente</th><th class="px-5 py-2 text-right">Validées</th><th class="px-5 py-2 text-right">Budget (FCFA)</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($indicateurs['par_entite'] as $ligne)
                                <tr>
                                    <td class="px-5 py-2 text-slate-800 dark:text-slate-100">{{ $ligne['entite'] }}</td>
                                    <td class="px-5 py-2 text-right">{{ $ligne['nombre'] }}</td>
                                    <td class="px-5 py-2 text-right">{{ $ligne['brouillons'] }}</td>
                                    <td class="px-5 py-2 text-right">{{ $ligne['en_attente'] }}</td>
                                    <td class="px-5 py-2 text-right">{{ $ligne['validees'] }}</td>
                                    <td class="px-5 py-2 text-right">{{ number_format($ligne['budget'], 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-4 text-slate-500">Aucune activité.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Chronogramme --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="font-semibold text-slate-900 dark:text-white">Chronogramme</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Activités prévues par trimestre (une activité peut couvrir plusieurs trimestres).</p>
                @php $maxT = max(1, $indicateurs['par_trimestre']->max()); @endphp
                <div class="mt-4 space-y-3">
                    @foreach ($indicateurs['par_trimestre'] as $trimestre => $nombre)
                        <div>
                            <div class="flex justify-between text-sm"><span class="text-slate-700 dark:text-slate-200">T{{ $trimestre }}</span><span class="font-medium">{{ $nombre }}</span></div>
                            <div class="mt-1 h-2 rounded bg-slate-100 dark:bg-slate-800"><div class="h-2 rounded bg-sky-500" style="width: {{ round($nombre / $maxT * 100) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-sm text-slate-700 dark:text-slate-200">Coût moyen d'une activité : <strong>{{ number_format($indicateurs['cout_moyen'], 0, ',', ' ') }} FCFA</strong></p>
            </div>
        </div>
    </div>
</x-layouts::app>
