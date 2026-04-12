<x-layouts::app title="Exercice {{ $exercice->annee }}">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">Exercice {{ $exercice->annee }}</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $exercice->objectifs_count }} objectif(s)</p>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('exercices.activate', $exercice) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm">Filtrer sur cet
                        exercice</button>
                </form>
                @can('manage_exercices')
                    <a href="{{ route('exercices.edit', $exercice) }}"
                        class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 text-sm dark:text-white">Modifier</a>
                @endcan
            </div>
        </div>
    </div>
</x-layouts::app>
