<?php

use App\Models\Departement;
use App\Models\Mission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function missionSignatairesPayload(): array
{
    return [
        [
            'libelle' => 'LA DBCGOQ',
            'nom' => 'AICHATOU DAO',
            'fonction' => 'Directrice',
        ],
        [
            'libelle' => "L'AGENT COMPTABLE",
            'nom' => 'PIERRE TRAORE',
            'fonction' => 'Inspecteur du Trésor',
        ],
        [
            'libelle' => 'LE DIRECTEUR GENERAL',
            'nom' => 'BOUBACAR DEMBELE',
            'fonction' => "Commandeur de l'Ordre National",
        ],
    ];
}

test('le dbcgoq peut créer une mission même ville', function () {
    $admin = userWithRole('dbcgoq');
    $departement = Departement::factory()->create();

    $response = $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_MEME_VILLE,
        'reference' => '005/MSDS-CANAM-DAGRH',
        'departement_id' => $departement->id,
        'objet' => 'Mission locale de supervision',
        'date_document' => '2026-08-18',
        'date_depart' => '2026-08-19',
        'date_retour' => '2026-08-20',
        'nombre_jours' => 2,
        'montant_par_jour' => 25000,
        'tickets_carburant_par_jour' => 2,
        'montant_ticket_carburant' => 5000,
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [
            ['nom_complet' => 'MOUSSA TRAORE'],
            ['nom_complet' => 'FATOUMATA DIALLO'],
        ],
        'signataires' => missionSignatairesPayload(),
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '005/MSDS-CANAM-DAGRH')->first();

    expect($mission)->not->toBeNull();
    expect($mission->type)->toBe(Mission::TYPE_MEME_VILLE);
    expect((int) $mission->nombre_personnes)->toBe(2);
    expect((int) $mission->nombre_tickets_carburant)->toBe(4);
    expect((float) $mission->montant_indemnites)->toBe(100000.0);
    expect((float) $mission->montant_carburant)->toBe(20000.0);
    expect((float) $mission->montant_total)->toBe(120000.0);
    expect($mission->participants()->count())->toBe(2);
    expect($mission->signataires()->count())->toBe(3);
});

test("le dbcgoq peut créer une mission à l'extérieur avec calcul par catégorie et zone", function () {
    $admin = userWithRole('dbcgoq');
    $departement = Departement::factory()->create();

    $response = $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_EXTERIEURE,
        'reference' => '261/MSDS-CANAM-DAGRH',
        'departement_id' => $departement->id,
        'objet' => 'Mission de représentation internationale',
        'destination' => 'Paris',
        'zone_code' => 'exceptionnelle_europe',
        'date_document' => '2026-08-18',
        'date_depart' => '2026-08-21',
        'date_retour' => '2026-08-23',
        'nombre_jours' => 3,
        'frais_participation_nombre' => 2,
        'frais_participation_unitaire' => 100000,
        'frais_visa_nombre' => 1,
        'frais_visa_unitaire' => 50000,
        'billets_affaire_nombre' => 1,
        'billets_affaire_unitaire' => 800000,
        'billets_economique_nombre' => 1,
        'billets_economique_unitaire' => 350000,
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [
            ['nom_complet' => 'BOUBACAR DEMBELE', 'categorie' => 'cat_1', 'nombre_nuitees' => 2],
            ['nom_complet' => 'PIERRE TRAORE', 'categorie' => 'cat_2', 'nombre_nuitees' => 2],
        ],
        'signataires' => missionSignatairesPayload(),
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '261/MSDS-CANAM-DAGRH')->first();

    expect($mission)->not->toBeNull();
    expect($mission->type)->toBe(Mission::TYPE_EXTERIEURE);
    expect($mission->zone_code)->toBe('exceptionnelle_europe');
    expect((float) $mission->zone_taux)->toBe(50.0);
    expect((float) $mission->montant_indemnites)->toBe(1775000.0);
    expect((float) $mission->montant_majoration)->toBe(887500.0);
    expect((float) $mission->montant_autres_frais)->toBe(250000.0);
    expect((float) $mission->montant_billets)->toBe(1150000.0);
    expect((float) $mission->montant_total)->toBe(4062500.0);

    $participants = $mission->participants()->orderBy('ordre')->get();
    expect($participants)->toHaveCount(2);
    expect((float) $participants[0]->sous_total)->toBe(1025000.0);
    expect((float) $participants[0]->majoration_montant)->toBe(512500.0);
    expect((float) $participants[0]->total_general)->toBe(1537500.0);
    expect((float) $participants[1]->sous_total)->toBe(750000.0);
    expect((float) $participants[1]->majoration_montant)->toBe(375000.0);
    expect((float) $participants[1]->total_general)->toBe(1125000.0);
});

test("une mission à l'extérieur est refusée sans zone ni catégorie", function () {
    $admin = userWithRole('dbcgoq');

    $response = $this->actingAs($admin)
        ->from(route('missions.create', ['type' => Mission::TYPE_EXTERIEURE]))
        ->post(route('missions.store'), [
            'type' => Mission::TYPE_EXTERIEURE,
            'reference' => '262/MSDS-CANAM-DAGRH',
            'objet' => 'Mission incomplète',
            'date_document' => '2026-08-18',
            'date_depart' => '2026-08-21',
            'date_retour' => '2026-08-23',
            'nombre_jours' => 3,
            'statut' => 'brouillon',
            'participants' => [
                ['nom_complet' => 'AGENT TEST'],
            ],
            'signataires' => missionSignatairesPayload(),
        ]);

    $response->assertRedirect(route('missions.create', ['type' => Mission::TYPE_EXTERIEURE]));
    $response->assertSessionHasErrors(['zone_code', 'participants.0.categorie']);
});
