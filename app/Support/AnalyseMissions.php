<?php

namespace App\Support;

use App\Models\Mission;
use Illuminate\Support\Collection;

/**
 * Statistiques des missions d'une période : volume, budget engagé, coûts moyens,
 * carburant, dépenses par poste et destinations.
 */
class AnalyseMissions
{
    /**
     * @param  Collection<int, Mission>  $missions  avec participants et departements chargés
     */
    public function __construct(private readonly Collection $missions) {}

    /**
     * @return array<string, mixed>
     */
    public function statistiques(): array
    {
        $missions = $this->missions;

        $parType = collect(Mission::TYPES)->map(fn ($libelle, $type) => [
            'libelle' => $libelle,
            'nombre' => $missions->where('type', $type)->count(),
            'budget' => (float) $missions->where('type', $type)->sum('montant_total'),
        ]);

        $parStructure = $missions
            ->flatMap(fn (Mission $mission) => ($mission->departements->isNotEmpty() ? $mission->departements : collect([$mission->departement])->filter())
                ->map(fn ($structure) => ['structure' => $structure->code ?: $structure->nom, 'mission' => $mission]))
            ->groupBy('structure')
            ->map(fn ($lignes, $structure) => [
                'structure' => $structure,
                'nombre' => $lignes->count(),
                'budget' => (float) $lignes->sum(fn ($ligne) => $ligne['mission']->montant_total),
            ])
            ->sortByDesc('budget')
            ->values();

        $parMois = collect(range(1, 12))->mapWithKeys(fn ($mois) => [
            $mois => $missions->filter(fn (Mission $mission) => (int) $mission->date_depart?->month === $mois)->count(),
        ]);

        $interieur = $missions->where('type', Mission::TYPE_REGION);

        // Destinations à l'intérieur : les localités des étapes (pas les régions
        // saisies pour les statistiques), avec le nombre de jours passés sur place.
        $destinationsInterieur = $interieur
            ->flatMap(fn (Mission $mission) => $mission->etapes->map(fn ($etape) => [
                'localite' => trim((string) $etape->localite),
                'type' => Mission::TYPES_ETAPES_REGIONALES[$etape->type_etape] ?? (string) $etape->type_etape,
                'jours' => (int) $etape->nombre_jours,
                'mission' => $mission->id,
            ]))
            ->filter(fn ($etape) => $etape['localite'] !== '')
            ->groupBy(fn ($etape) => mb_strtoupper($etape['localite']))
            ->map(fn ($etapes) => [
                'localite' => $etapes->first()['localite'],
                'type' => $etapes->first()['type'],
                'missions' => $etapes->pluck('mission')->unique()->count(),
                'jours' => $etapes->sum('jours'),
            ])
            ->sortByDesc('missions')
            ->values();

        $destinationsEtranger = $missions->where('type', Mission::TYPE_EXTERIEURE)
            ->groupBy(fn (Mission $mission) => trim((string) $mission->destination) ?: 'Non précisée')
            ->map(fn ($groupe, $destination) => [
                'destination' => $destination,
                'zone' => (string) $groupe->first()->zone_label,
                'missions' => $groupe->count(),
                'budget' => (float) $groupe->sum('montant_total'),
            ])
            ->sortByDesc('budget')
            ->values();

        $somme = fn (string $colonne) => (float) $missions->sum(fn (Mission $mission) => (float) $mission->{$colonne});

        // Dépenses par poste budgétaire (les sous-totaux « autres frais » ne sont pas
        // repris : ils regroupent des postes déjà listés).
        $depenses = collect([
            'Frais de mission et indemnités' => $somme('montant_indemnites'),
            'Majoration (étranger)' => $somme('montant_majoration'),
            'Carburant' => $somme('montant_carburant'),
            'Location de véhicules' => $somme('montant_location_vehicule'),
            "Billets d'avion" => $somme('montant_billets'),
            'Péages' => $somme('montant_peages'),
            'Frais de participation' => $somme('frais_participation_total'),
            'Frais de visa' => $somme('frais_visa_total'),
        ])->filter(fn ($montant) => $montant > 0)->sortDesc();

        $joursPersonnes = $missions->sum(fn (Mission $mission) => (int) $mission->nombre_jours * max(1, $mission->participants->count()));
        $budget = (float) $missions->sum('montant_total');

        return [
            'nombre' => $missions->count(),
            // Engagé = missions finalisées ; les brouillons ne sont encore que prévus.
            'budget_engage' => (float) $missions->where('statut', 'finalise')->sum('montant_total'),
            'budget_prevu' => (float) $missions->where('statut', 'brouillon')->sum('montant_total'),
            'cout_moyen_mission' => $missions->isNotEmpty() ? $budget / $missions->count() : 0.0,
            'cout_jour_personne' => $joursPersonnes > 0 ? $budget / $joursPersonnes : 0.0,
            'tickets_carburant' => (int) $missions->where('type', Mission::TYPE_MEME_VILLE)->sum('nombre_tickets_carburant'),
            'carburant' => [
                'litres_trajet' => $interieur->sum(fn (Mission $mission) => $mission->litresTrajet()),
                'montant_trajet' => (float) $interieur->sum(fn (Mission $mission) => (float) $mission->montant_carburant_trajet),
                'litres_ville' => $interieur->sum(fn (Mission $mission) => $mission->litresVille()),
                'montant_ville' => (float) $interieur->sum(fn (Mission $mission) => (float) $mission->montant_carburant_ville),
                'distance' => (float) $interieur->sum(fn (Mission $mission) => (float) $mission->distance_totale_km),
            ],
            'depenses' => $depenses,
            'destinations_interieur' => $destinationsInterieur,
            'destinations_etranger' => $destinationsEtranger,
            'budget' => (float) $missions->sum('montant_total'),
            'participants' => $missions->sum(fn (Mission $mission) => $mission->participants->count()),
            'jours_personnes' => $joursPersonnes,
            'finalisees' => $missions->where('statut', 'finalise')->count(),
            'brouillons' => $missions->where('statut', 'brouillon')->count(),
            'par_type' => $parType,
            'par_structure' => $parStructure,
            'par_mois' => $parMois,
        ];
    }
}
