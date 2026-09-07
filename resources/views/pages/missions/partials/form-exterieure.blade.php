@php
    $participantValues = old('participants', $participants ?? [['nom_complet' => '', 'categorie' => null, 'nombre_nuitees' => null]]);
    $signataireValues = old('signataires', $signataires ?? [['libelle' => '', 'nom' => '', 'fonction' => '']]);
@endphp

@include('pages.missions.partials.erreurs')

<input type="hidden" name="type" value="{{ \App\Models\Mission::TYPE_EXTERIEURE }}">

<div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
    <div><label for="reference" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Référence de l'ordre</label><input type="text" id="reference" name="reference" value="{{ old('reference', $mission->reference) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
    @include('pages.missions.partials.services-demandeurs')
    <div><label for="destination" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Destination</label><input type="text" id="destination" name="destination" value="{{ old('destination', $mission->destination) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
    <div><label for="zone_code" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Zone de majoration</label><select id="zone_code" name="zone_code" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"><option value="">Choisir une zone</option>@foreach (\App\Models\Mission::zonesExterieures() as $code => $zone)<option value="{{ $code }}" @selected(old('zone_code', $mission->zone_code) === $code)>{{ $zone['label'] }} ({{ $zone['taux'] }}%)</option>@endforeach</select></div>
    <div><label for="statut" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Statut</label><select id="statut" name="statut" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@foreach (\App\Models\Mission::STATUTS as $code => $label)<option value="{{ $code }}" @selected(old('statut', $mission->statut ?? 'brouillon') === $code)>{{ $label }}</option>@endforeach</select></div>
</div>

<div>
    <label for="objet" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Objet de la mission</label>
    <textarea id="objet" name="objet" rows="4" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">{{ old('objet', $mission->objet) }}</textarea>
</div>

@include('pages.missions.partials.code-budgetaire')

<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
    <div><label for="date_depart" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de départ</label><input type="date" id="date_depart" name="date_depart" value="{{ old('date_depart', optional($mission->date_depart)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
    <div><label for="date_retour" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de retour</label><input type="date" id="date_retour" name="date_retour" value="{{ old('date_retour', optional($mission->date_retour)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
    <div><label for="nombre_jours" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre de jours</label><input type="number" min="1" id="nombre_jours" name="nombre_jours" value="{{ old('nombre_jours', $mission->nombre_jours) }}" readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"></div>
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <h2 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Autres frais</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div><label for="frais_participation_nombre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nb personnes participation</label><input type="number" min="0" id="frais_participation_nombre" name="frais_participation_nombre" value="{{ old('frais_participation_nombre', $mission->frais_participation_nombre) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
            <div><label for="frais_participation_unitaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Montant unitaire participation</label><input type="number" step="0.01" min="0" id="frais_participation_unitaire" name="frais_participation_unitaire" value="{{ old('frais_participation_unitaire', $mission->frais_participation_unitaire) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
            <div><label for="frais_visa_nombre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nb personnes visa</label><input type="number" min="0" id="frais_visa_nombre" name="frais_visa_nombre" value="{{ old('frais_visa_nombre', $mission->frais_visa_nombre) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
            <div><label for="frais_visa_unitaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Montant unitaire visa</label><input type="number" step="0.01" min="0" id="frais_visa_unitaire" name="frais_visa_unitaire" value="{{ old('frais_visa_unitaire', $mission->frais_visa_unitaire) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <h2 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Billets d'avion</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div><label for="billets_affaire_nombre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nb billets affaire</label><input type="number" min="0" id="billets_affaire_nombre" name="billets_affaire_nombre" value="{{ old('billets_affaire_nombre', $mission->billets_affaire_nombre) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
            <div><label for="billets_affaire_unitaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Montant unitaire affaire</label><input type="number" step="0.01" min="0" id="billets_affaire_unitaire" name="billets_affaire_unitaire" value="{{ old('billets_affaire_unitaire', $mission->billets_affaire_unitaire) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
            <div><label for="billets_economique_nombre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nb billets économique</label><input type="number" min="0" id="billets_economique_nombre" name="billets_economique_nombre" value="{{ old('billets_economique_nombre', $mission->billets_economique_nombre) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
            <div><label for="billets_economique_unitaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Montant unitaire économique</label><input type="number" step="0.01" min="0" id="billets_economique_unitaire" name="billets_economique_unitaire" value="{{ old('billets_economique_unitaire', $mission->billets_economique_unitaire) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between gap-3"><div><h2 class="text-sm font-semibold text-slate-900 dark:text-white">Participants <span class="font-normal text-slate-400" data-row-count="participants">(1)</span></h2><p class="text-xs text-slate-500 dark:text-slate-400">Nuitées calculées automatiquement : nombre de jours − 1.</p></div><button type="button" title="Ajouter un participant (ou appuyez sur Entrée dans le dernier champ)" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600" data-add-row="participants">+ Ajouter</button></div>
        <div id="participants-rows" class="space-y-3">
            @foreach ($participantValues as $index => $participant)
                <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
                    <div class="md:col-span-7"><input type="text" name="participants[{{ $index }}][nom_complet]" value="{{ $participant['nom_complet'] ?? '' }}" placeholder="Prénoms et noms" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                    <div class="md:col-span-4"><select name="participants[{{ $index }}][categorie]" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"><option value="">Catégorie</option>@foreach (\App\Models\Mission::categoriesExterieures() as $code => $categorie)<option value="{{ $code }}" @selected(($participant['categorie'] ?? null) === $code)>{{ $categorie['label'] }}</option>@endforeach</select></div>
                    <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>&times;</button></div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between gap-3"><h2 class="text-sm font-semibold text-slate-900 dark:text-white">Signataires <span class="font-normal text-slate-400" data-row-count="signataires">(1)</span></h2><button type="button" title="Ajouter un signataire (ou appuyez sur Entrée dans le dernier champ)" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600" data-add-row="signataires">+ Ajouter</button></div>
        <div id="signataires-rows" class="space-y-3">
            @foreach ($signataireValues as $index => $signataire)
                <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
                    <div class="md:col-span-4"><input type="text" name="signataires[{{ $index }}][libelle]" value="{{ $signataire['libelle'] ?? '' }}" placeholder="Libellé" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                    <div class="md:col-span-3"><input type="text" name="signataires[{{ $index }}][nom]" value="{{ $signataire['nom'] ?? '' }}" placeholder="Nom" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                    <div class="md:col-span-4"><input type="text" name="signataires[{{ $index }}][fonction]" value="{{ $signataire['fonction'] ?? '' }}" placeholder="Fonction (facultatif)" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                    <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>&times;</button></div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<template id="participant-row-template">
    <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
        <div class="md:col-span-7"><input type="text" data-name="nom_complet" placeholder="Prénoms et noms" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-4"><select data-name="categorie" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"><option value="">Catégorie</option>@foreach (\App\Models\Mission::categoriesExterieures() as $code => $categorie)<option value="{{ $code }}">{{ $categorie['label'] }}</option>@endforeach</select></div>
        <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>&times;</button></div>
    </div>
</template>

<template id="signataire-row-template">
    <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
        <div class="md:col-span-4"><input type="text" data-name="libelle" placeholder="Libellé" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-3"><input type="text" data-name="nom" placeholder="Nom" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-4"><input type="text" data-name="fonction" placeholder="Fonction (facultatif)" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>&times;</button></div>
    </div>
</template>

@push('scripts')
    <script>
        (() => {
            const updateMissionDays = () => {
                const depart = document.getElementById('date_depart')?.value;
                const retour = document.getElementById('date_retour')?.value;
                const output = document.getElementById('nombre_jours');
                if (!depart || !retour || !output) return;
                const start = new Date(`${depart}T00:00:00`);
                const end = new Date(`${retour}T00:00:00`);
                if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) return;
                output.value = String(Math.floor((end - start) / 86400000) + 1);
            };
            const initRows = (collectionName, rowsId, templateId) => {
                const container = document.getElementById(rowsId);
                const template = document.getElementById(templateId);
                if (!container || !template) return;
                const rows = () => [...container.querySelectorAll('[data-row]')];
                const focusFirstInput = (row) => row?.querySelector('input, select')?.focus();

                const renumber = () => {
                    const allRows = rows();
                    allRows.forEach((row, index) => {
                        row.querySelectorAll('[data-name]').forEach((input) => {
                            input.name = `${collectionName}[${index}][${input.dataset.name}]`;
                        });
                    });
                    container.querySelectorAll('[data-remove-row]').forEach((button) => {
                        const seule = allRows.length <= 1;
                        button.disabled = seule;
                        button.classList.toggle('opacity-40', seule);
                        button.classList.toggle('cursor-not-allowed', seule);
                        button.title = seule ? 'Au moins une ligne est requise' : 'Retirer cette ligne';
                    });
                    const compteur = document.querySelector(`[data-row-count="${collectionName}"]`);
                    if (compteur) compteur.textContent = `(${allRows.length})`;
                };

                const addRow = () => {
                    container.appendChild(template.content.cloneNode(true));
                    renumber();
                    focusFirstInput(rows()[rows().length - 1]);
                };

                document.querySelectorAll(`[data-add-row="${collectionName}"]`).forEach((button) => {
                    if (button.dataset.bound === '1') return;
                    button.dataset.bound = '1';
                    button.addEventListener('click', addRow);
                });
                if (container.dataset.bound !== '1') {
                    container.dataset.bound = '1';
                    container.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-remove-row]');
                        if (!button || button.disabled) return;
                        button.closest('[data-row]')?.remove();
                        renumber();
                    });
                    // Entrée dans un champ ajoute une ligne (ou passe au champ suivant) au lieu de soumettre le formulaire.
                    container.addEventListener('keydown', (event) => {
                        if (event.key !== 'Enter' || event.target.tagName !== 'INPUT') return;
                        event.preventDefault();
                        const currentRow = event.target.closest('[data-row]');
                        const allRows = rows();
                        const currentIndex = allRows.indexOf(currentRow);
                        if (currentIndex === allRows.length - 1) {
                            addRow();
                        } else {
                            focusFirstInput(allRows[currentIndex + 1]);
                        }
                    });
                    container.addEventListener('blur', (event) => {
                        if (event.target.tagName === 'INPUT' && event.target.type === 'text') event.target.value = event.target.value.trim();
                    }, true);
                }
                renumber();
            };
            const boot = () => {
                initRows('participants', 'participants-rows', 'participant-row-template');
                initRows('signataires', 'signataires-rows', 'signataire-row-template');
                updateMissionDays();
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
