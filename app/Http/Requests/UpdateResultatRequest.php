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
            // Le résultat reste dans son exercice : l'objectif choisi doit le couvrir,
            // et le code n'est unique qu'au sein de cet exercice.
            'objectif_id' => [
                'required',
                $resultat?->exercice_id
                    ? Rule::exists('exercice_objectif', 'objectif_id')->where('exercice_id', $resultat->exercice_id)
                    : Rule::exists('objectifs', 'id'),
            ],
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('resultats', 'code')->where('exercice_id', $resultat?->exercice_id)->ignore($resultat?->id)->withoutTrashed(),
            ],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
