<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartementRequest extends FormRequest
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
            'code' => 'required|string|max:20|unique:departements,code',
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
