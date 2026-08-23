<x-layouts::app title="Créer un exercice">
    <div class="mx-auto flex h-full w-full max-w-3xl flex-1 flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Nouvel exercice</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                L'essentiel suffit pour démarrer : l'année, la période couverte et le statut.
                Les fenêtres de saisie, de suivi à mi-parcours et d'évaluation se règlent ensuite depuis la fiche de l'exercice.
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

        <form action="{{ route('exercices.store') }}" method="POST"
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            @csrf

            <div class="space-y-6 p-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label for="annee" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Année *</label>
                        <input type="number" id="annee" name="annee" min="2000" max="2100" value="{{ old('annee', date('Y')) }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        @error('annee')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="date_debut" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de début *</label>
                        <input type="date" id="date_debut" name="date_debut" value="{{ old('date_debut') }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        @error('date_debut')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="date_fin" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de fin *</label>
                        <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin') }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        @error('date_fin')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <fieldset>
                    <legend class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">Statut *</legend>
                    @php
                        $statuts = [
                            'brouillon' => ['libelle' => 'Brouillon', 'aide' => 'Préparation, invisible pour les structures.'],
                            'actif' => ['libelle' => 'Actif', 'aide' => 'Exercice de travail courant. Un seul à la fois.'],
                            'cloture' => ['libelle' => 'Clôturé', 'aide' => 'Archivé, plus aucune saisie possible.'],
                        ];
                        $statutCourant = old('statut', 'brouillon');
                    @endphp
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

                <p class="rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:bg-slate-950 dark:text-slate-400">
                    Après l'enregistrement, ouvrez la fiche de l'exercice pour définir l'ouverture et la date limite de saisie,
                    les fenêtres de mi-parcours et d'évaluation — ce sont elles qui déclenchent les relances et ouvrent les écrans de suivi.
                </p>
            </div>

            <div class="flex gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                <a href="{{ route('exercices.index') }}"
                    class="inline-flex items-center rounded-lg bg-slate-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600">Annuler</a>
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    Enregistrer l'exercice
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // L'année pilote la période par défaut, tant que les dates n'ont pas été saisies à la main.
            (() => {
                const boot = () => {
                    const annee = document.getElementById('annee');
                    const debut = document.getElementById('date_debut');
                    const fin = document.getElementById('date_fin');
                    if (!annee || !debut || !fin || annee.dataset.bound === '1') return;
                    annee.dataset.bound = '1';

                    const proposerPeriode = () => {
                        const valeur = Number(annee.value);
                        if (!Number.isInteger(valeur) || valeur < 2000 || valeur > 2100) return;
                        if (!debut.value || debut.dataset.auto === '1') {
                            debut.value = `${valeur}-01-01`;
                            debut.dataset.auto = '1';
                        }
                        if (!fin.value || fin.dataset.auto === '1') {
                            fin.value = `${valeur}-12-31`;
                            fin.dataset.auto = '1';
                        }
                    };

                    [debut, fin].forEach((champ) => champ.addEventListener('input', () => {
                        champ.dataset.auto = '0';
                    }));

                    annee.addEventListener('input', proposerPeriode);
                    proposerPeriode();
                };

                document.addEventListener('DOMContentLoaded', boot);
                document.addEventListener('livewire:navigated', boot);
            })();
        </script>
    @endpush
</x-layouts::app>
