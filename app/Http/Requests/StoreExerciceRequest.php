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
            'date_ouverture_saisie' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'date_limite_saisie' => ['nullable', 'date', 'after_or_equal:date_ouverture_saisie', 'before_or_equal:date_fin'],
            'statut' => ['required', Rule::in(['brouillon', 'actif', 'cloture'])],
        ];
    }
}
