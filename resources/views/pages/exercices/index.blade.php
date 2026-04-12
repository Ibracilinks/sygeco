<x-layouts::app title="Exercices">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center flex-wrap gap-3">
            <h1 class="text-2xl font-bold dark:text-white">Exercices</h1>
            @can('manage_exercices')
                <a href="{{ route('exercices.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm">
                    Nouvel exercice
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 p-4">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 p-4">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Année</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Période</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @foreach ($exercices as $exercice)
                        @php
                            $isActiveContext = \App\Support\ActiveExercice::id() === (int) $exercice->id;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 font-semibold dark:text-white">{{ $exercice->annee }}</td>
                            <td class="px-6 py-4 text-sm dark:text-zinc-300">
                                {{ $exercice->date_debut->format('d/m/Y') }} — {{ $exercice->date_fin->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $colors = [
                                        'brouillon' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-100',
                                        'actif' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                                        'cloture' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-100',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colors[$exercice->statut] ?? $colors['brouillon'] }}">
                                    {{ ucfirst($exercice->statut) }}
                                </span>
                                @if ($isActiveContext)
                                    <span
                                        class="ml-2 inline-flex rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200 px-2 py-0.5 text-xs font-medium">Filtré</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <form action="{{ route('exercices.activate', $exercice) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">Utiliser</button>
                                </form>
                                <a href="{{ route('exercices.show', $exercice) }}"
                                    class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400">Voir</a>
                                @can('manage_exercices')
                                    <a href="{{ route('exercices.edit', $exercice) }}"
                                        class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400">Modifier</a>
                                    <form action="{{ route('exercices.destroy', $exercice) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Supprimer cet exercice ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Supprimer</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $exercices->links() }}
        </div>
    </div>
</x-layouts::app>
