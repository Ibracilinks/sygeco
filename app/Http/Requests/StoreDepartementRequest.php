<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDepartementHierarchie;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDepartementRequest extends FormRequest
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
        return [
            'code' => 'required|string|max:20|unique:departements,code',
            'nom' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'responsable_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
            'ordre' => 'nullable|integer|min:1',
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
