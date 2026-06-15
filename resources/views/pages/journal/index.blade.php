<x-layouts::app title="Journal">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Journal</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Historique des actions tracées par l'application.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('journal.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-4">
            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Rechercher"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">

            <select name="log_name" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous les modules</option>
                @foreach ($logNames as $logName)
                    <option value="{{ $logName }}" @selected($filters['log_name'] === $logName)>{{ $logName }}</option>
                @endforeach
            </select>

            <select name="event" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Tous les événements</option>
                @foreach ($events as $event)
                    <option value="{{ $event }}" @selected($filters['event'] === $event)>{{ $event }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white dark:bg-slate-200 dark:text-slate-900">Filtrer</button>
                <a href="{{ route('journal.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Module</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Événement</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Description</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Utilisateur</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Sujet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($activities as $entry)
                        @php($activity = $entry['model'])
                        <tr>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $activity->created_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $activity->log_name ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $entry['event_label'] }}</td>
                            <td class="px-5 py-4 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $entry['description'] }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $activity->causer?->name ?? 'Système' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-200">{{ $entry['subject_label'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Aucune entrée de journal trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $activities->links() }}</div>
    </div>
</x-layouts::app>
