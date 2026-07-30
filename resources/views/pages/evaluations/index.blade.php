@php
    use App\Models\ActiviteEvaluation;

    $periodeLibelle = ActiviteEvaluation::PERIODES[$periode];
    $slug = ActiviteEvaluation::slugDePeriode($periode);
    $autreLibelle = ActiviteEvaluation::PERIODES[$autrePeriode];
    $autreSlug = ActiviteEvaluation::slugDePeriode($autrePeriode);
@endphp

<x-layouts::app title="Évaluation — {{ $periodeLibelle }}">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Évaluation — {{ $periodeLibelle }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    État d'exécution, budget consommé et valeur d'indicateur pour la période
                    @if ($exercice) — exercice {{ $exercice->annee }} @endif.
                    <a href="{{ route('evaluations.index', $autreSlug) }}" class="font-medium text-sky-700 hover:underline dark:text-sky-300">Voir {{ $autreLibelle }}</a>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('evaluations.export', array_merge([$slug], request()->query())) }}"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500">
                    Exporter (Cadre logique)
                </a>
                @can('edit_activites')
                    <button type="button" onclick="document.getElementById('nonProgrammeeModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                        + Activité non programmée
                    </button>
                @endcan
            </div>
        </div>

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

        {{-- Onglets des deux périodes --}}
        <div class="flex gap-1 rounded-xl border border-slate-200 bg-white p-1 dark:border-slate-700 dark:bg-slate-900 md:w-fit">
            @foreach (ActiviteEvaluation::PERIODES as $valeur => $libelle)
                <a href="{{ route('evaluations.index', ActiviteEvaluation::slugDePeriode($valeur)) }}"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition
                    {{ $valeur === $periode
                        ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-900'
                        : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    {{ $libelle }}
                </a>
            @endforeach
        </div>

        {{-- État de la fenêtre de saisie --}}
        @if ($exercice?->enPeriodeEvaluationPour($periode))
            <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 dark:border-sky-800 dark:bg-sky-950/40">
                <p class="text-sm font-medium text-sky-800 dark:text-sky-200">
                    🟢 Fenêtre de saisie {{ $periodeLibelle }} ouverte{{ $finFenetre ? " — jusqu'au ".$finFenetre->format('d/m/Y') : '' }}.
                </p>
            </div>
        @elseif ($peutSaisir)
            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800 dark:bg-indigo-950/40">
                <p class="text-sm font-medium text-indigo-800 dark:text-indigo-200">
                    ✎ Fenêtre {{ $periodeLibelle }} fermée{{ $debutFenetre && $finFenetre ? ' ('.$debutFenetre->format('d/m/Y').' → '.$finFenetre->format('d/m/Y').')' : '' }}, saisie autorisée pour votre profil.
                </p>
            </div>
        @else
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/40">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    🔒 Fenêtre de saisie {{ $periodeLibelle }} fermée{{ $debutFenetre && $finFenetre ? ' ('.$debutFenetre->format('d/m/Y').' → '.$finFenetre->format('d/m/Y').')' : '' }} — consultation uniquement.
                </p>
            </div>
        @endif

        {{-- KPI d'avancement de la période --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités validées</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['total']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($summary['evaluees']) }} évaluée(s) — {{ $summary['taux_saisie'] }}%</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/25">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Réalisé</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($summary['realise']) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/25">
                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">En cours</p>
                <p class="mt-2 text-3xl font-semibold text-amber-800 dark:text-amber-100">{{ number_format($summary['en_cours']) }}</p>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/60 dark:bg-rose-950/25">
                <p class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">Non réalisé</p>
                <p class="mt-2 text-3xl font-semibold text-rose-800 dark:text-rose-100">{{ number_format($summary['non_realise']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Non évaluée</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['non_evaluee']) }}</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/60 dark:bg-sky-950/25">
                <p class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-300">Taux de réalisation</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-100">{{ $summary['taux_realisation'] }}%</p>
                <p class="text-xs text-sky-700/80 dark:text-sky-300/80">sur les activités évaluées</p>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" action="{{ route('evaluations.index', $slug) }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-5">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nom de l'activité"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">

            <select name="extrant_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous extrants</option>
                @foreach ($extrants as $extrant)
                    <option value="{{ $extrant->id }}" @selected((string) ($filters['extrant_id'] ?? '') === (string) $extrant->id)>{{ $extrant->code }}</option>
                @endforeach
            </select>

            <select name="departement_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Toutes structures</option>
                <x-departement-options :groupes="$departementsGroupes" :selected="$filters['departement_id'] ?? ''" />
            </select>

            <select name="statut_execution" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous états</option>
                @foreach (\App\Models\Activite::STATUTS_EXECUTION as $val => $label)
                    <option value="{{ $val }}" @selected(($filters['statut_execution'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
                <option value="non_evaluee" @selected(($filters['statut_execution'] ?? '') === 'non_evaluee')>Non évaluée</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Filtrer</button>
                <a href="{{ route('evaluations.index', $slug) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Activité</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $periodeLibelle }}</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $autreLibelle }}</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Mise à jour</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Budget &amp; observations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($activites as $activite)
                        @php
                            $evaluation = $activite->evaluation($periode);
                            $autreEvaluation = $activite->evaluation($autrePeriode);
                            $montant = $evaluation?->montant_utilise;
                            $ecart = $montant !== null ? (float) $activite->cout - (float) $montant : null;
                        @endphp
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <a href="{{ route('activites.show', $activite) }}" class="text-sm font-semibold text-slate-900 transition hover:text-sky-700 hover:underline dark:text-white dark:hover:text-sky-300">
                                    {{ Str::limit($activite->nom_activite, 90) }}
                                </a>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $activite->extrant->code ?? '—' }} • {{ $activite->departement->nom ?? '-' }}
                                    @if ($activite->non_programmee)
                                        <span class="ml-1 inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-purple-800 dark:bg-purple-900/40 dark:text-purple-200">Non programmée</span>
                                    @endif
                                </p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Budget planifié : {{ number_format($activite->cout, 0, ',', ' ') }} FCFA</p>
                            </td>
                            <td class="px-5 py-4">
                                @if ($evaluation)
                                    <x-execution-badge :statut="$evaluation->statut_execution" />
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        Non évaluée
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($autreEvaluation)
                                    <x-execution-badge :statut="$autreEvaluation->statut_execution" />
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500">Non évaluée</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500 dark:text-slate-400">
                                @if ($evaluation?->maj_le)
                                    {{ $evaluation->maj_le->format('d/m/Y H:i') }}
                                    @if ($evaluation->majPar)<br>par {{ $evaluation->majPar->name }}@endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1 text-sm text-slate-600 dark:text-slate-300">
                                    <p>{{ $evaluation?->observation ?: '—' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Budget utilisé : {{ $montant !== null ? number_format($montant, 0, ',', ' ').' FCFA' : '—' }}
                                        @if ($ecart !== null)
                                            <span class="{{ $ecart < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                                ({{ $ecart < 0 ? 'dépassement' : 'écart' }} {{ number_format(abs($ecart), 0, ',', ' ') }})
                                            </span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Valeur indicateur :
                                        {{ $evaluation?->valeur_indicateur !== null ? rtrim(rtrim(number_format($evaluation->valeur_indicateur, 2, ',', ' '), '0'), ',') : '—' }}
                                    </p>
                                </div>
                                @if (auth()->user()->can('edit_activites') && $peutSaisir)
                                    @php
                                        $evalData = [
                                            'action' => route('evaluations.enregistrer', [$activite, $slug]),
                                            'nom' => $activite->nom_activite,
                                            'cout' => (float) $activite->cout,
                                            'statut_execution' => $evaluation->statut_execution ?? 'non_realise',
                                            'observation' => $evaluation?->observation,
                                            'montant_utilise' => $evaluation?->montant_utilise,
                                            'valeur_indicateur' => $evaluation?->valeur_indicateur,
                                        ];
                                    @endphp
                                    <button type="button"
                                        onclick="openEvaluationModal({{ Js::from($evalData) }})"
                                        class="mt-2 inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                                        ✎ Renseigner {{ $periodeLibelle }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Aucune activité à évaluer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $activites->links() }}</div>
    </div>

    @can('edit_activites')
        {{-- Modale : création d'une activité non programmée (hors PTA) --}}
        <div id="nonProgrammeeModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40 p-4">
            <div class="mx-auto my-10 max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold dark:text-white">Nouvelle activité non programmée</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Activité hors plan de travail annuel, rattachée à l'exercice en cours{{ $exercice ? ' ('.$exercice->annee.')' : '' }}.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('nonProgrammeeModal').classList.add('hidden')"
                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('activites.non-programmee.store') }}" method="POST" class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Intitulé de l'activité *</label>
                        <input type="text" name="nom_activite" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    </div>

                    @unless (auth()->user()->hasRole('chef'))
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Structure *</label>
                            <select name="departement_id" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                <option value="">— Sélectionner —</option>
                                <x-departement-options :groupes="$departementsGroupes" />
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="departement_id" value="{{ auth()->user()->departement_id }}">
                    @endunless

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Coût (FCFA) *</label>
                        <input type="number" name="cout" min="0" step="1" max="{{ \App\Models\Activite::MONTANT_MAX }}" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">État d'exécution</label>
                        <select name="statut_execution"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            @foreach (\App\Models\Activite::STATUTS_EXECUTION as $val => $label)
                                <option value="{{ $val }}" @selected($val === 'realise')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Indicateur (facultatif)</label>
                        <input type="text" name="indicateur_objectivement_verifiable"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Moyen de vérification (facultatif)</label>
                        <input type="text" name="moyen_verification"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Observations (facultatif)</label>
                        <textarea name="commentaires" rows="2"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></textarea>
                    </div>

                    <div class="md:col-span-2 flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('nonProgrammeeModal').classList.add('hidden')"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-white">Annuler</button>
                        <button type="submit"
                            class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modale : renseigner l'évaluation de la période affichée --}}
        <div id="evaluationModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40 p-4">
            <div class="mx-auto my-10 max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold dark:text-white">Évaluation {{ $periodeLibelle }}</h2>
                        <p id="evaluationModalNom" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"></p>
                    </div>
                    <button type="button" onclick="document.getElementById('evaluationModal').classList.add('hidden')"
                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-white">✕</button>
                </div>

                <form id="evaluationForm" method="POST" class="mt-6 grid grid-cols-1 gap-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">État d'exécution *</label>
                        <select name="statut_execution" id="eval_statut_execution" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            @foreach (\App\Models\Activite::STATUTS_EXECUTION as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Observation</label>
                        <textarea name="observation" id="eval_observation" rows="2" maxlength="1000"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Budget utilisé (FCFA)</label>
                            <input type="number" step="0.01" min="0" max="{{ \App\Models\Activite::MONTANT_MAX }}" name="montant_utilise" id="eval_montant_utilise"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            <p id="eval_cout_hint" class="mt-1 text-xs text-slate-400"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Valeur de l'indicateur</label>
                            <input type="number" step="0.01" name="valeur_indicateur" id="eval_valeur_indicateur"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('evaluationModal').classList.add('hidden')"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-white">Annuler</button>
                        <button type="submit"
                            class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openEvaluationModal(data) {
                const form = document.getElementById('evaluationForm');
                form.action = data.action;
                document.getElementById('evaluationModalNom').textContent = data.nom || '';
                document.getElementById('eval_statut_execution').value = data.statut_execution || 'non_realise';
                document.getElementById('eval_observation').value = data.observation || '';
                document.getElementById('eval_montant_utilise').value = data.montant_utilise ?? '';
                document.getElementById('eval_valeur_indicateur').value = data.valeur_indicateur ?? '';
                const cout = Number(data.cout || 0);
                document.getElementById('eval_cout_hint').textContent =
                    'Budget planifié : ' + cout.toLocaleString('fr-FR') + ' FCFA';
                document.getElementById('evaluationModal').classList.remove('hidden');
            }
        </script>
    @endcan
</x-layouts::app>
