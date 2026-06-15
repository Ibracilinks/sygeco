<?php

namespace App\Http\Requests;

use App\Models\Resultat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResultatRequest extends FormRequest
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
        /** @var Resultat|null $resultat */
        $resultat = $this->route('resultat');

        return [
            'objectif_id' => 'required|exists:objectifs,id',
            'code' => ['required', 'string', 'max:20', Rule::unique('resultats', 'code')->ignore($resultat?->id)],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
