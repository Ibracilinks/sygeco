<?php

namespace App\Http\Requests\Concerns;

use App\Models\Departement;
use Illuminate\Validation\Validator;

trait ValidatesDepartementHierarchie
{
    /**
     * Règles communes pour le type et le rattachement hiérarchique.
     *
     * @return array<string, mixed>
     */
    protected function reglesHierarchie(): array
    {
        return [
            'type' => ['required', 'in:' . implode(',', Departement::TYPES)],
            'parent_id' => ['nullable', 'exists:departements,id'],
        ];
    }

    /**
     * Vérifie la cohérence niveau ↔ parent :
     * - une direction n'a pas de parent ;
     * - un département se rattache à une direction ;
     * - un service se rattache à un département.
     */
    protected function validerCoherenceHierarchie(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');
            $parentId = $this->input('parent_id');

            if ($type === Departement::TYPE_DIRECTION) {
                if (! empty($parentId)) {
                    $validator->errors()->add('parent_id', 'Une direction est au sommet et ne peut pas avoir de parent.');
                }

                return;
            }

            if (empty($parentId)) {
                $validator->errors()->add('parent_id', 'Une entité de ce niveau doit être rattachée à une entité supérieure.');

                return;
            }

            $parent = Departement::find($parentId);

            $typeParentAttendu = $type === Departement::TYPE_SERVICE
                ? Departement::TYPE_DEPARTEMENT
                : Departement::TYPE_DIRECTION;

            if ($parent && $parent->type !== $typeParentAttendu) {
                $libelles = [
                    Departement::TYPE_DIRECTION => 'une direction',
                    Departement::TYPE_DEPARTEMENT => 'un département',
                ];

                $validator->errors()->add('parent_id', 'Le parent doit être ' . $libelles[$typeParentAttendu] . '.');
            }
        });
    }
}
