@php
    $participantValues = old('participants', $participants ?? [['nom_complet' => '']]);
    $signataireValues = old('signataires', $signataires ?? [['libelle' => '', 'nom' => '', 'fonction' => '']]);
@endphp

@include('pages.missions.partials.erreurs')

<input type="hidden" name="type" value="{{ \App\Models\Mission::TYPE_MEME_VILLE }}">

<div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
    <div>
        <label for="reference" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Référence de l'ordre</label>
        <input type="text" id="reference" name="reference" value="{{ old('reference', $mission->reference) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        @error('reference')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="departement_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Structure demandeuse</label>
        <select id="departement_id" name="departement_id"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
            <option value="">Sans structure</option>
            @foreach ($departements as $departement)
                <option value="{{ $departement->id }}" @selected((string) old('departement_id', $mission->departement_id) === (string) $departement->id)>{{ $departement->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="statut" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Statut</label>
        <select id="statut" name="statut"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
            @foreach (\App\Models\Mission::STATUTS as $code => $label)
                <option value="{{ $code }}" @selected(old('statut', $mission->statut) === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="lieu_signature" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Lieu de signature</label>
        <input type="text" id="lieu_signature" name="lieu_signature" value="{{ old('lieu_signature', $mission->lieu_signature) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
    </div>
</div>

<div>
    <label for="objet" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Objet de la mission</label>
    <textarea id="objet" name="objet" rows="4"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">{{ old('objet', $mission->objet) }}</textarea>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
    <div>
        <label for="date_document" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date du document</label>
        <input type="date" id="date_document" name="date_document" value="{{ old('date_document', optional($mission->date_document)->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
    </div>
    <div>
        <label for="date_depart" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de départ</label>
        <input type="date" id="date_depart" name="date_depart" value="{{ old('date_depart', optional($mission->date_depart)->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
    </div>
    <div>
        <label for="date_retour" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de retour</label>
        <input type="date" id="date_retour" name="date_retour" value="{{ old('date_retour', optional($mission->date_retour)->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
    </div>
    <div>
        <label for="nombre_jours" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre de jours ouvrables</label>
        <input type="number" min="1" id="nombre_jours" name="nombre_jours" value="{{ old('nombre_jours', $mission->nombre_jours) }}"
            data-jours-ouvrables
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Calculé hors samedis et dimanches, modifiable.</p>
        @error('nombre_jours')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Rappel</p>
        <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">Le nombre de personnes est déduit automatiquement des participants.</p>
    </div>
</div>

{{-- Une mission dans la même ville n'ouvre droit qu'à des tickets de carburant : aucun barème monétaire. --}}
<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
    <div>
        <label for="tickets_carburant_par_jour" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tickets carburant / jour</label>
        <input type="number" min="0" id="tickets_carburant_par_jour" name="tickets_carburant_par_jour" value="{{ old('tickets_carburant_par_jour', $mission->tickets_carburant_par_jour) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        @error('tickets_carburant_par_jour')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Participants</h2>
            </div>
            <button type="button" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-100" data-add-row="participants">Ajouter</button>
        </div>
        <div id="participants-rows" class="space-y-3">
            @foreach ($participantValues as $index => $participant)
                <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
                    <div class="md:col-span-11">
                        <input type="text" name="participants[{{ $index }}][nom_complet]" value="{{ $participant['nom_complet'] ?? '' }}" placeholder="Prénoms et noms"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    </div>
                    <div class="md:col-span-1">
                        <button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>X</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Signataires</h2>
            </div>
            <button type="button" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-100" data-add-row="signataires">Ajouter</button>
        </div>
        <div id="signataires-rows" class="space-y-3">
            @foreach ($signataireValues as $index => $signataire)
                <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
                    <div class="md:col-span-4"><input type="text" name="signataires[{{ $index }}][libelle]" value="{{ $signataire['libelle'] ?? '' }}" placeholder="Libellé" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                    <div class="md:col-span-3"><input type="text" name="signataires[{{ $index }}][nom]" value="{{ $signataire['nom'] ?? '' }}" placeholder="Nom" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                    <div class="md:col-span-4"><input type="text" name="signataires[{{ $index }}][fonction]" value="{{ $signataire['fonction'] ?? '' }}" placeholder="Fonction (facultatif)" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                    <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>X</button></div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<template id="participant-row-template">
    <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
        <div class="md:col-span-11"><input type="text" data-name="nom_complet" placeholder="Prénoms et noms" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>X</button></div>
    </div>
</template>

<template id="signataire-row-template">
    <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
        <div class="md:col-span-4"><input type="text" data-name="libelle" placeholder="Libellé" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-3"><input type="text" data-name="nom" placeholder="Nom" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-4"><input type="text" data-name="fonction" placeholder="Fonction (facultatif)" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>X</button></div>
    </div>
</template>

@push('scripts')
    <script>
        (() => {
            // Jours ouvrables : samedis et dimanches exclus. La valeur reste modifiable,
            // et une saisie manuelle n'est plus écrasée par un changement de dates.
            const joursOuvrables = (start, end) => {
                let jours = 0;
                for (const jour = new Date(start); jour <= end; jour.setDate(jour.getDate() + 1)) {
                    const semaine = jour.getDay();
                    if (semaine !== 0 && semaine !== 6) jours += 1;
                }
                return Math.max(1, jours);
            };

            const updateMissionDays = () => {
                const depart = document.getElementById('date_depart')?.value;
                const retour = document.getElementById('date_retour')?.value;
                const output = document.getElementById('nombre_jours');
                if (!depart || !retour || !output || output.dataset.manuel === '1') return;
                const start = new Date(`${depart}T00:00:00`);
                const end = new Date(`${retour}T00:00:00`);
                if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) return;
                output.value = String(joursOuvrables(start, end));
            };

            const initRows = (collectionName, rowsId, templateId) => {
                const container = document.getElementById(rowsId);
                const template = document.getElementById(templateId);
                if (!container || !template) return;
                const renumber = () => {
                    [...container.querySelectorAll('[data-row]')].forEach((row, index) => {
                        row.querySelectorAll('[data-name]').forEach((input) => {
                            input.name = `${collectionName}[${index}][${input.dataset.name}]`;
                        });
                    });
                };
                document.querySelectorAll(`[data-add-row="${collectionName}"]`).forEach((button) => {
                    if (button.dataset.bound === '1') return;
                    button.dataset.bound = '1';
                    button.addEventListener('click', () => {
                        container.appendChild(template.content.cloneNode(true));
                        renumber();
                    });
                });
                if (container.dataset.bound !== '1') {
                    container.dataset.bound = '1';
                    container.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-remove-row]');
                        if (!button || container.querySelectorAll('[data-row]').length <= 1) return;
                        button.closest('[data-row]')?.remove();
                        renumber();
                    });
                }
                renumber();
            };

            const boot = () => {
                initRows('participants', 'participants-rows', 'participant-row-template');
                initRows('signataires', 'signataires-rows', 'signataire-row-template');

                const champJours = document.getElementById('nombre_jours');
                if (champJours && champJours.dataset.bound !== '1') {
                    champJours.dataset.bound = '1';
                    champJours.addEventListener('input', () => {
                        champJours.dataset.manuel = '1';
                    });
                }

                // Au chargement, on ne touche pas à une valeur déjà enregistrée.
                if (!champJours || !champJours.value) updateMissionDays();

                ['date_depart', 'date_retour'].forEach((id) => {
                    const field = document.getElementById(id);
                    if (field && field.dataset.bound !== '1') {
                        field.dataset.bound = '1';
                        field.addEventListener('change', updateMissionDays);
                    }
                });
            };

            document.addEventListener('DOMContentLoaded', boot);
            document.addEventListener('livewire:navigated', boot);
        })();
    </script>
@endpush
