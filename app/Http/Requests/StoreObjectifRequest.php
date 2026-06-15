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
            'exercice_id' => 'required|exists:exercices,id',
            'code' => 'required|string|max:20|unique:objectifs,code',
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'ordre' => 'nullable|integer|min:0',
        ];
    }
}
