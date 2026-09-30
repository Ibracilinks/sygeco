<x-layouts::app title="Modifier une mission">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Modifier la mission</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Édition dédiée au type {{ \App\Models\Mission::TYPES[$mission->type] ?? $mission->type }}.</p>
        </div>

        <div>
            <span class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-slate-900">
                {{ \App\Models\Mission::TYPES[$mission->type] ?? $mission->type }}
            </span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            <form action="{{ route('missions.update', $mission) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                @include($formPartial)

                <div class="flex gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <a href="{{ route('missions.show', $mission) }}" class="inline-flex items-center rounded-lg bg-slate-500 px-4 py-2 text-white transition hover:bg-slate-600">
                        Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
