<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExerciceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_exercices') ?? false;
    }

    public function rules(): array
    {
        return [
            'annee' => ['required', 'integer', 'min:2000', 'max:2100', 'unique:exercices,annee'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'statut' => ['required', Rule::in(['brouillon', 'actif', 'cloture'])],
        ];
    }
}
