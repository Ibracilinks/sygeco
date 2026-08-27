<?php

namespace App\Support;

use App\Models\Activite;
use Illuminate\Support\Collection;

class CadreLogique
{
    /**
     * Regroupe des activités selon le cadre logique :
     * Objectif stratégique → Résultat stratégique → Extrant → activités.
     *
     * @param  Collection<int, Activite>  $activites
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
                                // Même lecture que la liste de programmation : au sein d'un
                                // extrant, les activités sont classées par structure porteuse
                                // de A à Z. La clé de tri est une chaîne unique, `sortBy` ne
                                // sachant pas comparer des tableaux.
                                'activites' => $parExtrant
                                    ->sortBy(fn ($activite) => mb_strtolower($activite->departement->code ?? '')
                                        .'|'.str_pad((string) $activite->id, 12, '0', STR_PAD_LEFT))
                                    ->values(),
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
