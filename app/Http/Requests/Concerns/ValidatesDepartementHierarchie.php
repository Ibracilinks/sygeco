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
     * - une direction centrale se rattache à une direction ;
     * - un service se rattache à une direction centrale, ou directement à une
     *   direction générale (service rattaché).
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

            if (! $parent) {
                return;
            }

            // Types de parent autorisés selon le niveau de l'entité.
            $typesParentAutorises = $type === Departement::TYPE_SERVICE
                ? [Departement::TYPE_DEPARTEMENT, Departement::TYPE_DIRECTION] // service ou service rattaché
                : [Departement::TYPE_DIRECTION];

            if (! in_array($parent->type, $typesParentAutorises, true)) {
                $message = $type === Departement::TYPE_SERVICE
                    ? 'Un service doit être rattaché à une direction centrale, ou directement à une direction (service rattaché).'
                    : 'Une direction centrale doit être rattachée à une direction.';

                $validator->errors()->add('parent_id', $message);
            }
        });
    }
}
