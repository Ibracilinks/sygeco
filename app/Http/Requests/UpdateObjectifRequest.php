<?php

namespace App\Http\Requests;

use App\Models\Objectif;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateObjectifRequest extends FormRequest
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
        /** @var Objectif|null $objectif */
        $objectif = $this->route('objectif');

        return [
            'exercice_id' => 'required|exists:exercices,id',
            'code' => ['required', 'string', 'max:20', Rule::unique('objectifs', 'code')->ignore($objectif?->id)],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'ordre' => 'nullable|integer|min:0',
        ];
    }
}
