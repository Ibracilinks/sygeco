<x-layouts::app title="Suivi des activités">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Suivi des activités</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">État d'avancement (réalisé / en cours / non réalisé) et observations — exercice en contexte.</p>
            </div>
            @can('edit_activites')
                <button type="button" onclick="document.getElementById('nonProgrammeeModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    + Activité non programmée
                </button>
            @endcan
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

        {{-- État de la fenêtre de saisie de l'exécution --}}
        @if ($periodeSuivi === 'mi_parcours')
            <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 dark:border-sky-800 dark:bg-sky-950/40">
                <p class="text-sm font-medium text-sky-800 dark:text-sky-200">
                    🟢 Période de suivi à mi-parcours ouverte{{ $exercice?->date_fin_mi_parcours ? ' — jusqu\'au ' . $exercice->date_fin_mi_parcours->format('d/m/Y') : '' }}.
                    Renseignez l'état d'exécution de vos activités.
                </p>
            </div>
        @elseif ($periodeSuivi === 'evaluation')
            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800 dark:bg-indigo-950/40">
                <p class="text-sm font-medium text-indigo-800 dark:text-indigo-200">
                    🟢 Période d'évaluation de fin d'exercice ouverte{{ $exercice?->date_fin_evaluation ? ' — jusqu\'au ' . $exercice->date_fin_evaluation->format('d/m/Y') : '' }}.
                    Finalisez l'état d'exécution de vos activités.
                </p>
            </div>
        @elseif (! $peutSaisirExecution)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/40">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    🔒 Aucune fenêtre de saisie ouverte. La mise à jour de l'exécution n'est possible que pendant les périodes de mi-parcours ou d'évaluation.
                </p>
            </div>
        @endif

        {{-- KPI avancement --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['total']) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/25">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Réalisé</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($summary['realise']) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/25">
                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">En cours</p>
                <p class="mt-2 text-3xl font-semibold text-amber-800 dark:text-amber-100">{{ number_format($summary['en_cours']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Non réalisé</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['non_realise']) }}</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/60 dark:bg-sky-950/25">
                <p class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-300">Taux de réalisation</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-100">{{ $summary['taux_realisation'] }}%</p>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" action="{{ route('activites.suivi') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-5">
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
                @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}" @selected((string) ($filters['departement_id'] ?? '') === (string) $departement->id)>{{ $departement->nom }}</option>
                @endforeach
            </select>

            <select name="statut_execution" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tout suivi</option>
                @foreach (\App\Models\Activite::STATUTS_EXECUTION as $val => $label)
                    <option value="{{ $val }}" @selected(($filters['statut_execution'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Filtrer</button>
                <a href="{{ route('activites.suivi') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Activité</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Suivi actuel</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Mise à jour</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">État &amp; observations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($activites as $activite)
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
                                <p class="mt-2 text-xs font-medium text-sky-700 dark:text-sky-300">Voir le détail, les observations et les pièces jointes</p>
                            </td>
                            <td class="px-5 py-4">
                                <x-execution-badge :statut="$activite->statut_execution" />
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500 dark:text-slate-400">
                                @if ($activite->execution_maj_le)
                                    {{ $activite->execution_maj_le->format('d/m/Y H:i') }}
                                    @if ($activite->executionMajPar)<br>par {{ $activite->executionMajPar->name }}@endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if (auth()->user()->can('edit_activites') && $peutSaisirExecution)
                                    <form action="{{ route('activites.execution', $activite) }}" method="POST" class="flex flex-col gap-2 sm:flex-row sm:items-start">
                                        @csrf
                                        <select name="statut_execution"
                                            class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                            @foreach (\App\Models\Activite::STATUTS_EXECUTION as $val => $label)
                                                <option value="{{ $val }}" @selected($activite->statut_execution === $val)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="execution_commentaire" maxlength="1000"
                                            value="{{ $activite->execution_commentaire }}" placeholder="Observation"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 sm:w-64">
                                        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-medium text-white dark:bg-slate-200 dark:text-slate-900">Enregistrer</button>
                                    </form>
                                @else
                                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ $activite->execution_commentaire ?: '—' }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Aucune activité à suivre.</td></tr>
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
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Activité hors plan de travail annuel, rattachée à l'exercice en cours{{ $exercice ? ' (' . $exercice->annee . ')' : '' }}.</p>
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
                                @foreach ($departements as $departement)
                                    <option value="{{ $departement->id }}">{{ $departement->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="departement_id" value="{{ auth()->user()->departement_id }}">
                    @endunless

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Coût (FCFA) *</label>
                        <input type="number" name="cout" min="0" step="1" required
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
    @endcan
</x-layouts::app>
