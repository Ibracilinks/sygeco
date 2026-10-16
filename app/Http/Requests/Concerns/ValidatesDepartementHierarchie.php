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
            'type' => ['required', 'in:'.implode(',', Departement::TYPES)],
            'parent_id' => ['nullable', 'exists:departements,id'],
        ];
    }

    /**
     * Vérifie la cohérence niveau ↔ parent :
     * - la Direction Générale et l'Agence Comptable sont uniques ;
     * - la Direction Générale est au sommet, sans parent ;
     * - une direction centrale, l'agence comptable, un bureau régional et un point
     *   focal se rattachent à la Direction Générale ;
     * - un service se rattache à une direction centrale, à l'agence comptable, ou
     *   directement à la Direction Générale (service rattaché) ;
     * - une entité feuille (service, bureau régional, point focal) ne peut pas
     *   chapeauter d'autres entités.
     */
    protected function validerCoherenceHierarchie(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');
            $parentId = $this->input('parent_id');
            $courante = $this->route('departement');
            $courante = $courante instanceof Departement ? $courante : null;

            if (in_array($type, Departement::TYPES_FEUILLES, true)
                && $courante
                && $courante->enfants()->exists()) {
                $validator->errors()->add('type', 'Cette entité chapeaute déjà d\'autres entités : elle ne peut pas devenir une entité de dernier niveau.');
            }

            if (in_array($type, Departement::TYPES_UNIQUES, true)) {
                $doublon = Departement::query()
                    ->where('type', $type)
                    ->when($courante, fn ($query) => $query->whereKeyNot($courante->id))
                    ->exists();

                if ($doublon) {
                    $validator->errors()->add('type', Departement::TYPE_LABELS[$type].' existe déjà : le système n\'en compte qu\'une seule.');
                }
            }

            if ($type === Departement::TYPE_DIRECTION) {
                if (! empty($parentId)) {
                    $validator->errors()->add('parent_id', 'La Direction Générale est au sommet et ne peut pas avoir de parent.');
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
            $typesParentAutorises = Departement::PARENTS_AUTORISES[$type] ?? [Departement::TYPE_DIRECTION];

            if (! in_array($parent->type, $typesParentAutorises, true)) {
                $message = $type === Departement::TYPE_SERVICE
                    ? 'Un service doit être rattaché à une direction centrale, à l\'agence comptable, ou directement à la Direction Générale (service rattaché).'
                    : 'Une direction centrale, l\'agence comptable, un bureau régional et un point focal se rattachent à la Direction Générale.';

                $validator->errors()->add('parent_id', $message);
            }
        });
    }
}
