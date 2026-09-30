<x-layouts::app title="Barèmes des missions">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Barèmes des missions</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Montants appliqués au calcul des ordres de mission. Les missions déjà enregistrées gardent les montants
                calculés lors de leur saisie : un nouveau barème ne s'applique qu'aux missions créées ou rééditées ensuite.
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950">
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ $errors->count() }} champ(s) à corriger :</p>
                <ul class="mt-2 space-y-1 text-sm text-red-700 dark:text-red-300">
                    @foreach ($errors->all() as $message)
                        <li class="flex gap-2"><span aria-hidden="true">•</span><span>{{ $message }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mission-baremes.update') }}" method="POST" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            @foreach (\App\Models\MissionBareme::GROUPES as $groupe => $titre)
                @php($lignes = $groupes[$groupe] ?? collect())
                @continue($lignes->isEmpty())

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $titre }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $groupe === \App\Models\MissionBareme::GROUPE_ZONE
                                ? 'Le taux majore le sous-total (frais de mission + indemnités) des missions à l\'étranger.'
                                : 'Frais de mission comptés par jour, indemnités comptées par nuitée.' }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Code</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Libellé</th>
                                    @if ($groupe === \App\Models\MissionBareme::GROUPE_ZONE)
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Taux de majoration (%)</th>
                                    @else
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Correspondance</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Frais / jour</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Indemnités / nuitée</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($lignes as $bareme)
                                    <tr>
                                        <td class="px-5 py-3 align-top">
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-mono text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $bareme->code }}</span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <input type="text" name="baremes[{{ $bareme->id }}][libelle]"
                                                value="{{ old('baremes.'.$bareme->id.'.libelle', $bareme->libelle) }}"
                                                class="w-full min-w-56 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                        </td>
                                        @if ($groupe === \App\Models\MissionBareme::GROUPE_ZONE)
                                            <td class="px-5 py-3">
                                                <input type="number" step="0.01" min="0" max="100" name="baremes[{{ $bareme->id }}][taux]"
                                                    value="{{ old('baremes.'.$bareme->id.'.taux', (float) $bareme->taux) }}"
                                                    class="w-32 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                            </td>
                                        @else
                                            <td class="px-5 py-3">
                                                <input type="text" name="baremes[{{ $bareme->id }}][description]"
                                                    value="{{ old('baremes.'.$bareme->id.'.description', $bareme->description) }}"
                                                    class="w-full min-w-72 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                            </td>
                                            <td class="px-5 py-3">
                                                <input type="number" step="0.01" min="0" name="baremes[{{ $bareme->id }}][frais_mission]"
                                                    value="{{ old('baremes.'.$bareme->id.'.frais_mission', (float) $bareme->frais_mission) }}"
                                                    class="w-36 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                            </td>
                                            <td class="px-5 py-3">
                                                <input type="number" step="0.01" min="0" name="baremes[{{ $bareme->id }}][indemnites]"
                                                    value="{{ old('baremes.'.$bareme->id.'.indemnites', (float) $bareme->indemnites) }}"
                                                    class="w-36 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="flex gap-3">
                <a href="{{ route('missions.index') }}"
                    class="inline-flex items-center rounded-lg bg-slate-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600">Annuler</a>
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Enregistrer les barèmes
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
