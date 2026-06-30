<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDepartementHierarchie;
use App\Models\Departement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDepartementRequest extends FormRequest
{
    use ValidatesDepartementHierarchie;

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
            'parent_id' => [
                'nullable',
                'exists:departements,id',
                // Une entité ne peut pas être son propre parent.
                Rule::notIn([$departement?->id]),
            ],
        ] + $this->reglesHierarchie();
    }

    public function withValidator(Validator $validator): void
    {
        $this->validerCoherenceHierarchie($validator);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'parent_id' => $this->filled('parent_id') ? $this->input('parent_id') : null,
        ]);
    }
}
