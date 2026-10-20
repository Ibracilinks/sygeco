<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

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
            'departement_id' => 'nullable|exists:departements,id',
            'poste' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'roles' => 'required|array|min:1',
            // Le contrôle de gestion n'attribue ni superadmin ni dbcgoq.
            'roles.*' => ['required', Rule::in(
                Role::query()->get(['id', 'name'])
                    ->filter(fn ($role) => $this->user()?->peutAttribuerRole($role->name))
                    ->pluck('id')->all()
            )],
        ];
    }
}
