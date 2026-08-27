@php
    $participantValues = old('participants', $participants ?? [['nom_complet' => '', 'categorie' => null]]);
    $etapeValues = old('etapes', $etapes ?? [[
        'type_etape' => 'region',
        'bareme' => 'national',
        'localite' => '',
        'date_depart' => optional($mission->date_depart)->format('Y-m-d'),
        'date_retour' => optional($mission->date_retour)->format('Y-m-d'),
        'premiere_nuitee_payee' => false,
    ]]);
    $signataireValues = old('signataires', $signataires ?? [['libelle' => '', 'nom' => '', 'fonction' => '']]);
@endphp

@include('pages.missions.partials.erreurs')

<input type="hidden" name="type" value="{{ \App\Models\Mission::TYPE_REGION }}">

<div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
    <div><label for="reference" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Référence de l'ordre</label><input type="text" id="reference" name="reference" value="{{ old('reference', $mission->reference) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
    <div><label for="departement_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Structure demandeuse</label><select id="departement_id" name="departement_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"><option value="">Sans structure</option>@foreach ($departements as $departement)<option value="{{ $departement->id }}" @selected((string) old('departement_id', $mission->departement_id) === (string) $departement->id)>{{ $departement->nom }}</option>@endforeach</select></div>
    <div><label for="destination" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Région principale</label><input type="text" id="destination" name="destination" value="{{ old('destination', $mission->destination) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
    <div><label for="lieu_signature" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Lieu de signature</label><input type="text" id="lieu_signature" name="lieu_signature" value="{{ old('lieu_signature', $mission->lieu_signature) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
</div>

<div>
    <label for="objet" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Objet de la mission</label>
    <textarea id="objet" name="objet" rows="4" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">{{ old('objet', $mission->objet) }}</textarea>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
    <div><label for="date_document" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date du document</label><input type="date" id="date_document" name="date_document" value="{{ old('date_document', optional($mission->date_document)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
    <div><label for="date_depart" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de départ</label><input type="date" id="date_depart" name="date_depart" value="{{ old('date_depart', optional($mission->date_depart)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
    <div><label for="date_retour" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Date de retour</label><input type="date" id="date_retour" name="date_retour" value="{{ old('date_retour', optional($mission->date_retour)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
    <div><label for="nombre_jours" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre de jours</label><input type="number" min="1" id="nombre_jours" name="nombre_jours" value="{{ old('nombre_jours', $mission->nombre_jours) }}" readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"></div>
    <div><label for="statut" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Statut</label><select id="statut" name="statut" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@foreach (\App\Models\Mission::STATUTS as $code => $label)<option value="{{ $code }}" @selected(old('statut', $mission->statut) === $code)>{{ $label }}</option>@endforeach</select></div>
</div>

{{-- Sections II et III du budget officiel : carburant, location, billet, péages. --}}
<div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
    <h2 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Transport et péages</h2>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
    <div><label for="nombre_vehicules" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre de véhicules</label><input type="number" min="0" id="nombre_vehicules" name="nombre_vehicules" value="{{ old('nombre_vehicules', $mission->nombre_vehicules) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('nombre_vehicules')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="distance_totale_km" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Distance totale (km)</label><input type="number" step="0.01" min="0" id="distance_totale_km" name="distance_totale_km" value="{{ old('distance_totale_km', (float) $mission->distance_totale_km) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('distance_totale_km')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="consommation_aux_cent" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Consommation (L / 100 km)</label><input type="number" step="0.01" min="0" id="consommation_aux_cent" name="consommation_aux_cent" value="{{ old('consommation_aux_cent', (float) $mission->consommation_aux_cent) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('consommation_aux_cent')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="prix_litre_carburant" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Prix du litre (FCFA)</label><input type="number" step="0.01" min="0" id="prix_litre_carburant" name="prix_litre_carburant" value="{{ old('prix_litre_carburant', (float) $mission->prix_litre_carburant) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('prix_litre_carburant')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="litres_par_jour_ville" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Carburant en ville (L / jour)</label><input type="number" step="0.01" min="0" id="litres_par_jour_ville" name="litres_par_jour_ville" value="{{ old('litres_par_jour_ville', (float) $mission->litres_par_jour_ville) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('litres_par_jour_ville')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="location_vehicule_jours" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Location véhicule (jours)</label><input type="number" min="0" id="location_vehicule_jours" name="location_vehicule_jours" value="{{ old('location_vehicule_jours', $mission->location_vehicule_jours) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('location_vehicule_jours')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="location_vehicule_tarif" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Location véhicule (FCFA / jour)</label><input type="number" step="0.01" min="0" id="location_vehicule_tarif" name="location_vehicule_tarif" value="{{ old('location_vehicule_tarif', (float) $mission->location_vehicule_tarif) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('location_vehicule_tarif')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="montant_peages" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Péages (FCFA)</label><input type="number" step="0.01" min="0" id="montant_peages" name="montant_peages" value="{{ old('montant_peages', (float) $mission->montant_peages) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('montant_peages')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="billets_economique_nombre" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Billets d'avion (nombre)</label><input type="number" min="0" id="billets_economique_nombre" name="billets_economique_nombre" value="{{ old('billets_economique_nombre', $mission->billets_economique_nombre) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('billets_economique_nombre')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="billets_economique_unitaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Billet d'avion (prix unitaire)</label><input type="number" step="0.01" min="0" id="billets_economique_unitaire" name="billets_economique_unitaire" value="{{ old('billets_economique_unitaire', (float) $mission->billets_economique_unitaire) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@error('billets_economique_unitaire')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    </div>
</div>

<div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Règle métier</p>
    <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">Chaque étape a sa propre période. Les nuitées valent par défaut `jours - 1`, sauf si la première nuit est cochée.</p>
</div>

<div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
    <div class="mb-3 flex items-center justify-between gap-3"><div><h2 class="text-sm font-semibold text-slate-900 dark:text-white">Étapes de mission</h2></div><button type="button" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-100" data-add-row="etapes">Ajouter</button></div>
    <p class="mb-3 text-xs text-slate-500 dark:text-slate-400" data-etapes-total>
        Total des jours d'étapes : <span class="font-semibold" data-etapes-jours>0</span>
        sur <span class="font-semibold" data-mission-jours>{{ $mission->nombre_jours }}</span> jour(s) de mission.
    </p>

    <div id="etapes-rows" class="space-y-3">
        @foreach ($etapeValues as $index => $etape)
            <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
                <div class="md:col-span-2"><select name="etapes[{{ $index }}][type_etape]" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@foreach (\App\Models\Mission::TYPES_ETAPES_REGIONALES as $code => $label)<option value="{{ $code }}" @selected(($etape['type_etape'] ?? '') === $code)>{{ $label }}</option>@endforeach</select></div>
                <div class="md:col-span-2"><select name="etapes[{{ $index }}][bareme]" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@foreach (\App\Models\Mission::BAREMES_REGIONAUX as $code => $bareme)<option value="{{ $code }}" @selected(($etape['bareme'] ?? 'national') === $code)>{{ $bareme['label'] }}</option>@endforeach</select></div>
                <div class="md:col-span-2"><input type="text" name="etapes[{{ $index }}][localite]" value="{{ $etape['localite'] ?? '' }}" placeholder="Localité" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                <div class="md:col-span-2"><input type="date" name="etapes[{{ $index }}][date_depart]" value="{{ $etape['date_depart'] ?? '' }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                <div class="md:col-span-2"><input type="date" name="etapes[{{ $index }}][date_retour]" value="{{ $etape['date_retour'] ?? '' }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                <div class="md:col-span-1 flex items-center justify-center"><label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300"><input type="checkbox" name="etapes[{{ $index }}][premiere_nuitee_payee]" value="1" @checked(!empty($etape['premiere_nuitee_payee']))>1re nuit</label></div>
                <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>X</button></div>
            </div>
        @endforeach
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between gap-3"><h2 class="text-sm font-semibold text-slate-900 dark:text-white">Participants</h2><button type="button" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-100" data-add-row="participants">Ajouter</button></div>
        <div id="participants-rows" class="space-y-3">
            @foreach ($participantValues as $index => $participant)
                <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
                    <div class="md:col-span-8"><input type="text" name="participants[{{ $index }}][nom_complet]" value="{{ $participant['nom_complet'] ?? '' }}" placeholder="Prénoms et noms" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
                    <div class="md:col-span-3"><select name="participants[{{ $index }}][categorie]" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"><option value="">Catégorie</option>@foreach (\App\Models\Mission::categoriesNationales() as $code => $categorie)<option value="{{ $code }}" @selected(($participant['categorie'] ?? null) === $code)>{{ $categorie['label'] }}</option>@endforeach</select></div>
                    <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>X</button></div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between gap-3"><h2 class="text-sm font-semibold text-slate-900 dark:text-white">Signataires</h2><button type="button" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-100" data-add-row="signataires">Ajouter</button></div>
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
        <div class="md:col-span-8"><input type="text" data-name="nom_complet" placeholder="Prénoms et noms" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-3"><select data-name="categorie" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"><option value="">Catégorie</option>@foreach (\App\Models\Mission::categoriesNationales() as $code => $categorie)<option value="{{ $code }}">{{ $categorie['label'] }}</option>@endforeach</select></div>
        <div class="md:col-span-1"><button type="button" class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200" data-remove-row>X</button></div>
    </div>
</template>

<template id="etape-row-template">
    <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 md:grid-cols-12" data-row>
        <div class="md:col-span-2"><select data-name="type_etape" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@foreach (\App\Models\Mission::TYPES_ETAPES_REGIONALES as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach</select></div>
        <div class="md:col-span-2"><select data-name="bareme" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">@foreach (\App\Models\Mission::BAREMES_REGIONAUX as $code => $bareme)<option value="{{ $code }}">{{ $bareme['label'] }}</option>@endforeach</select></div>
        <div class="md:col-span-2"><input type="text" data-name="localite" placeholder="Localité" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-2"><input type="date" data-name="date_depart" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-2"><input type="date" data-name="date_retour" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"></div>
        <div class="md:col-span-1 flex items-center justify-center"><label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300"><input type="checkbox" data-name="premiere_nuitee_payee" value="1">1re nuit</label></div>
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
                initRows('etapes', 'etapes-rows', 'etape-row-template');
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

@push('scripts')
    <script>
        // Les étapes découpent la mission : leur total ne doit pas dépasser sa durée.
        (() => {
            const boot = () => {
                const conteneur = document.getElementById('etapes-rows');
                const affichage = document.querySelector('[data-etapes-jours]');
                const attendu = document.querySelector('[data-mission-jours]');
                const bloc = document.querySelector('[data-etapes-total]');
                if (!conteneur || !affichage || !bloc) return;

                const joursEntre = (debut, fin) => {
                    const d = new Date(`${debut}T00:00:00`);
                    const f = new Date(`${fin}T00:00:00`);
                    if (Number.isNaN(d.getTime()) || Number.isNaN(f.getTime()) || f < d) return 0;
                    return Math.floor((f - d) / 86400000) + 1;
                };

                const recalculer = () => {
                    let total = 0;
                    conteneur.querySelectorAll('[data-row]').forEach((ligne) => {
                        const debut = ligne.querySelector('input[type="date"][name*="[date_depart]"]')?.value;
                        const fin = ligne.querySelector('input[type="date"][name*="[date_retour]"]')?.value;
                        if (debut && fin) total += joursEntre(debut, fin);
                    });

                    affichage.textContent = String(total);
                    const mission = Number(document.getElementById('nombre_jours')?.value || attendu?.textContent || 0);
                    if (attendu) attendu.textContent = String(mission);

                    const depasse = mission > 0 && total > mission;
                    bloc.classList.toggle('text-red-600', depasse);
                    bloc.classList.toggle('dark:text-red-400', depasse);
                    bloc.classList.toggle('text-slate-500', !depasse);
                    bloc.classList.toggle('dark:text-slate-400', !depasse);
                };

                if (conteneur.dataset.totalBound !== '1') {
                    conteneur.dataset.totalBound = '1';
                    conteneur.addEventListener('change', recalculer);
                    conteneur.addEventListener('click', () => setTimeout(recalculer, 0));
                }

                ['date_depart', 'date_retour', 'nombre_jours'].forEach((id) => {
                    const champ = document.getElementById(id);
                    if (champ && champ.dataset.totalBound !== '1') {
                        champ.dataset.totalBound = '1';
                        champ.addEventListener('change', recalculer);
                    }
                });

                document.querySelectorAll('[data-add-row="etapes"]').forEach((bouton) => {
                    if (bouton.dataset.totalBound === '1') return;
                    bouton.dataset.totalBound = '1';
                    bouton.addEventListener('click', () => setTimeout(recalculer, 0));
                });

                recalculer();
            };

            document.addEventListener('DOMContentLoaded', boot);
            document.addEventListener('livewire:navigated', boot);
        })();
    </script>
@endpush
