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
            'objet' => 'required|string|max:5000',
            'destination' => 'nullable|string|max:150',
            'zone_code' => 'nullable|string|max:40',
            'date_document' => 'required|date',
            'date_depart' => 'required|date',
            'date_retour' => 'required|date|after_or_equal:date_depart',
            'nombre_jours' => 'required|integer|min:1|max:365',
            'tickets_carburant_par_jour' => 'nullable|integer|min:0|max:100',
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
            'signataires' => 'required|array|min:1',
            'signataires.*.libelle' => 'nullable|string|max:150',
            'signataires.*.nom' => 'required|string|max:255',
            'signataires.*.fonction' => 'required|string|max:255',
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

        $signataires = collect($this->input('signataires', []))
            ->map(fn ($signataire) => [
                'libelle' => trim((string) ($signataire['libelle'] ?? '')) ?: null,
                'nom' => trim((string) ($signataire['nom'] ?? '')),
                'fonction' => trim((string) ($signataire['fonction'] ?? '')),
            ])
            ->filter(fn ($signataire) => $signataire['nom'] !== '' || $signataire['fonction'] !== '' || $signataire['libelle'] !== null)
            ->values()
            ->all();

        $this->merge([
            'type' => $this->input('type', Mission::TYPE_MEME_VILLE),
            'lieu_signature' => trim((string) $this->input('lieu_signature', '')) ?: 'Bamako',
            'tickets_carburant_par_jour' => is_numeric($this->input('tickets_carburant_par_jour')) ? (int) $this->input('tickets_carburant_par_jour') : 0,
            'frais_participation_nombre' => is_numeric($this->input('frais_participation_nombre')) ? (int) $this->input('frais_participation_nombre') : 0,
            'frais_visa_nombre' => is_numeric($this->input('frais_visa_nombre')) ? (int) $this->input('frais_visa_nombre') : 0,
            'billets_affaire_nombre' => is_numeric($this->input('billets_affaire_nombre')) ? (int) $this->input('billets_affaire_nombre') : 0,
            'billets_economique_nombre' => is_numeric($this->input('billets_economique_nombre')) ? (int) $this->input('billets_economique_nombre') : 0,
            'participants' => $participants,
            'signataires' => $signataires,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type', Mission::TYPE_MEME_VILLE);

            if ($type === Mission::TYPE_MEME_VILLE) {
                if ($this->input('montant_par_jour') === null || $this->input('montant_ticket_carburant') === null) {
                    $validator->errors()->add('montant_par_jour', 'Les barèmes même ville sont requis.');
                }
            }

            if ($type === Mission::TYPE_EXTERIEURE) {
                if (! array_key_exists((string) $this->input('zone_code'), Mission::ZONES_EXTERIEURES)) {
                    $validator->errors()->add('zone_code', 'La zone de majoration est requise pour une mission extérieure.');
                }

                foreach ((array) $this->input('participants', []) as $index => $participant) {
                    if (! array_key_exists((string) ($participant['categorie'] ?? ''), Mission::CATEGORIES_EXTERIEURES)) {
                        $validator->errors()->add("participants.$index.categorie", 'La catégorie du participant est requise pour une mission extérieure.');
                    }
                }
            }
        });
    }
}
