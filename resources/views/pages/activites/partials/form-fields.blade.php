<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="extrant_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Extrant *</label>
        <select id="extrant_id" name="extrant_id"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Sélectionnez un extrant</option>
            @foreach ($extrants as $extrant)
                <option value="{{ $extrant->id }}" @selected((string) old('extrant_id', $activite->extrant_id ?? $selectedExtrant ?? '') === (string) $extrant->id)>
                    {{ $extrant->code }} - {{ Str::limit($extrant->libelle, 60) }}
                </option>
            @endforeach
        </select>
        @error('extrant_id')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="departement_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Structure *</label>
        {{-- Liste déroulante restreinte au périmètre de l'utilisateur : un chef de Direction
             Centrale y retrouve sa DC et tous les services qu'elle chapeaute. --}}
        <select id="departement_id" name="departement_id" required
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Sélectionnez une structure</option>
            <x-departement-options :groupes="$departementsGroupes ?? \App\Models\Departement::grouperParDirectionCentrale($departements)"
                :selected="old('departement_id', $activite->departement_id ?? $departementId ?? '')" />
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Entités regroupées par Direction Centrale.</p>
        @error('departement_id')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    @php($structuresGroupesListe = $structuresGroupes ?? \App\Models\Departement::grouperParDirectionCentrale(\App\Models\Departement::active()->ordered()->get()))
    @php($structuresSelectionnees = collect(old('structures_intervenantes', isset($activite) ? $activite->departements->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->all())

    <div class="md:col-span-2">
        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Structures intervenantes</label>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                <span data-si-compteur>0</span> sélectionnée(s)
            </span>
        </div>

        <div data-si-widget
            class="rounded-lg border border-slate-300 bg-white dark:border-slate-700 dark:bg-slate-950">
            {{-- Barre d'outils : recherche et actions globales --}}
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 p-2 dark:border-slate-800">
                <div class="relative min-w-0 flex-1">
                    <input type="search" data-si-recherche placeholder="Rechercher une structure…"
                        class="w-full rounded-md border border-slate-300 bg-white py-1.5 pl-8 pr-3 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    <svg class="pointer-events-none absolute left-2.5 top-2 size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                    </svg>
                </div>
                <button type="button" data-si-vider
                    class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    Tout désélectionner
                </button>
            </div>

            {{-- Puces des structures retenues, retirables d'un clic --}}
            <div data-si-puces class="hidden flex-wrap gap-1.5 border-b border-slate-200 p-2 dark:border-slate-800"></div>

            {{-- Liste groupée par Direction Centrale --}}
            <div class="max-h-72 overflow-y-auto p-1">
                @foreach ($structuresGroupesListe as $groupe => $entites)
                    <div data-si-groupe class="mb-1">
                        <div class="flex items-center justify-between gap-2 rounded-md bg-slate-50 px-2.5 py-1.5 dark:bg-slate-900">
                            <p class="truncate text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $groupe }}</p>
                            <button type="button" data-si-groupe-toggle
                                class="shrink-0 text-xs font-medium text-sky-700 hover:underline dark:text-sky-300">Tout</button>
                        </div>

                        @foreach ($entites as $entite)
                            <label data-si-option data-si-nom="{{ Str::lower($entite->nom) }}"
                                class="flex cursor-pointer items-center gap-2 rounded-md px-2.5 py-1.5 text-sm text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                                <input type="checkbox" name="structures_intervenantes[]" value="{{ $entite->id }}"
                                    data-si-case data-si-libelle="{{ $entite->nom }}"
                                    @checked(in_array((string) $entite->id, $structuresSelectionnees, true))
                                    class="rounded border-slate-300 text-slate-800 dark:border-slate-600">
                                <span class="truncate">{{ $entite->nom }}</span>
                                @if ($entite->type !== \App\Models\Departement::TYPE_DEPARTEMENT)
                                    <span class="shrink-0 text-xs text-slate-400 dark:text-slate-500">({{ $entite->typeLibelle() }})</span>
                                @endif
                                <span data-si-porteuse class="ml-auto hidden shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Structure porteuse</span>
                            </label>
                        @endforeach
                    </div>
                @endforeach

                <p data-si-vide class="hidden px-2.5 py-6 text-center text-sm text-slate-500 dark:text-slate-400">Aucune structure ne correspond à cette recherche.</p>
            </div>
        </div>

        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            L'activité reste portée par une seule structure ; les intervenantes sont les entités qui y participent.
        </p>
        @error('structures_intervenantes')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
        @error('structures_intervenantes.*')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="nom_activite" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nom de l'activité *</label>
        <textarea id="nom_activite" name="nom_activite" rows="3"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">{{ old('nom_activite', $activite->nom_activite ?? '') }}</textarea>
        @error('nom_activite')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="indicateur_objectivement_verifiable" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Indicateur objectivement vérifiable *</label>
        <input id="indicateur_objectivement_verifiable" type="text" name="indicateur_objectivement_verifiable" value="{{ old('indicateur_objectivement_verifiable', $activite->indicateur_objectivement_verifiable ?? '') }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('indicateur_objectivement_verifiable')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="moyen_verification" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Moyen de vérification *</label>
        <input id="moyen_verification" type="text" name="moyen_verification" value="{{ old('moyen_verification', $activite->moyen_verification ?? '') }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('moyen_verification')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="cout" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Coût (FCFA) *</label>
        <input id="cout" type="number" name="cout" value="{{ old('cout', $activite->cout ?? '') }}" step="0.01"
            min="0" max="{{ \App\Models\Activite::MONTANT_MAX }}" required
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('cout')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <p class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Chronogramme * <span class="font-normal text-slate-500 dark:text-slate-400">(au moins une période)</span></p>
        <div class="grid grid-cols-2 gap-2 text-sm text-slate-700 dark:text-slate-200">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="trimestre_1" value="on" @checked(old('trimestre_1', isset($activite) ? $activite->trimestre_1 === 'oui' : false)) class="rounded border-slate-300 text-slate-800 dark:border-slate-700">T1</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="trimestre_2" value="on" @checked(old('trimestre_2', isset($activite) ? $activite->trimestre_2 === 'oui' : false)) class="rounded border-slate-300 text-slate-800 dark:border-slate-700">T2</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="trimestre_3" value="on" @checked(old('trimestre_3', isset($activite) ? $activite->trimestre_3 === 'oui' : false)) class="rounded border-slate-300 text-slate-800 dark:border-slate-700">T3</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="trimestre_4" value="on" @checked(old('trimestre_4', isset($activite) ? $activite->trimestre_4 === 'oui' : false)) class="rounded border-slate-300 text-slate-800 dark:border-slate-700">T4</label>
        </div>
        @error('chronogramme')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="commentaires" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Commentaires</label>
        <textarea id="commentaires" name="commentaires" rows="2"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">{{ old('commentaires', $activite->commentaires ?? '') }}</textarea>
        @error('commentaires')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
    // Sélecteur des structures intervenantes : recherche, puces retirables, sélection
    // par groupe, et neutralisation de la structure porteuse (elle est déjà le
    // rattachement principal et serait de toute façon écartée à l'enregistrement).
    (function () {
        const widget = document.querySelector('[data-si-widget]');
        if (!widget) return;

        const recherche = widget.querySelector('[data-si-recherche]');
        const zonePuces = widget.querySelector('[data-si-puces]');
        const compteur = document.querySelector('[data-si-compteur]');
        const messageVide = widget.querySelector('[data-si-vide]');
        const cases = () => Array.from(widget.querySelectorAll('[data-si-case]'));
        const porteuse = document.getElementById('departement_id')
            || document.querySelector('input[name="departement_id"]');

        function rafraichir() {
            const idPorteuse = porteuse ? String(porteuse.value || '') : '';
            const retenues = [];

            cases().forEach((c) => {
                const estPorteuse = idPorteuse !== '' && c.value === idPorteuse;
                const ligne = c.closest('[data-si-option]');

                // La structure porteuse ne peut pas être sa propre intervenante.
                c.disabled = estPorteuse;
                if (estPorteuse) c.checked = false;
                ligne.classList.toggle('opacity-50', estPorteuse);
                ligne.classList.toggle('cursor-not-allowed', estPorteuse);
                ligne.querySelector('[data-si-porteuse]').classList.toggle('hidden', !estPorteuse);

                if (c.checked) retenues.push(c);
            });

            compteur.textContent = retenues.length;

            zonePuces.innerHTML = '';
            zonePuces.classList.toggle('hidden', retenues.length === 0);
            zonePuces.classList.toggle('flex', retenues.length > 0);

            retenues.forEach((c) => {
                const puce = document.createElement('button');
                puce.type = 'button';
                puce.className = 'inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 transition hover:bg-rose-100 hover:text-rose-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-rose-900/40 dark:hover:text-rose-200';
                puce.textContent = c.dataset.siLibelle;
                puce.title = 'Retirer ' + c.dataset.siLibelle;
                puce.insertAdjacentHTML('beforeend', '<span aria-hidden="true">&times;</span>');
                puce.addEventListener('click', () => {
                    c.checked = false;
                    rafraichir();
                });
                zonePuces.appendChild(puce);
            });
        }

        function filtrer() {
            const terme = (recherche.value || '').trim().toLowerCase();
            let visibles = 0;

            widget.querySelectorAll('[data-si-groupe]').forEach((groupe) => {
                let visiblesDuGroupe = 0;

                groupe.querySelectorAll('[data-si-option]').forEach((option) => {
                    // Une structure retenue reste visible même hors recherche, pour
                    // qu'on puisse toujours la décocher depuis la liste.
                    const correspond = terme === ''
                        || option.dataset.siNom.includes(terme)
                        || option.querySelector('[data-si-case]').checked;

                    option.classList.toggle('hidden', !correspond);
                    if (correspond) visiblesDuGroupe++;
                });

                groupe.classList.toggle('hidden', visiblesDuGroupe === 0);
                visibles += visiblesDuGroupe;
            });

            messageVide.classList.toggle('hidden', visibles > 0);
        }

        widget.addEventListener('change', (e) => {
            if (e.target.matches('[data-si-case]')) rafraichir();
        });

        widget.querySelectorAll('[data-si-groupe-toggle]').forEach((bouton) => {
            bouton.addEventListener('click', () => {
                const options = Array.from(bouton.closest('[data-si-groupe]')
                    .querySelectorAll('[data-si-option]:not(.hidden) [data-si-case]:not(:disabled)'));
                const toutCoche = options.length > 0 && options.every((c) => c.checked);

                options.forEach((c) => { c.checked = ! toutCoche; });
                rafraichir();
            });
        });

        widget.querySelector('[data-si-vider]').addEventListener('click', () => {
            cases().forEach((c) => { c.checked = false; });
            rafraichir();
        });

        recherche.addEventListener('input', filtrer);
        if (porteuse) porteuse.addEventListener('change', rafraichir);

        rafraichir();
    })();
</script>
