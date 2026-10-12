<x-layouts::app title="Modifier l'exercice">
    @php
        $champ = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white';
        $libelle = 'mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200';

        $statuts = [
            'brouillon' => ['libelle' => 'Brouillon', 'aide' => 'Préparation, invisible pour les structures.'],
            'actif' => ['libelle' => 'Actif', 'aide' => 'Exercice de travail courant. Un seul à la fois.'],
            'cloture' => ['libelle' => 'Clôturé', 'aide' => 'Archivé, plus aucune saisie possible.'],
        ];
        $statutCourant = old('statut', $exercice->statut);
    @endphp

    <div class="mx-auto flex h-full w-full max-w-3xl flex-1 flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Modifier l'exercice {{ $exercice->annee }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Période et statut de l'exercice. Les dates de saisie, de mi-parcours et d'évaluation se règlent depuis le
                <a href="{{ route('exercices.show', $exercice) }}" class="font-medium text-slate-700 underline underline-offset-2 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white">calendrier de la fiche</a>.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                    {{ $errors->count() }} champ(s) à corriger :
                </p>
                <ul class="mt-2 space-y-1 text-sm text-red-700 dark:text-red-300">
                    @foreach ($errors->all() as $message)
                        <li class="flex gap-2"><span aria-hidden="true">•</span><span>{{ $message }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('exercices.update', $exercice) }}" method="POST"
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            @csrf
            @method('PUT')

            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                <section class="space-y-4 p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Période</h2>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <label for="annee" class="{{ $libelle }}">Année *</label>
                            <input type="number" id="annee" name="annee" min="2000" max="2100"
                                value="{{ old('annee', $exercice->annee) }}" class="{{ $champ }}">
                            @error('annee')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="date_debut" class="{{ $libelle }}">Date de début *</label>
                            <input type="date" id="date_debut" name="date_debut"
                                value="{{ old('date_debut', $exercice->date_debut->format('Y-m-d')) }}" class="{{ $champ }}">
                            @error('date_debut')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="date_fin" class="{{ $libelle }}">Date de fin *</label>
                            <input type="date" id="date_fin" name="date_fin"
                                value="{{ old('date_fin', $exercice->date_fin->format('Y-m-d')) }}" class="{{ $champ }}">
                            @error('date_fin')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <fieldset class="p-6">
                    <legend class="sr-only">Statut</legend>
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut *</h2>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @foreach ($statuts as $code => $statut)
                            <label class="cursor-pointer rounded-lg border p-3 transition has-[:checked]:border-slate-800 has-[:checked]:bg-slate-50 dark:has-[:checked]:border-slate-300 dark:has-[:checked]:bg-slate-800 {{ $statutCourant === $code ? 'border-slate-800 bg-slate-50 dark:border-slate-300 dark:bg-slate-800' : 'border-slate-300 dark:border-slate-700' }}">
                                <span class="flex items-center gap-2">
                                    <input type="radio" name="statut" value="{{ $code }}" @checked($statutCourant === $code)
                                        class="text-slate-800 focus:ring-slate-500 dark:text-slate-200">
                                    <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $statut['libelle'] }}</span>
                                </span>
                                <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $statut['aide'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('statut')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </fieldset>
            </div>

            <div class="flex gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                <a href="{{ route('exercices.index') }}"
                    class="inline-flex items-center rounded-lg bg-slate-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600">Annuler</a>
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
