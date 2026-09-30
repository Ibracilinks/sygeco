<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreObjectifRequest extends FormRequest
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
        return [
            // Un objectif couvre un ou plusieurs exercices (plan stratégique pluriannuel).
            'exercice_ids' => ['required', 'array', 'min:1'],
            'exercice_ids.*' => ['integer', 'exists:exercices,id'],
            'code' => 'required|string|max:20|unique:objectifs,code',
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'ordre' => 'nullable|integer|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'exercice_ids.required' => 'Sélectionnez au moins un exercice couvert par cet objectif.',
            'exercice_ids.min' => 'Sélectionnez au moins un exercice couvert par cet objectif.',
        ];
    }
}
