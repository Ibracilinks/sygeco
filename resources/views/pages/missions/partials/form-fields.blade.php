@php
    $participantValues = old('participants', $participants ?? [['nom_complet' => '', 'categorie' => null, 'nombre_nuitees' => null]]);
    $signataireValues = old('signataires', $signataires ?? [['libelle' => '', 'nom' => '', 'fonction' => '']]);
    $currentType = old('type', $mission->type ?? \App\Models\Mission::TYPE_MEME_VILLE);
@endphp

@if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Merci de corriger les erreurs ci-dessous.</p>
    </div>
@endif

<div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
    <div>
        <label for="type" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Type de mission</label>
        <select id="type" name="type"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            @foreach (\App\Models\Mission::TYPES as $code => $label)
                <option value="{{ $code }}" @selected($currentType === $code)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="reference" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Référence de l'ordre</label>
        <input type="text" id="reference" name="reference" value="{{ old('reference', $mission->reference) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('reference')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="departement_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Structure demandeuse</label>
        <select id="departement_id" name="departement_id"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Sans structure</option>
            @foreach ($departements as $departement)
                <option value="{{ $departement->id }}" @selected((string) old('departement_id', $mission->departement_id) === (string) $departement->id)>{{ $departement->nom }}</option>
            @endforeach
        </select>
        @error('departement_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="statut" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Statut</label>
        <select id="statut" name="statut"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            @foreach (\App\Models\Mission::STATUTS as $code => $label)
                <option value="{{ $code }}" @selected(old('statut', $mission->statut) === $code)>{{ $label }}</option>
            @endforeach
        </select>
        @error('statut')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label for="objet" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Objet de la mission</label>
    <textarea id="objet" name="objet" rows="4"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">{{ old('objet', $mission->objet) }}</textarea>
    @error('objet')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
    <div>
        <label for="date_document" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date du document</label>
        <input type="date" id="date_document" name="date_document" value="{{ old('date_document', optional($mission->date_document)->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('date_document')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="date_depart" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de départ</label>
        <input type="date" id="date_depart" name="date_depart" value="{{ old('date_depart', optional($mission->date_depart)->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('date_depart')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="date_retour" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de retour</label>
        <input type="date" id="date_retour" name="date_retour" value="{{ old('date_retour', optional($mission->date_retour)->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('date_retour')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="nombre_jours" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre de jours</label>
        <input type="number" min="1" id="nombre_jours" name="nombre_jours" value="{{ old('nombre_jours', $mission->nombre_jours) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('nombre_jours')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="lieu_signature" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Lieu de signature</label>
        <input type="text" id="lieu_signature" name="lieu_signature" value="{{ old('lieu_signature', $mission->lieu_signature) }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        @error('lieu_signature')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div data-type-section="exterieure" class="{{ $currentType === \App\Models\Mission::TYPE_EXTERIEURE ? '' : 'hidden' }}">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div>
            <label for="destination" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Destination</label>
            <input type="text" id="destination" name="destination" value="{{ old('destination', $mission->destination) }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            @error('destination')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="zone_code" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Zone de majoration</label>
            <select id="zone_code" name="zone_code"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Choisir une zone</option>
                @foreach (\App\Models\Mission::ZONES_EXTERIEURES as $code => $zone)
                    <option value="{{ $code }}" @selected(old('zone_code', $mission->zone_code) === $code)>{{ $zone['label'] }} ({{ $zone['taux'] }}%)</option>
                @endforeach
            </select>
            @error('zone_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <h2 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Autres frais</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="frais_participation_nombre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nb personnes participation</label>
                    <input type="number" min="0" id="frais_participation_nombre" name="frais_participation_nombre" value="{{ old('frais_participation_nombre', $mission->frais_participation_nombre) }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
                <div>
                    <label for="frais_participation_unitaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Montant unitaire participation</label>
                    <input type="number" step="0.01" min="0" id="frais_participation_unitaire" name="frais_participation_unitaire" value="{{ old('frais_participation_unitaire', $mission->frais_participation_unitaire) }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
                <div>
                    <label for="frais_visa_nombre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nb personnes visa</label>
                    <input type="number" min="0" id="frais_visa_nombre" name="frais_visa_nombre" value="{{ old('frais_visa_nombre', $mission->frais_visa_nombre) }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
                <div>
                    <label for="frais_visa_unitaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Montant unitaire visa</label>
                    <input type="number" step="0.01" min="0" id="frais_visa_unitaire" name="frais_visa_unitaire" value="{{ old('frais_visa_unitaire', $mission->frais_visa_unitaire) }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <h2 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Billets d'avion</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="billets_affaire_nombre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nb billets affaire</label>
                    <input type="number" min="0" id="billets_affaire_nombre" name="billets_affaire_nombre" value="{{ old('billets_affaire_nombre', $mission->billets_affaire_nombre) }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
                <div>
                    <label for="billets_affaire_unitaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Prix unitaire affaire</label>
                    <input type="number" step="0.01" min="0" id="billets_affaire_unitaire" name="billets_affaire_unitaire" value="{{ old('billets_affaire_unitaire', $mission->billets_affaire_unitaire) }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
                <div>
                    <label for="billets_economique_nombre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nb billets économique</label>
                    <input type="number" min="0" id="billets_economique_nombre" name="billets_economique_nombre" value="{{ old('billets_economique_nombre', $mission->billets_economique_nombre) }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
                <div>
                    <label for="billets_economique_unitaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Prix unitaire économique</label>
                    <input type="number" step="0.01" min="0" id="billets_economique_unitaire" name="billets_economique_unitaire" value="{{ old('billets_economique_unitaire', $mission->billets_economique_unitaire) }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
            </div>
        </div>
    </div>
</div>

<div data-type-section="meme_ville" class="{{ $currentType === \App\Models\Mission::TYPE_MEME_VILLE ? '' : 'hidden' }}">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <div>
            <label for="montant_par_jour" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Montant par jour</label>
            <input type="number" step="0.01" min="0" id="montant_par_jour" name="montant_par_jour" value="{{ old('montant_par_jour', $mission->montant_par_jour) }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        </div>
        <div>
            <label for="tickets_carburant_par_jour" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tickets carburant / jour</label>
            <input type="number" min="0" id="tickets_carburant_par_jour" name="tickets_carburant_par_jour" value="{{ old('tickets_carburant_par_jour', $mission->tickets_carburant_par_jour) }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        </div>
        <div>
            <label for="montant_ticket_carburant" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Montant d'un ticket carburant</label>
            <input type="number" step="0.01" min="0" id="montant_ticket_carburant" name="montant_ticket_carburant" value="{{ old('montant_ticket_carburant', $mission->montant_ticket_carburant) }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Rappel</p>
            <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">Le nombre de personnes est déduit automatiquement du tableau des participants.</p>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Participants</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pour l'extérieur, renseigne aussi la catégorie et les nuitées.</p>
            </div>
            <button type="button" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-100"
                data-add-row="participants">
                Ajouter
            </button>
        </div>
        <div id="participants-rows" class="space-y-3">
            @foreach ($participantValues as $index => $participant)
                <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
                    <div class="md:col-span-6">
                        <input type="text" name="participants[{{ $index }}][nom_complet]" value="{{ $participant['nom_complet'] ?? '' }}"
                            placeholder="Prénoms et noms"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    </div>
                    <div class="md:col-span-3" data-type-section="exterieure-inline">
                        <select name="participants[{{ $index }}][categorie]"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                            <option value="">Catégorie</option>
                            @foreach (\App\Models\Mission::CATEGORIES_EXTERIEURES as $code => $categorie)
                                <option value="{{ $code }}" @selected(($participant['categorie'] ?? null) === $code)>{{ $categorie['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2" data-type-section="exterieure-inline">
                        <input type="number" min="0" name="participants[{{ $index }}][nombre_nuitees]" value="{{ $participant['nombre_nuitees'] ?? '' }}"
                            placeholder="Nuitées"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    </div>
                    <div class="md:col-span-1">
                        <button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>
                            X
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        @error('participants')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        @error('participants.*.nom_complet')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        @error('participants.*.categorie')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Signataires</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Préremplis depuis le dernier document du même type quand il existe.</p>
            </div>
            <button type="button" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-100"
                data-add-row="signataires">
                Ajouter
            </button>
        </div>
        <div id="signataires-rows" class="space-y-3">
            @foreach ($signataireValues as $index => $signataire)
                <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
                    <div class="md:col-span-4">
                        <input type="text" name="signataires[{{ $index }}][libelle]" value="{{ $signataire['libelle'] ?? '' }}"
                            placeholder="Libellé"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    </div>
                    <div class="md:col-span-3">
                        <input type="text" name="signataires[{{ $index }}][nom]" value="{{ $signataire['nom'] ?? '' }}"
                            placeholder="Nom"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    </div>
                    <div class="md:col-span-4">
                        <input type="text" name="signataires[{{ $index }}][fonction]" value="{{ $signataire['fonction'] ?? '' }}"
                            placeholder="Fonction"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    </div>
                    <div class="md:col-span-1">
                        <button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>
                            X
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<template id="participant-row-template">
    <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
        <div class="md:col-span-6">
            <input type="text" data-name="nom_complet" placeholder="Prénoms et noms"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        </div>
        <div class="md:col-span-3" data-type-section="exterieure-inline">
            <select data-name="categorie"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                <option value="">Catégorie</option>
                @foreach (\App\Models\Mission::CATEGORIES_EXTERIEURES as $code => $categorie)
                    <option value="{{ $code }}">{{ $categorie['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2" data-type-section="exterieure-inline">
            <input type="number" min="0" data-name="nombre_nuitees" placeholder="Nuitées"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        </div>
        <div class="md:col-span-1">
            <button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>
                X
            </button>
        </div>
    </div>
</template>

<template id="signataire-row-template">
    <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
        <div class="md:col-span-4">
            <input type="text" data-name="libelle" placeholder="Libellé"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        </div>
        <div class="md:col-span-3">
            <input type="text" data-name="nom" placeholder="Nom"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        </div>
        <div class="md:col-span-4">
            <input type="text" data-name="fonction" placeholder="Fonction"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        </div>
        <div class="md:col-span-1">
            <button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>
                X
            </button>
        </div>
    </div>
</template>

@push('scripts')
    <script>
        (function () {
            const typeField = () => document.getElementById('type');

            const updateTypeSections = () => {
                const value = typeField()?.value || '{{ \App\Models\Mission::TYPE_MEME_VILLE }}';
                document.querySelectorAll('[data-type-section="meme_ville"]').forEach((el) => el.classList.toggle('hidden', value !== 'meme_ville'));
                document.querySelectorAll('[data-type-section="exterieure"]').forEach((el) => el.classList.toggle('hidden', value !== 'exterieure'));
                document.querySelectorAll('[data-type-section="exterieure-inline"]').forEach((el) => el.classList.toggle('hidden', value !== 'exterieure'));
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

                        row.querySelectorAll('input[name], select[name]').forEach((input) => {
                            input.name = input.name.replace(new RegExp(`^${collectionName}\\[\\d+\\]`), `${collectionName}[${index}]`);
                        });
                    });
                    updateTypeSections();
                };

                document.querySelectorAll(`[data-add-row="${collectionName}"]`).forEach((button) => {
                    if (button.dataset.bound === '1') return;
                    button.dataset.bound = '1';
                    button.addEventListener('click', () => {
                        const clone = template.content.cloneNode(true);
                        container.appendChild(clone);
                        renumber();
                    });
                });

                if (container.dataset.bound === '1') return;
                container.dataset.bound = '1';
                container.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-remove-row]');
                    if (!button) return;

                    const rows = container.querySelectorAll('[data-row]');
                    if (rows.length <= 1) return;

                    button.closest('[data-row]')?.remove();
                    renumber();
                });

                renumber();
            };

            const boot = () => {
                initRows('participants', 'participants-rows', 'participant-row-template');
                initRows('signataires', 'signataires-rows', 'signataire-row-template');
                updateTypeSections();
                const field = typeField();
                if (field && field.dataset.bound !== '1') {
                    field.dataset.bound = '1';
                    field.addEventListener('change', updateTypeSections);
                }
            };

            document.addEventListener('DOMContentLoaded', boot);
            document.addEventListener('livewire:navigated', boot);
        })();
    </script>
@endpush
