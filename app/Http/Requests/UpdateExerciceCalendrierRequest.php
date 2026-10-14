<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Réglage d'une fenêtre du calendrier (saisie, mi-parcours, évaluation) depuis la fiche
 * de l'exercice. Chaque modale n'envoie que sa paire de dates : les champs absents
 * ne sont ni validés ni modifiés.
 */
class UpdateExerciceCalendrierRequest extends FormRequest
{
    protected $errorBag = 'calendrier';

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('exercice')) ?? false;
    }

    public function rules(): array
    {
        $exercice = $this->route('exercice');
        $debut = $exercice->date_debut->format('Y-m-d');
        $fin = $exercice->date_fin->format('Y-m-d');

        return [
            'fenetre' => ['required', 'in:saisie,mi_parcours,evaluation'],
            // La saisie des activités peut s'ouvrir avant le début de l'exercice et se clore après sa fin.
            'date_ouverture_saisie' => ['sometimes', 'nullable', 'date'],
            'date_limite_saisie' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_ouverture_saisie'],
            'date_debut_mi_parcours' => ['sometimes', 'nullable', 'date', "after_or_equal:$debut", "before_or_equal:$fin"],
            'date_fin_mi_parcours' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_debut_mi_parcours', "before_or_equal:$fin"],
            'date_debut_evaluation' => ['sometimes', 'nullable', 'date', "after_or_equal:$debut", "before_or_equal:$fin"],
            'date_fin_evaluation' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_debut_evaluation', "before_or_equal:$fin"],
        ];
    }

    public function attributes(): array
    {
        return [
            'date_ouverture_saisie' => "date d'ouverture de la saisie",
            'date_limite_saisie' => 'date limite de saisie',
            'date_debut_mi_parcours' => 'début du mi-parcours',
            'date_fin_mi_parcours' => 'fin du mi-parcours',
            'date_debut_evaluation' => "début de l'évaluation",
            'date_fin_evaluation' => "fin de l'évaluation",
        ];
    }
}
