{{--
    Corps partagé des deux pages d'évaluation. Chaque période a sa propre vue
    (mi-parcours / fin-annee) et n'affiche que ses propres données : aucune
    donnée de l'autre période n'apparaît ici.
--}}
@php
    use App\Models\ActiviteEvaluation;

    $periodeLibelle = ActiviteEvaluation::PERIODES[$periode];
    $slug = ActiviteEvaluation::slugDePeriode($periode);
@endphp

<x-layouts::app title="Évaluation — {{ $periodeLibelle }}">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Évaluation — {{ $periodeLibelle }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    État d'exécution, budget consommé et valeur d'indicateur pour la période
                    @if ($exercice) — exercice {{ $exercice->annee }} @endif.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('evaluations.export', array_merge([$slug], request()->query()), false) }}"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500">
                    Exporter (Cadre logique)
                </a>
                @can('create_activites')
                    <button type="button" onclick="document.getElementById('nonProgrammeeModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                        + Activité non programmée
                    </button>
                @endcan
            </div>
        </div>

        {{-- Retour des enregistrements faits sans rechargement de page. --}}
        <div id="evalFlash" class="hidden" role="status" aria-live="polite"></div>

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

        {{-- État de la fenêtre de saisie --}}
        @if ($exercice?->enPeriodeEvaluationPour($periode))
            <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 dark:border-sky-800 dark:bg-sky-950/40">
                <p class="text-sm font-medium text-sky-800 dark:text-sky-200">
                    🟢 Fenêtre de saisie {{ $periodeLibelle }} ouverte{{ $finFenetre ? " — jusqu'au ".$finFenetre->format('d/m/Y') : '' }}.
                </p>
            </div>
        @else
            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800 dark:bg-indigo-950/40">
                <p class="text-sm font-medium text-indigo-800 dark:text-indigo-200">
                    ✎ Hors période {{ $periodeLibelle }}{{ $debutFenetre && $finFenetre ? ' ('.$debutFenetre->format('d/m/Y').' → '.$finFenetre->format('d/m/Y').')' : '' }} — la saisie reste ouverte en permanence.
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
        <form method="GET" action="{{ route('evaluations.index', $slug, false) }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-5">
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
                <a href="{{ route('evaluations.index', $slug, false) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Activité</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $periodeLibelle }}</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Mise à jour</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Budget &amp; observations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($activites as $activite)
                        @php
                            $evaluation = $activite->evaluation($periode);
                            $montant = $evaluation?->montant_utilise;
                            $ecart = $montant !== null ? (float) $activite->cout - (float) $montant : null;
                        @endphp
                        <tr class="align-top" data-ligne-activite="{{ $activite->id }}">
                            <td class="px-5 py-4">
                                <a href="{{ route('activites.show', $activite, false) }}" class="text-sm font-semibold text-slate-900 transition hover:text-sky-700 hover:underline dark:text-white dark:hover:text-sky-300">
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
                            <td class="px-5 py-4" data-cellule="badge">
                                @if ($evaluation)
                                    <x-execution-badge :statut="$evaluation->statut_execution" />
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        Non évaluée
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500 dark:text-slate-400" data-cellule="maj">
                                @if ($evaluation?->maj_le)
                                    {{ $evaluation->maj_le->format('d/m/Y H:i') }}
                                    @if ($evaluation->majPar)<br>par {{ $evaluation->majPar->name }}@endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1 text-sm text-slate-600 dark:text-slate-300">
                                    <p data-cellule="observation">{{ $evaluation?->observation ?: '—' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Budget utilisé : <span data-cellule="montant">{{ $montant !== null ? number_format($montant, 0, ',', ' ').' FCFA' : '—' }}</span>
                                        <span data-cellule="ecart" class="{{ $ecart !== null && $ecart < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            @if ($ecart !== null)({{ $ecart < 0 ? 'dépassement' : 'écart' }} {{ number_format(abs($ecart), 0, ',', ' ') }})@endif
                                        </span>
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Valeur indicateur :
                                        <span data-cellule="valeur_indicateur">{{ $evaluation?->valeur_indicateur ?: '—' }}</span>
                                    </p>
                                </div>
                                @if (auth()->user()->can('evaluate_activites') && $peutSaisir)
                                    @php
                                        $evalData = [
                                            // Conserver le protocole du navigateur, même derrière un proxy HTTP.
                                            'action' => route('evaluations.enregistrer', [$activite, $slug], false),
                                            'nom' => $activite->nom_activite,
                                            'cout' => (float) $activite->cout,
                                            'statut_execution' => $evaluation->statut_execution ?? 'non_realise',
                                            'observation' => $evaluation?->observation,
                                            'montant_utilise' => $evaluation?->montant_utilise,
                                            'valeur_indicateur' => $evaluation?->valeur_indicateur,
                                        ];
                                    @endphp
                                    <button type="button"
                                        onclick="openEvaluationModal({{ Js::from($evalData + ['activite_id' => $activite->id]) }})"
                                        class="mt-2 inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                                        ✎ Renseigner {{ $periodeLibelle }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Aucune activité à évaluer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $activites->withPath(route('evaluations.index', $slug, false))->links() }}</div>
    </div>

    @can('create_activites')
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

                <form action="{{ route('activites.non-programmee.store', [], false) }}" method="POST" class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Intitulé de l'activité *</label>
                        <input type="text" name="nom_activite" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    </div>

                    @unless (auth()->user()->perimetreActivitesIds() === null)
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
                        <p class="mt-1 hidden text-sm text-rose-600 dark:text-rose-400" data-erreur="statut_execution"></p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Observation *</label>
                        <textarea name="observation" id="eval_observation" rows="2" maxlength="1000" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></textarea>
                        <p class="mt-1 hidden text-sm text-rose-600 dark:text-rose-400" data-erreur="observation"></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {{-- Budget consommé : réservé à l'administration et toujours facultatif. --}}
                        @if ($peutSaisirBudget)
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Budget utilisé (FCFA)</label>
                                <input type="number" step="0.01" min="0" max="{{ \App\Models\Activite::MONTANT_MAX }}" name="montant_utilise" id="eval_montant_utilise"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                <p id="eval_cout_hint" class="mt-1 text-xs text-slate-400"></p>
                                <p class="mt-1 hidden text-sm text-rose-600 dark:text-rose-400" data-erreur="montant_utilise"></p>
                            </div>
                        @endif
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Valeur de l'indicateur *</label>
                            <input type="text" maxlength="255" name="valeur_indicateur" id="eval_valeur_indicateur" required
                                placeholder="Ex. 12, « 3 sur 5 » ou « Rapport produit »" 
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            <p class="mt-1 hidden text-sm text-rose-600 dark:text-rose-400" data-erreur="valeur_indicateur"></p>
                        </div>
                    </div>

                    <p class="hidden rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:bg-rose-900/30 dark:text-rose-300" data-erreur="global"></p>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('evaluationModal').classList.add('hidden')"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-white">Annuler</button>
                        <button type="submit" id="eval_submit"
                            class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-slate-200 dark:text-slate-900">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            let ligneEnCours = null;

            function openEvaluationModal(data) {
                const form = document.getElementById('evaluationForm');
                form.action = data.action;
                ligneEnCours = data.activite_id ?? null;
                effacerErreurs(form);
                document.getElementById('evaluationModalNom').textContent = data.nom || '';
                document.getElementById('eval_statut_execution').value = data.statut_execution || 'non_realise';
                document.getElementById('eval_observation').value = data.observation || '';
                document.getElementById('eval_valeur_indicateur').value = data.valeur_indicateur ?? '';

                // Champ budget absent du formulaire pour les profils non administrateurs.
                const montant = document.getElementById('eval_montant_utilise');
                if (montant) {
                    montant.value = data.montant_utilise ?? '';
                    const cout = Number(data.cout || 0);
                    document.getElementById('eval_cout_hint').textContent =
                        'Budget planifié : ' + cout.toLocaleString('fr-FR') + ' FCFA';
                }

                document.getElementById('evaluationModal').classList.remove('hidden');
                verifierFormulaire();
            }

            const MESSAGES = {
                required: 'Ce champ est obligatoire.',
                min: 'La valeur ne peut pas être négative.',
                max: 'La valeur dépasse le maximum autorisé.',
            };

            /** Champs réellement présents : le budget est absent pour les non-administrateurs. */
            function champs(form) {
                return Array.from(form.querySelectorAll('[name]:not([type=hidden])'));
            }

            /**
             * Contrôle un champ et renvoie son message d'erreur, ou une chaîne vide.
             * On s'appuie sur la validité native (required, min, max, step) plutôt que
             * de réécrire les règles, pour rester aligné sur la validation serveur.
             */
            function erreurDe(champ) {
                const v = champ.validity;
                if (v.valid) return '';
                if (v.valueMissing) return MESSAGES.required;
                if (v.rangeUnderflow) return MESSAGES.min;
                if (v.rangeOverflow) return MESSAGES.max;
                return champ.validationMessage;
            }

            function afficherErreur(form, nom, message) {
                const cible = form.querySelector(`[data-erreur="${nom}"]`);
                if (!cible) return;
                cible.textContent = message || '';
                cible.classList.toggle('hidden', !message);

                const champ = form.querySelector(`[name="${nom}"]`);
                if (champ) {
                    champ.classList.toggle('border-rose-500', Boolean(message));
                    champ.classList.toggle('border-slate-300', !message);
                }
            }

            function effacerErreurs(form) {
                form.querySelectorAll('[data-erreur]').forEach((el) => {
                    el.textContent = '';
                    el.classList.add('hidden');
                });
                champs(form).forEach((c) => {
                    c.classList.remove('border-rose-500');
                    c.classList.add('border-slate-300');
                });
            }

            /** Active ou non le bouton selon l'état courant, sans rien afficher. */
            function verifierFormulaire() {
                const form = document.getElementById('evaluationForm');
                const bouton = document.getElementById('eval_submit');
                bouton.disabled = !champs(form).every((c) => c.validity.valid);
            }

            function rafraichirLigne(donnees) {
                if (!ligneEnCours) return;
                const ligne = document.querySelector(`[data-ligne-activite="${ligneEnCours}"]`);
                if (!ligne) return;

                const poser = (cle, html) => {
                    const cel = ligne.querySelector(`[data-cellule="${cle}"]`);
                    if (cel) cel.innerHTML = html;
                };

                poser('badge', donnees.badge);
                poser('maj', donnees.maj);
                poser('observation', donnees.observation);
                poser('montant', donnees.montant);
                poser('valeur_indicateur', donnees.valeur_indicateur);

                const ecart = ligne.querySelector('[data-cellule="ecart"]');
                if (ecart) {
                    ecart.innerHTML = donnees.ecart || '';
                    ecart.classList.toggle('text-rose-600', donnees.ecart_depassement);
                    ecart.classList.toggle('dark:text-rose-400', donnees.ecart_depassement);
                    ecart.classList.toggle('text-emerald-600', !donnees.ecart_depassement);
                    ecart.classList.toggle('dark:text-emerald-400', !donnees.ecart_depassement);
                }

                ligne.classList.add('bg-emerald-50', 'dark:bg-emerald-900/20');
                setTimeout(() => ligne.classList.remove('bg-emerald-50', 'dark:bg-emerald-900/20'), 1500);
            }

            function annoncer(message, succes = true) {
                const zone = document.getElementById('evalFlash');
                zone.textContent = message;
                zone.className = succes
                    ? 'mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-200'
                    : 'mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-900/30 dark:text-rose-200';
                zone.classList.remove('hidden');
                zone.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('evaluationForm');
                if (!form) return;

                // Validation au fil de la saisie : le message n'apparaît qu'une fois le
                // champ quitté ou corrigé, pour ne pas invalider dès la première frappe.
                form.addEventListener('input', (e) => {
                    if (!e.target.name) return;
                    const dejaSignale = !form.querySelector(`[data-erreur="${e.target.name}"]`)?.classList.contains('hidden');
                    if (dejaSignale) afficherErreur(form, e.target.name, erreurDe(e.target));
                    verifierFormulaire();
                });

                form.addEventListener('focusout', (e) => {
                    if (!e.target.name) return;
                    afficherErreur(form, e.target.name, erreurDe(e.target));
                });

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    let valide = true;
                    champs(form).forEach((c) => {
                        const message = erreurDe(c);
                        afficherErreur(form, c.name, message);
                        if (message) valide = false;
                    });
                    if (!valide) return;

                    const bouton = document.getElementById('eval_submit');
                    const libelle = bouton.textContent;
                    bouton.disabled = true;
                    bouton.textContent = 'Enregistrement…';

                    try {
                        const reponse = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: new FormData(form),
                        });
                        const donnees = await reponse.json().catch(() => ({}));

                        if (reponse.ok) {
                            rafraichirLigne(donnees.ligne || {});
                            document.getElementById('evaluationModal').classList.add('hidden');
                            annoncer(donnees.message || 'Évaluation enregistrée.');
                            return;
                        }

                        // 422 : erreurs de validation renvoyées par le serveur.
                        if (donnees.errors) {
                            Object.entries(donnees.errors).forEach(([nom, messages]) => {
                                afficherErreur(form, nom, messages[0]);
                            });
                        }
                        afficherErreur(form, 'global', donnees.errors ? '' : (donnees.message || 'Enregistrement impossible.'));
                    } catch (erreur) {
                        afficherErreur(form, 'global', 'Connexion interrompue : réessayez.');
                    } finally {
                        bouton.textContent = libelle;
                        verifierFormulaire();
                    }
                });
            });
        </script>
    @endcan
</x-layouts::app>
