<x-layouts::app title="Modifier une activité">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Modifier l'activité</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ Str::limit($activite->nom_activite, 90) }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            <form action="{{ route('activites.update', $activite) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @include('pages.activites.partials.form-fields', ['activite' => $activite])

                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <a href="{{ route('activites.show', $activite) }}"
                        class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Annuler
                    </a>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
