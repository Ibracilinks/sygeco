<x-layouts::app title="Arbitrage / Validation">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Arbitrage / Validation</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ number_format($compteur) }} activité(s) soumise(s) par {{ $groupes->count() }} entité(s) —
                    {{ number_format($coutTotal, 0, ',', ' ') }} FCFA à arbitrer.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('validations.exporter') }}"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500">
                    Exporter (Cadre logique)
                </a>
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

        {{-- Toutes les entités ayant au moins une activité à arbitrer, par niveau hiérarchique --}}
        @php
            $libellesSections = [
                \App\Models\Departement::TYPE_DIRECTION => \App\Models\Departement::GROUPE_DIRECTIONS,
                \App\Models\Departement::TYPE_DEPARTEMENT => \App\Models\Departement::GROUPE_DIRECTIONS_CENTRALES,
                \App\Models\Departement::TYPE_SERVICE => 'Services',
                'autres' => 'Autres structures',
            ];
        @endphp

        @forelse ($sections as $type => $entites)
            <div>
                <div class="mb-3 flex items-center gap-3">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $libellesSections[$type] ?? $type }}</h2>
                    <span class="text-xs text-slate-400 dark:text-slate-500">
                        {{ $entites->count() }} entité(s) • {{ number_format($entites->sum('cout_total'), 0, ',', ' ') }} FCFA
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($entites as $groupe)
                        @php $entite = $groupe['departement']; @endphp
                        <a href="{{ $entite ? route('validations.entite', $entite) : '#' }}" wire:navigate
                            class="group flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 transition hover:border-slate-400 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-500">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-base font-semibold text-slate-900 group-hover:text-sky-700 dark:text-white dark:group-hover:text-sky-300">
                                        {{ $entite->nom ?? 'Structure non définie' }}
                                    </h3>
                                    <span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                                        {{ count($groupe['activites']) }} act.
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $entite?->typeLibelle() }}
                                    @if ($entite?->code) • {{ $entite->code }} @endif
                                    @if ($entite?->parent) <br>rattachée à {{ $entite->parent->nom }} @endif
                                </p>
                            </div>

                            <div class="mt-4 flex items-end justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Budget soumis</p>
                                    <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ number_format($groupe['cout_total'], 0, ',', ' ') }} <span class="text-xs font-normal">FCFA</span></p>
                                </div>
                                <span class="text-sm font-medium text-sky-700 dark:text-sky-300">Arbitrer →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm text-slate-500 dark:text-slate-400">Aucune activité en attente d'arbitrage.</p>
            </div>
        @endforelse
    </div>
</x-layouts::app>
