<x-layouts::app title="Créer une Activité">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Créer une activité</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Saisissez une nouvelle activité réalisée par votre
                département</p>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
            <form action="{{ route('activites.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Extrant -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Extrant *</label>
                        <select name="extrant_id"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Sélectionnez un extrant</option>
                            @foreach ($extrants as $extrant)
                                <option value="{{ $extrant->id }}"
                                    {{ old('extrant_id', $selectedExtrant) == $extrant->id ? 'selected' : '' }}>
                                    {{ $extrant->code }} - {{ Str::limit($extrant->libelle, 60) }}
                                </option>
                            @endforeach
                        </select>
                        @error('extrant_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Département -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Département *</label>
                        @if (auth()->user()->hasRole('chef_departement') && auth()->user()->departement_id)
                            <input type="hidden" name="departement_id" value="{{ auth()->user()->departement_id }}">
                            <div
                                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-neutral-100 dark:bg-zinc-700 px-3 py-2 text-zinc-600 dark:text-zinc-300">
                                {{ auth()->user()->departement->nom ?? 'Votre département' }}
                            </div>
                        @else
                            <select name="departement_id"
                                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Sélectionnez un département</option>
                                @foreach ($departements as $departement)
                                    <option value="{{ $departement->id }}"
                                        {{ old('departement_id') == $departement->id ? 'selected' : '' }}>
                                        {{ $departement->nom }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('departement_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nom de l'activité -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Nom de l'activité *</label>
                        <textarea name="nom_activite" rows="3"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Décrivez l'activité réalisée">{{ old('nom_activite') }}</textarea>
                        @error('nom_activite')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Indicateur objectivement vérifiable -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Indicateur objectivement
                            vérifiable *</label>
                        <input type="text" name="indicateur_objectivement_verifiable"
                            value="{{ old('indicateur_objectivement_verifiable') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: Nombre de personnes formées, Taux de réalisation, etc.">
                        @error('indicateur_objectivement_verifiable')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Moyen de vérification -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Moyen de vérification *</label>
                        <input type="text" name="moyen_verification" value="{{ old('moyen_verification') }}"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: Rapport d'activité, PV de réunion, Facture, etc.">
                        @error('moyen_verification')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Coût -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Coût (FCFA) *</label>
                        <input type="number" name="cout" value="{{ old('cout') }}" step="0.01"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0">
                        @error('cout')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Chronogramme (trimestres) -->
                    <div>
                        <label class="block text-sm font-medium dark:text-white mb-2">Chronogramme (trimestres
                            concernés)</label>
                        <div class="flex flex-wrap gap-4 mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="trimestre_1" value="on"
                                    {{ old('trimestre_1') ? 'checked' : '' }}
                                    class="rounded border-neutral-300 dark:border-neutral-600 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 dark:text-white">T1 (Janv - Mars)</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="trimestre_2" value="on"
                                    {{ old('trimestre_2') ? 'checked' : '' }}
                                    class="rounded border-neutral-300 dark:border-neutral-600 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 dark:text-white">T2 (Avril - Juin)</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="trimestre_3" value="on"
                                    {{ old('trimestre_3') ? 'checked' : '' }}
                                    class="rounded border-neutral-300 dark:border-neutral-600 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 dark:text-white">T3 (Juillet - Sept)</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="trimestre_4" value="on"
                                    {{ old('trimestre_4') ? 'checked' : '' }}
                                    class="rounded border-neutral-300 dark:border-neutral-600 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 dark:text-white">T4 (Oct - Déc)</span>
                            </label>
                        </div>
                        @error('trimestre_1')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Commentaires -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium dark:text-white mb-2">Commentaires (optionnel)</label>
                        <textarea name="commentaires" rows="2"
                            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-zinc-900 px-3 py-2 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Informations complémentaires">{{ old('commentaires') }}</textarea>
                        @error('commentaires')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <a href="{{ route('activites.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Créer l'activité
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
