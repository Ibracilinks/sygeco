<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMissionBaremesRequest;
use App\Models\MissionBareme;

/**
 * Administration des barèmes de mission : les montants sont modifiables, mais la
 * liste des catégories et des zones est réglementaire — on n'en ajoute ni n'en
 * supprime depuis l'application, sous peine d'orpheliner les missions existantes.
 */
class MissionBaremeController extends Controller
{
    public function index()
    {
        $groupes = MissionBareme::query()
            ->orderBy('ordre')
            ->orderBy('code')
            ->get()
            ->groupBy('groupe');

        return view('pages.mission-baremes.index', compact('groupes'));
    }

    public function update(UpdateMissionBaremesRequest $request)
    {
        $lignes = $request->validated('baremes');

        $baremes = MissionBareme::query()->findMany(array_keys($lignes))->keyBy('id');

        foreach ($lignes as $id => $valeurs) {
            $bareme = $baremes->get((int) $id);

            if (! $bareme) {
                continue;
            }

            // Une zone ne porte qu'un taux ; une catégorie, des montants.
            $bareme->fill($bareme->estZone()
                ? [
                    'libelle' => $valeurs['libelle'],
                    'taux' => $valeurs['taux'] ?? 0,
                ]
                : [
                    'libelle' => $valeurs['libelle'],
                    'description' => $valeurs['description'] ?? null,
                    'frais_mission' => $valeurs['frais_mission'] ?? 0,
                    'indemnites' => $valeurs['indemnites'] ?? 0,
                ]);

            $bareme->save();
        }

        MissionBareme::oublierCache();

        return redirect()->route('mission-baremes.index')
            ->with('success', 'Barèmes des missions mis à jour.');
    }
}
