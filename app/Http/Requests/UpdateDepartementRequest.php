<?php

namespace App\Http\Requests;

use App\Models\Departement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartementRequest extends FormRequest
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
        /** @var Departement|null $departement */
        $departement = $this->route('departement');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departements', 'code')->ignore($departement?->id),
            ],
            'nom' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'responsable_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
            'ordre' => 'nullable|integer|min:1',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
