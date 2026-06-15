<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'departement_id' => 'nullable|exists:departements,id',
            'poste' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|exists:roles,id',
        ];
    }
}
