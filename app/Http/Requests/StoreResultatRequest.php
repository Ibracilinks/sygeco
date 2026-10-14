<?php

namespace App\Http\Requests;

use App\Support\ActiveExercice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResultatRequest extends FormRequest
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
        // Le résultat est créé dans l'exercice actif : son objectif doit le couvrir,
        // et son code n'est unique qu'au sein de cet exercice.
        $exerciceId = ActiveExercice::id();

        return [
            'objectif_id' => [
                'required',
                Rule::exists('exercice_objectif', 'objectif_id')->where('exercice_id', $exerciceId),
            ],
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('resultats', 'code')->where('exercice_id', $exerciceId)->withoutTrashed(),
            ],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
