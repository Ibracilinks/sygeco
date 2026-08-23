<x-layouts::app title="Nouvelle mission">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        {{-- Le type vient de la rubrique d'où l'on arrive : un seul formulaire à l'écran, sans onglets. --}}
        @php $libelleType = \App\Models\Mission::TYPES[$mission->type] ?? $mission->type; @endphp

        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Nouvelle mission — {{ $libelleType }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Saisie dédiée aux missions {{ mb_strtolower($libelleType) }}.</p>
            @if ($precedente)
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Signataires et barèmes préremplis depuis la mission {{ $precedente->reference }}.</p>
            @endif
        </div>

        <div>
            <span class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-slate-900">
                {{ $libelleType }}
            </span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            <form action="{{ route('missions.store') }}" method="POST" class="space-y-6">
                @csrf
                @include($formPartial)

                <div class="flex gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <a href="{{ route('missions.index') }}" class="inline-flex items-center rounded-lg bg-slate-500 px-4 py-2 text-white transition hover:bg-slate-600">
                        Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                        Enregistrer la mission
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
