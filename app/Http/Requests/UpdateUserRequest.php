<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'departement_id' => 'nullable|exists:departements,id',
            'poste' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|exists:roles,id',
        ];
    }
}
