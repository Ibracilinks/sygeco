<?php

namespace App\Http\Requests;

use App\Models\Mission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Mission|null $mission */
        $mission = $this->route('mission');

        return [
            'reference' => [
                'required',
                'string',
                'max:80',
                Rule::unique('missions', 'reference')->ignore($mission?->id),
            ],
            'type' => ['required', Rule::in(array_keys(Mission::TYPES))],
            'departement_id' => 'nullable|exists:departements,id',
            'structures_demandeuses' => 'nullable|array',
            'structures_demandeuses.*' => 'integer|exists:departements,id',
            'objet' => 'required|string|max:5000',
            'code_budgetaire' => 'nullable|string|max:60',
            'point_depart' => 'nullable|string|max:150',
            'destination' => 'nullable|string|max:150',
            'destinations' => 'nullable|array',
            'destinations.*' => 'string|max:150',
            'zone_code' => 'nullable|string|max:40',
            // La date du document n'est plus saisie : elle est posée à la création.
            'date_document' => 'nullable|date',
            'date_depart' => 'required|date',
            'date_retour' => 'required|date|after_or_equal:date_depart',
            'nombre_jours' => 'nullable|integer|min:1|max:365',
            'tickets_carburant_par_jour' => 'nullable|integer|min:0|max:100',
            'nombre_vehicules' => 'nullable|integer|min:0|max:100',
            'distance_totale_km' => 'nullable|numeric|min:0|max:99999.99',
            'consommation_aux_cent' => 'nullable|numeric|min:0|max:999.99',
            'litres_par_jour_ville' => 'nullable|numeric|min:0|max:999.99',
            'prix_litre_carburant' => 'nullable|numeric|min:0|max:99999.99',
            'location_vehicule_jours' => 'nullable|integer|min:0|max:365',
            'location_vehicule_tarif' => 'nullable|numeric|min:0|max:9999999999999.99',
            'montant_peages' => 'nullable|numeric|min:0|max:9999999999999.99',
            'montant_par_jour' => 'nullable|numeric|min:0|max:9999999999999.99',
            'montant_ticket_carburant' => 'nullable|numeric|min:0|max:9999999999999.99',
            'frais_participation_nombre' => 'nullable|integer|min:0|max:1000',
            'frais_participation_unitaire' => 'nullable|numeric|min:0|max:9999999999999.99',
            'frais_visa_nombre' => 'nullable|integer|min:0|max:1000',
            'frais_visa_unitaire' => 'nullable|numeric|min:0|max:9999999999999.99',
            'billets_affaire_nombre' => 'nullable|integer|min:0|max:1000',
            'billets_affaire_unitaire' => 'nullable|numeric|min:0|max:9999999999999.99',
            'billets_economique_nombre' => 'nullable|integer|min:0|max:1000',
            'billets_economique_unitaire' => 'nullable|numeric|min:0|max:9999999999999.99',
            'lieu_signature' => 'nullable|string|max:100',
            'statut' => ['required', Rule::in(array_keys(Mission::STATUTS))],
            'participants' => 'required|array|min:1',
            'participants.*.nom_complet' => 'required|string|max:255',
            'participants.*.categorie' => 'nullable|string|max:40',
            'participants.*.nombre_nuitees' => 'nullable|integer|min:0|max:365',
            'etapes' => 'nullable|array',
            'etapes.*.type_etape' => 'nullable|string|max:40',
            'etapes.*.bareme' => 'nullable|string|max:40',
            'etapes.*.localite' => 'nullable|string|max:150',
            'etapes.*.date_depart' => 'nullable|date',
            'etapes.*.date_retour' => 'nullable|date',
            'etapes.*.nombre_jours' => 'nullable|integer|min:1|max:365',
            'etapes.*.nombre_nuitees' => 'nullable|integer|min:0|max:365',
            'etapes.*.premiere_nuitee_payee' => 'nullable|boolean',
            'signataires' => 'required|array|min:1',
            'signataires.*.libelle' => 'nullable|string|max:150',
            'signataires.*.nom' => 'required|string|max:255',
            'signataires.*.fonction' => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        $participants = collect($this->input('participants', []))
            ->map(fn ($participant) => [
                'nom_complet' => trim((string) ($participant['nom_complet'] ?? '')),
                'categorie' => trim((string) ($participant['categorie'] ?? '')) ?: null,
                'nombre_nuitees' => is_numeric($participant['nombre_nuitees'] ?? null) ? (int) $participant['nombre_nuitees'] : null,
            ])
            ->filter(fn ($participant) => $participant['nom_complet'] !== '')
            ->values()
            ->all();

        // Chaque étape se saisit en nombre de jours ; ses dates sont déduites plus bas.
        $etapes = collect($this->input('etapes', []))
            ->map(function ($etape) {
                $premiereNuiteePayee = filter_var($etape['premiere_nuitee_payee'] ?? false, FILTER_VALIDATE_BOOL);
                $jours = is_numeric($etape['nombre_jours'] ?? null) ? max(1, (int) $etape['nombre_jours']) : 1;

                return [
                    'type_etape' => trim((string) ($etape['type_etape'] ?? '')) ?: null,
                    'bareme' => trim((string) ($etape['bareme'] ?? '')) ?: 'national',
                    'localite' => trim((string) ($etape['localite'] ?? '')),
                    'nombre_jours' => $jours,
                    'nombre_nuitees' => Mission::nuiteesDepuisJours($jours, $premiereNuiteePayee),
                    'premiere_nuitee_payee' => $premiereNuiteePayee,
                ];
            })
            ->filter(fn ($etape) => $etape['localite'] !== '')
            ->values()
            ->all();

        $signataires = collect($this->input('signataires', []))
            ->map(fn ($signataire) => [
                'libelle' => trim((string) ($signataire['libelle'] ?? '')) ?: null,
                'nom' => trim((string) ($signataire['nom'] ?? '')),
                'fonction' => trim((string) ($signataire['fonction'] ?? '')),
            ])
            ->filter(fn ($signataire) => $signataire['nom'] !== '' || $signataire['fonction'] !== '' || $signataire['libelle'] !== null)
            ->values()
            ->all();

        // Services demandeurs : plusieurs structures, la première faisant office
        // de structure principale sur les documents officiels.
        $structures = collect($this->input('structures_demandeuses', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $dateDepart = $this->input('date_depart', now()->toDateString());
        $dateRetour = $this->input('date_retour', $dateDepart);

        // Les étapes s'enchaînent à partir du départ de la mission.
        $etapes = Mission::datesEtapesSequentielles($etapes, $dateDepart);

        // Régions de destination : plusieurs par mission, réunies dans `destination`.
        $destinations = collect($this->input('destinations', []))
            ->map(fn ($region) => trim((string) $region))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Les postes budgétaires laissés vides valent zéro : leurs colonnes sont NOT NULL.
        $this->merge(Mission::normaliserMontantsFacultatifs($this->all()));

        $this->merge([
            'type' => $this->input('type', Mission::TYPE_MEME_VILLE),
            // À la mise à jour, l'absence de statut transmis conserve celui de la mission.
            'statut' => $this->input('statut') ?: ($this->route('mission')?->statut ?? 'brouillon'),
            'lieu_signature' => trim((string) $this->input('lieu_signature', '')) ?: 'Bamako',
            'structures_demandeuses' => $structures,
            'destinations' => $destinations,
            'destination' => $destinations !== [] ? implode(', ', $destinations) : $this->input('destination'),
            'departement_id' => $structures[0] ?? $this->input('departement_id'),
            'date_document' => $this->input('date_document') ?: ($this->route('mission')?->date_document?->toDateString() ?? now()->toDateString()),
            'nombre_jours' => Mission::resoudreNombreJours(
                $this->input('type', Mission::TYPE_MEME_VILLE),
                $this->input('nombre_jours'),
                $dateDepart,
                $dateRetour
            ),
            'tickets_carburant_par_jour' => is_numeric($this->input('tickets_carburant_par_jour')) ? (int) $this->input('tickets_carburant_par_jour') : 0,
            'frais_participation_nombre' => is_numeric($this->input('frais_participation_nombre')) ? (int) $this->input('frais_participation_nombre') : 0,
            'frais_visa_nombre' => is_numeric($this->input('frais_visa_nombre')) ? (int) $this->input('frais_visa_nombre') : 0,
            'billets_affaire_nombre' => is_numeric($this->input('billets_affaire_nombre')) ? (int) $this->input('billets_affaire_nombre') : 0,
            'billets_economique_nombre' => is_numeric($this->input('billets_economique_nombre')) ? (int) $this->input('billets_economique_nombre') : 0,
            'participants' => $participants,
            'etapes' => $etapes,
            'signataires' => $signataires,
        ]);
    }

    /**
     * Libellés métier des champs, repris dans les messages d'erreur.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'reference' => 'référence de l\'ordre',
            'departement_id' => 'structure demandeuse',
            'structures_demandeuses' => 'services demandeurs',
            'objet' => 'objet de la mission',
            'code_budgetaire' => 'code budgétaire',
            'point_depart' => 'point de départ',
            'destination' => 'destination',
            'destinations' => 'régions de destination',
            'zone_code' => 'zone de majoration',
            'date_document' => 'date du document',
            'date_depart' => 'date de départ',
            'date_retour' => 'date de retour',
            'nombre_jours' => 'nombre de jours',
            'tickets_carburant_par_jour' => 'tickets carburant par jour',
            'lieu_signature' => 'lieu de signature',
            'statut' => 'statut',
            'participants' => 'participants',
            'participants.*.nom_complet' => 'nom du participant',
            'participants.*.categorie' => 'catégorie du participant',
            'participants.*.nombre_nuitees' => 'nombre de nuitées du participant',
            'etapes' => 'étapes',
            'etapes.*.localite' => 'localité de l\'étape',
            'etapes.*.nombre_jours' => 'nombre de jours de l\'étape',
            'etapes.*.date_depart' => 'date de départ de l\'étape',
            'etapes.*.date_retour' => 'date de retour de l\'étape',
            'signataires' => 'signataires',
            'signataires.*.nom' => 'nom du signataire',
            'signataires.*.fonction' => 'fonction du signataire',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type', Mission::TYPE_MEME_VILLE);

            if ($type === Mission::TYPE_EXTERIEURE) {
                if (! array_key_exists((string) $this->input('zone_code'), Mission::zonesExterieures())) {
                    $validator->errors()->add('zone_code', 'La zone de majoration est requise pour une mission extérieure.');
                }

                foreach ((array) $this->input('participants', []) as $index => $participant) {
                    if (! array_key_exists((string) ($participant['categorie'] ?? ''), Mission::categoriesExterieures())) {
                        $validator->errors()->add("participants.$index.categorie", 'La catégorie du participant est requise pour une mission extérieure.');
                    }
                }
            }

            if ($type === Mission::TYPE_REGION) {
                // Les étapes découpent la mission : enchaînées depuis son départ, elles
                // ne peuvent pas totaliser plus de jours qu'elle n'en compte.
                $joursMission = (int) $this->input('nombre_jours');
                $totalJoursEtapes = collect($this->input('etapes', []))
                    ->sum(fn ($etape) => (int) ($etape['nombre_jours'] ?? 0));

                if ($joursMission > 0 && $totalJoursEtapes > $joursMission) {
                    $validator->errors()->add(
                        'etapes',
                        "Le total des jours d'étapes ($totalJoursEtapes) dépasse la durée de la mission ($joursMission jours)."
                    );
                }
            }

            if ($type === Mission::TYPE_REGION) {
                if (blank($this->input('destination'))) {
                    $validator->errors()->add('destinations', 'Au moins une région de destination est requise pour une mission région.');
                }

                if (! is_array($this->input('etapes')) || count((array) $this->input('etapes')) === 0) {
                    $validator->errors()->add('etapes', 'Au moins une étape est requise pour une mission région.');
                }

                foreach ((array) $this->input('participants', []) as $index => $participant) {
                    if (! array_key_exists((string) ($participant['categorie'] ?? ''), Mission::categoriesNationales())) {
                        $validator->errors()->add("participants.$index.categorie", 'La catégorie du participant est requise pour une mission région.');
                    }
                }

                foreach ((array) $this->input('etapes', []) as $index => $etape) {
                    if (! array_key_exists((string) ($etape['type_etape'] ?? ''), Mission::TYPES_ETAPES_REGIONALES)) {
                        $validator->errors()->add("etapes.$index.type_etape", "Le type d'étape est requis.");
                    }

                    if (! array_key_exists((string) ($etape['bareme'] ?? ''), Mission::BAREMES_REGIONAUX)) {
                        $validator->errors()->add("etapes.$index.bareme", 'Le barème de cette étape est invalide.');
                    }
                }
            }
        });
    }
}
