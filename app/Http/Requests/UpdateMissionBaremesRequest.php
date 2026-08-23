<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMissionBaremesRequest extends FormRequest
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
            'baremes' => ['required', 'array', 'min:1'],
            'baremes.*.libelle' => ['required', 'string', 'max:150'],
            'baremes.*.description' => ['nullable', 'string', 'max:500'],
            'baremes.*.frais_mission' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'baremes.*.indemnites' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'baremes.*.taux' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'baremes.*.libelle' => 'libellé',
            'baremes.*.description' => 'description',
            'baremes.*.frais_mission' => 'frais de mission par jour',
            'baremes.*.indemnites' => 'indemnités par nuitée',
            'baremes.*.taux' => 'taux de majoration',
        ];
    }
}
