<x-layouts::app title="Saisie des valeurs - {{ $indicateur->code }}">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Saisie des valeurs</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Indicateur: {{ $indicateur->libelle }}</p>
            <p class="text-sm text-zinc-500">Périodicité: {{ $indicateur->periodicite_label }} | Unité:
                {{ $indicateur->unite ?? 'Non définie' }}</p>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
            <form action="{{ route('indicateurs.store-valeurs', $indicateur) }}" method="POST" class="space-y-6">
                @csrf

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-zinc-900">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                    Période</th>
                                @foreach ($departements as $departement)
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                        {{ $departement->nom }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach ($periodes as $periode)
                                <tr>
                                    <td class="px-4 py-3 font-medium dark:text-white">{{ $periode }}</td>
                                    @foreach ($departements as $index => $departement)
                                        <td class="px-4 py-3">
                                            <input type="number"
                                                name="valeurs[{{ $loop->parent->index }}][{{ $index }}][valeur_realisee]"
                                                step="0.01"
                                                class="w-32 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="Valeur">
                                            <input type="hidden"
                                                name="valeurs[{{ $loop->parent->index }}][{{ $index }}][departement_id]"
                                                value="{{ $departement->id }}">
                                            <input type="hidden"
                                                name="valeurs[{{ $loop->parent->index }}][{{ $index }}][periode]"
                                                value="{{ $periode }}">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <a href="{{ route('indicateurs.show', $indicateur) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Enregistrer les valeurs
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
