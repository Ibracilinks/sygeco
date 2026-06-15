<x-layouts::app title="Créer un Département">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Nouveau département</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Créez l'entité, assignez un responsable et définissez son rang d'affichage.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            <form action="{{ route('departements.store') }}" method="POST" class="space-y-6">
                @csrf

                @include('pages.departements.partials.form-fields', ['departement' => null, 'users' => $users])

                <div class="flex gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <a href="{{ route('departements.index') }}" class="inline-flex items-center rounded-lg bg-slate-500 px-4 py-2 text-white transition hover:bg-slate-600">
                        Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                        Créer le département
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
