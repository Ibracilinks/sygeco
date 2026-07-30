<?php

namespace App\Support;

use Illuminate\Support\Collection;

class CadreLogique
{
    /**
     * Regroupe des activités selon le cadre logique :
     * Objectif stratégique → Résultat stratégique → Extrant → activités.
     *
     * @param  Collection<int, \App\Models\Activite>  $activites
     * @return Collection<int, array{objectif: mixed, resultats: Collection}>
     */
    public static function grouper(Collection $activites): Collection
    {
        return $activites
            ->groupBy(fn ($activite) => optional(optional($activite->extrant)->resultat)->objectif_id)
            ->map(function ($parObjectif) {
                $objectif = optional(optional($parObjectif->first()->extrant)->resultat)->objectif;

                $resultats = $parObjectif
                    ->groupBy(fn ($activite) => optional($activite->extrant)->resultat_id)
                    ->map(function ($parResultat) {
                        $resultat = optional($parResultat->first()->extrant)->resultat;

                        $extrants = $parResultat
                            ->groupBy(fn ($activite) => $activite->extrant_id)
                            ->map(fn ($parExtrant) => [
                                'extrant' => $parExtrant->first()->extrant,
                                'activites' => $parExtrant->values(),
                            ])
                            ->values();

                        return ['resultat' => $resultat, 'extrants' => $extrants];
                    })
                    ->values();

                return ['objectif' => $objectif, 'resultats' => $resultats];
            })
            ->values();
    }
}
