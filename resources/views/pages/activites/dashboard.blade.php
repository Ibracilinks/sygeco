<x-layouts::app :title="__('Tableau de bord des activités')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tableau de bord</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Statistiques et indicateurs des activités
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('activites.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Nouvelle activité
                </a>
            </div>
        </div>

        {{-- Cartes statistiques --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
                <p class="text-sm text-gray-500">Total activités</p>
                <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
                <p class="text-sm text-gray-500">Activités actives</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['actives'] }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
                <p class="text-sm text-gray-500">Budget total</p>
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['budget_total'], 0, ',', ' ') }}
                    FCFA</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
                <p class="text-sm text-gray-500">Activités par extrant</p>
                <p class="text-2xl font-bold">{{ $activitesParExtrant->count() }}</p>
            </div>
        </div>

        {{-- Dernières activités --}}
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
            <div class="border-b p-4">
                <h3 class="text-lg font-semibold">Dernières activités créées</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Code</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Libellé</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Extrant</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Budget</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentActivites as $activite)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-sm">{{ $activite->code }}</td>
                                <td class="px-4 py-3">{{ Str::limit($activite->libelle, 40) }}</td>
                                <td class="px-4 py-3">{{ $activite->extrant->code ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    {{ number_format($activite->budget_previsionnel_global, 0, ',', ' ') }} FCFA</td>
                                <td class="px-4 py-3">
                                    @if ($activite->is_active)
                                        <span
                                            class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Actif</span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Inactif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $activite->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t p-3 text-center">
                <a href="{{ route('activites.index') }}" class="text-sm text-indigo-600">Voir toutes les activités
                    →</a>
            </div>
        </div>
    </div>
</x-layouts::app>
