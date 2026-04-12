<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExerciceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_exercices') ?? false;
    }

    public function rules(): array
    {
        $exercice = $this->route('exercice');

        return [
            'annee' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('exercices', 'annee')->ignore($exercice->id),
            ],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'statut' => ['required', Rule::in(['brouillon', 'actif', 'cloture'])],
        ];
    }
}
