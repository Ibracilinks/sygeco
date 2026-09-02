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
        'tickets_carburant_par_jour' => 2,
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
    // Aucun montant pour une mission même ville : uniquement des tickets.
    expect((float) $mission->montant_indemnites)->toBe(0.0);
    expect((float) $mission->montant_carburant)->toBe(0.0);
    expect((float) $mission->montant_total)->toBe(0.0);
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
            'statut' => 'brouillon',
            'participants' => [
                ['nom_complet' => 'AGENT TEST'],
            ],
            'signataires' => missionSignatairesPayload(),
        ]);

    $response->assertRedirect(route('missions.create', ['type' => Mission::TYPE_EXTERIEURE]));
    $response->assertSessionHasErrors(['zone_code', 'participants.0.categorie']);
});

test('une mission regionale calcule les jours et les nuitées par étape', function () {
    $admin = userWithRole('dbcgoq');
    $departement = Departement::factory()->create();

    $response = $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_REGION,
        'reference' => '300/MSDS-CANAM-DAGRH',
        'departement_id' => $departement->id,
        'objet' => 'Mission de supervision regionale',
        'destination' => 'Sikasso',
        'date_document' => '2026-08-18',
        'date_depart' => '2026-08-20',
        'date_retour' => '2026-08-29',
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [
            ['nom_complet' => 'MOUSSA TRAORE', 'categorie' => 'cat_4'],
            ['nom_complet' => 'FATOUMATA DIALLO', 'categorie' => 'cat_7'],
        ],
        'etapes' => [
            [
                'type_etape' => 'region',
                'bareme' => 'national',
                'localite' => 'Sikasso',
                'date_depart' => '2026-08-20',
                'date_retour' => '2026-08-24',
            ],
            [
                'type_etape' => 'cercle',
                'bareme' => 'meme_region',
                'localite' => 'Koutiala',
                'date_depart' => '2026-08-25',
                'date_retour' => '2026-08-29',
                'premiere_nuitee_payee' => '1',
            ],
        ],
        'signataires' => missionSignatairesPayload(),
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '300/MSDS-CANAM-DAGRH')->first();

    expect($mission)->not->toBeNull();
    expect($mission->type)->toBe(Mission::TYPE_REGION);
    expect((int) $mission->nombre_jours)->toBe(10);
    expect((float) $mission->montant_total)->toBe(735000.0);
    expect($mission->etapes()->count())->toBe(2);

    $etapes = $mission->etapes()->orderBy('ordre')->get();
    expect((int) $etapes[0]->nombre_jours)->toBe(5);
    expect((int) $etapes[0]->nombre_nuitees)->toBe(4);
    expect((int) $etapes[1]->nombre_jours)->toBe(5);
    expect((int) $etapes[1]->nombre_nuitees)->toBe(5);

    $participants = $mission->participants()->orderBy('ordre')->get();
    expect((float) $participants[0]->total_general)->toBe(452500.0);
    expect((int) $participants[0]->nombre_nuitees)->toBe(9);
    expect((float) $participants[1]->total_general)->toBe(282500.0);
    expect((int) $participants[1]->nombre_nuitees)->toBe(9);
});

test('une mission regionale est refusee sans categorie ni etape', function () {
    $admin = userWithRole('dbcgoq');

    $response = $this->actingAs($admin)
        ->from(route('missions.create', ['type' => Mission::TYPE_REGION]))
        ->post(route('missions.store'), [
            'type' => Mission::TYPE_REGION,
            'reference' => '301/MSDS-CANAM-DAGRH',
            'objet' => 'Mission regionale incomplete',
            'destination' => 'Kayes',
            'date_document' => '2026-08-18',
            'date_depart' => '2026-08-20',
            'date_retour' => '2026-08-25',
            'statut' => 'brouillon',
            'participants' => [
                ['nom_complet' => 'AGENT TEST'],
            ],
            'signataires' => missionSignatairesPayload(),
        ]);

    $response->assertRedirect(route('missions.create', ['type' => Mission::TYPE_REGION]));
    $response->assertSessionHasErrors(['etapes', 'participants.0.categorie']);
});

test('le nombre de jours d\'une mission même ville exclut les week-ends', function () {
    // Du vendredi 21 au mardi 25 août 2026 : samedi et dimanche exclus → 3 jours ouvrables.
    expect(Mission::calculerNombreJoursOuvrables('2026-08-21', '2026-08-25'))->toBe(3);
    expect(Mission::calculerNombreJoursOuvrables('2026-08-22', '2026-08-23'))->toBe(1);

    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_MEME_VILLE,
        'reference' => '006/MSDS-CANAM-DAGRH',
        'objet' => 'Mission locale à cheval sur un week-end',
        'date_document' => '2026-08-20',
        'date_depart' => '2026-08-21',
        'date_retour' => '2026-08-25',
        'tickets_carburant_par_jour' => 2,
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [['nom_complet' => 'MOUSSA TRAORE']],
        'signataires' => missionSignatairesPayload(),
    ])->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '006/MSDS-CANAM-DAGRH')->firstOrFail();

    expect((int) $mission->nombre_jours)->toBe(3);
    expect((int) $mission->nombre_tickets_carburant)->toBe(6);
});

test('le nombre de jours saisi à la main est conservé', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_MEME_VILLE,
        'reference' => '007/MSDS-CANAM-DAGRH',
        'objet' => 'Mission locale avec durée ajustée',
        'date_document' => '2026-08-20',
        'date_depart' => '2026-08-21',
        'date_retour' => '2026-08-25',
        'nombre_jours' => 5,
        'tickets_carburant_par_jour' => 1,
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [['nom_complet' => 'MOUSSA TRAORE']],
        'signataires' => missionSignatairesPayload(),
    ])->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '007/MSDS-CANAM-DAGRH')->firstOrFail();

    expect((int) $mission->nombre_jours)->toBe(5);
    expect((int) $mission->nombre_tickets_carburant)->toBe(5);
});

test('la fonction d\'un signataire est facultative', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_MEME_VILLE,
        'reference' => '008/MSDS-CANAM-DAGRH',
        'objet' => 'Mission locale sans fonction de signataire',
        'date_document' => '2026-08-20',
        'date_depart' => '2026-08-24',
        'date_retour' => '2026-08-25',
        'tickets_carburant_par_jour' => 1,
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [['nom_complet' => 'MOUSSA TRAORE']],
        'signataires' => [
            ['libelle' => 'Le Directeur Général', 'nom' => 'BOUBACAR DEMBELE', 'fonction' => ''],
        ],
    ])->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '008/MSDS-CANAM-DAGRH')->firstOrFail();

    expect($mission->signataires()->count())->toBe(1);
    expect($mission->signataires()->first()->fonction)->toBe('');
});

test('la rubrique d\'un type propose la création dans ce type', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)
        ->get(route('missions.index', ['type' => Mission::TYPE_EXTERIEURE]))
        ->assertOk()
        ->assertSee(route('missions.create', ['type' => Mission::TYPE_EXTERIEURE]), false);
});

test('le formulaire de création est dédié au type demandé, sans onglets', function () {
    $admin = userWithRole('dbcgoq');

    $reponse = $this->actingAs($admin)
        ->get(route('missions.create', ['type' => Mission::TYPE_EXTERIEURE]))
        ->assertOk()
        ->assertSee("À l'étranger");

    // Plus de bascule vers les autres types depuis l'écran de saisie.
    $reponse->assertDontSee(route('missions.create', ['type' => Mission::TYPE_REGION]), false);
    $reponse->assertDontSee(route('missions.create', ['type' => Mission::TYPE_MEME_VILLE]), false);
});

test('les nuitées d\'une mission à l\'étranger valent le nombre de jours moins un', function () {
    $admin = userWithRole('dbcgoq');

    // Du 21 au 25 : 5 jours → 4 nuitées, quelle que soit la valeur transmise.
    $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_EXTERIEURE,
        'reference' => '262/MSDS-CANAM-DAGRH',
        'objet' => 'Mission internationale de cinq jours',
        'destination' => 'Dakar',
        'zone_code' => 'exceptionnelle_europe',
        'date_document' => '2026-08-18',
        'date_depart' => '2026-08-21',
        'date_retour' => '2026-08-25',
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [
            ['nom_complet' => 'BOUBACAR DEMBELE', 'categorie' => 'cat_1', 'nombre_nuitees' => 99],
        ],
        'signataires' => missionSignatairesPayload(),
    ])->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '262/MSDS-CANAM-DAGRH')->firstOrFail();

    expect((int) $mission->nombre_jours)->toBe(5);
    expect((int) $mission->participants()->first()->nombre_nuitees)->toBe(4);
});

test('une mission créée sans statut part en brouillon', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_EXTERIEURE,
        'reference' => '263/MSDS-CANAM-DAGRH',
        'objet' => 'Mission internationale sans statut transmis',
        'destination' => 'Genève',
        'zone_code' => 'exceptionnelle_europe',
        'date_document' => '2026-08-18',
        'date_depart' => '2026-08-21',
        'date_retour' => '2026-08-23',
        'lieu_signature' => 'Bamako',
        'participants' => [['nom_complet' => 'BOUBACAR DEMBELE', 'categorie' => 'cat_1']],
        'signataires' => missionSignatairesPayload(),
    ])->assertSessionHasNoErrors();

    expect(Mission::where('reference', '263/MSDS-CANAM-DAGRH')->firstOrFail()->statut)->toBe('brouillon');
});

test('le document même ville reprend l\'en-tête officiel et le tableau du modèle', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_MEME_VILLE,
        'reference' => '005/MSDS-CANAM-DAGRH',
        'objet' => 'Pointage contradictoire',
        'date_document' => '2026-01-20',
        'date_depart' => '2026-01-21',
        'date_retour' => '2026-02-20',
        'tickets_carburant_par_jour' => 1,
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [['nom_complet' => 'MOULAYE I BA']],
        'signataires' => missionSignatairesPayload(),
    ])->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '005/MSDS-CANAM-DAGRH')->firstOrFail();

    $this->actingAs($admin)->get(route('missions.show', $mission))
        ->assertOk()
        ->assertSee('Ministère de la Santé et du Développement Social')
        ->assertSee('République du Mali')
        ->assertSee("Budget relatif a l'ordre de mission", false)
        ->assertSee('Mtant par nuitée')
        ->assertSee('Nombre de ticket par jour')
        ->assertSee('MOULAYE I BA')
        ->assertSee('logo_canam.png', false);
});

test('une mission en brouillon peut être finalisée depuis sa fiche', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_MEME_VILLE,
        'reference' => '010/MSDS-CANAM-DAGRH',
        'objet' => 'Mission à finaliser',
        'date_document' => '2026-08-20',
        'date_depart' => '2026-08-24',
        'date_retour' => '2026-08-25',
        'tickets_carburant_par_jour' => 1,
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [['nom_complet' => 'MOUSSA TRAORE']],
        'signataires' => missionSignatairesPayload(),
    ])->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '010/MSDS-CANAM-DAGRH')->firstOrFail();

    $this->actingAs($admin)->get(route('missions.show', $mission))->assertOk()->assertSee('Finaliser');

    $this->actingAs($admin)->post(route('missions.finaliser', $mission))
        ->assertRedirect(route('missions.show', $mission));

    expect($mission->fresh()->statut)->toBe('finalise');

    // Le bouton disparaît une fois la mission arrêtée, et l'action devient sans effet.
    $this->actingAs($admin)->get(route('missions.show', $mission))->assertOk()->assertDontSee('>Finaliser<', false);
    $this->actingAs($admin)->post(route('missions.finaliser', $mission))->assertSessionHas('error');
});

test('le document intérieur du pays reprend le modèle officiel avec carburant et péages', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_REGION,
        'reference' => '014/CANAM-SI',
        'objet' => 'Supervision des antennes',
        'destination' => 'Sikasso',
        'date_document' => '2026-08-18',
        'date_depart' => '2026-08-18',
        'date_retour' => '2026-08-22',
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'distance_totale_km' => 300,
        'consommation_aux_cent' => 20,
        'litres_par_jour_ville' => 5,
        'prix_litre_carburant' => 866,
        'location_vehicule_jours' => 5,
        'location_vehicule_tarif' => 50000,
        'montant_peages' => 5000,
        'participants' => [['nom_complet' => 'MOUSSA TRAORE', 'categorie' => 'cat_4']],
        'etapes' => [
            ['type_etape' => 'cercle', 'bareme' => 'national', 'localite' => 'Koutiala', 'date_depart' => '2026-08-18', 'date_retour' => '2026-08-19'],
            ['type_etape' => 'region', 'bareme' => 'national', 'localite' => 'Sikasso', 'date_depart' => '2026-08-20', 'date_retour' => '2026-08-22'],
        ],
        'signataires' => missionSignatairesPayload(),
    ])->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '014/CANAM-SI')->firstOrFail();

    // Carburant : 300 km × 20/100 × 866 = 51 960 ; ville : 5 L × 5 jours × 866 = 21 650.
    expect((float) $mission->montant_carburant_trajet)->toBe(51960.0);
    expect((float) $mission->montant_carburant_ville)->toBe(21650.0);
    expect((float) $mission->montant_location_vehicule)->toBe(250000.0);
    expect((float) $mission->montant_total)->toBe(
        (float) $mission->montant_indemnites + 51960.0 + 21650.0 + 250000.0 + 5000.0
    );

    // Le document sépare les indemnités cercles et régions.
    $repartition = $mission->repartitionRegionale();
    expect($repartition)->toHaveCount(2);
    expect($repartition[0]['libelle'])->toBe('Indemnités cercles');
    expect($repartition[1]['libelle'])->toBe('Indemnités régions');

    $this->actingAs($admin)->get(route('missions.show', $mission))
        ->assertOk()
        ->assertSee('Indemnités cercles')
        ->assertSee('Montant carburant trajet')
        ->assertSee('III- Péages')
        ->assertSee('Total général');
});

test('les étapes ne peuvent pas totaliser plus de jours que la mission', function () {
    $admin = userWithRole('dbcgoq');

    // Mission de 5 jours, étapes de 3 + 4 jours : incohérent.
    $this->actingAs($admin)
        ->from(route('missions.create', ['type' => Mission::TYPE_REGION]))
        ->post(route('missions.store'), [
            'type' => Mission::TYPE_REGION,
            'reference' => '015/CANAM-SI',
            'objet' => 'Étapes incohérentes',
            'destination' => 'Ségou',
            'date_document' => '2026-08-18',
            'date_depart' => '2026-08-18',
            'date_retour' => '2026-08-22',
            'lieu_signature' => 'Bamako',
            'statut' => 'brouillon',
            'participants' => [['nom_complet' => 'MOUSSA TRAORE', 'categorie' => 'cat_4']],
            'etapes' => [
                ['type_etape' => 'cercle', 'bareme' => 'national', 'localite' => 'Bla', 'date_depart' => '2026-08-18', 'date_retour' => '2026-08-20'],
                ['type_etape' => 'region', 'bareme' => 'national', 'localite' => 'Ségou', 'date_depart' => '2026-08-19', 'date_retour' => '2026-08-22'],
            ],
            'signataires' => missionSignatairesPayload(),
        ])->assertSessionHasErrors('etapes');

    expect(Mission::where('reference', '015/CANAM-SI')->exists())->toBeFalse();
});

test('une étape hors de la période de la mission est refusée', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)
        ->from(route('missions.create', ['type' => Mission::TYPE_REGION]))
        ->post(route('missions.store'), [
            'type' => Mission::TYPE_REGION,
            'reference' => '016/CANAM-SI',
            'objet' => 'Étape hors période',
            'destination' => 'Ségou',
            'date_document' => '2026-08-18',
            'date_depart' => '2026-08-18',
            'date_retour' => '2026-08-22',
            'lieu_signature' => 'Bamako',
            'statut' => 'brouillon',
            'participants' => [['nom_complet' => 'MOUSSA TRAORE', 'categorie' => 'cat_4']],
            'etapes' => [
                ['type_etape' => 'region', 'bareme' => 'national', 'localite' => 'Ségou', 'date_depart' => '2026-08-25', 'date_retour' => '2026-08-26'],
            ],
            'signataires' => missionSignatairesPayload(),
        ])->assertSessionHasErrors('etapes.0.date_depart');
});

test('les postes de transport laissés vides sont enregistrés à zéro', function () {
    $admin = userWithRole('dbcgoq');
    $departement = Departement::factory()->create();

    // Le formulaire région poste toujours ces champs : vides, ils arrivent à null
    // alors que les colonnes sont NOT NULL.
    $payload = [
        'type' => Mission::TYPE_REGION,
        'reference' => '301/MSDS-CANAM-DAGRH',
        'departement_id' => $departement->id,
        'objet' => 'Mission de supervision sans frais de transport',
        'destination' => 'Tombouctou',
        'date_document' => '2026-08-18',
        'date_depart' => '2026-08-20',
        'date_retour' => '2026-08-24',
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'nombre_vehicules' => '',
        'distance_totale_km' => '',
        'consommation_aux_cent' => '',
        'litres_par_jour_ville' => '',
        'prix_litre_carburant' => '',
        'location_vehicule_jours' => '',
        'location_vehicule_tarif' => '',
        'montant_peages' => '',
        'billets_economique_nombre' => '',
        'billets_economique_unitaire' => '',
        'participants' => [
            ['nom_complet' => 'MOUSSA TRAORE', 'categorie' => 'cat_4'],
        ],
        'etapes' => [
            [
                'type_etape' => 'region',
                'bareme' => 'national',
                'localite' => 'Tombouctou',
                'date_depart' => '2026-08-20',
                'date_retour' => '2026-08-24',
            ],
        ],
        'signataires' => missionSignatairesPayload(),
    ];

    $this->actingAs($admin)->post(route('missions.store'), $payload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '301/MSDS-CANAM-DAGRH')->first();

    expect($mission)->not->toBeNull();
    expect((float) $mission->distance_totale_km)->toBe(0.0);
    expect((float) $mission->consommation_aux_cent)->toBe(0.0);
    expect((float) $mission->prix_litre_carburant)->toBe(0.0);
    expect((int) $mission->nombre_vehicules)->toBe(0);
    expect((float) $mission->montant_carburant)->toBe(0.0);
    expect((float) $mission->montant_autres_frais)->toBe(0.0);

    $this->actingAs($admin)
        ->put(route('missions.update', $mission), array_merge($payload, [
            'objet' => 'Mission de supervision corrigée',
        ]))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $mission->refresh();

    expect($mission->objet)->toBe('Mission de supervision corrigée');
    expect((float) $mission->distance_totale_km)->toBe(0.0);
    expect((float) $mission->montant_carburant)->toBe(0.0);
});

test('une mission retient plusieurs services demandeurs, son code budgétaire et son point de départ', function () {
    $admin = userWithRole('dbcgoq');
    $premier = Departement::factory()->create(['nom' => 'Direction des Opérations']);
    $second = Departement::factory()->create(['nom' => 'Direction Financière']);

    // Ni date du document ni lieu de signature : ces champs ont quitté le formulaire.
    $response = $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_REGION,
        'reference' => '302/MSDS-CANAM-DAGRH',
        'structures_demandeuses' => [$premier->id, $second->id],
        'objet' => 'Mission de supervision conjointe',
        'code_budgetaire' => Mission::CODES_BUDGETAIRES[1],
        'point_depart' => 'Ségou',
        'destination' => 'Mopti',
        'date_depart' => '2026-08-20',
        'date_retour' => '2026-08-24',
        'statut' => 'brouillon',
        'participants' => [
            ['nom_complet' => 'MOUSSA TRAORE', 'categorie' => 'cat_4'],
        ],
        'etapes' => [
            [
                'type_etape' => 'region',
                'bareme' => 'national',
                'localite' => 'Mopti',
                'date_depart' => '2026-08-20',
                'date_retour' => '2026-08-24',
            ],
        ],
        'signataires' => missionSignatairesPayload(),
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '302/MSDS-CANAM-DAGRH')->first();

    expect($mission)->not->toBeNull();
    expect($mission->departements->pluck('id')->all())->toBe([$premier->id, $second->id]);
    // La première structure reste la structure principale des documents.
    expect($mission->departement_id)->toBe($premier->id);
    expect($mission->code_budgetaire)->toBe(Mission::CODES_BUDGETAIRES[1]);
    expect($mission->point_depart)->toBe('Ségou');
    expect($mission->date_document?->toDateString())->toBe(today()->toDateString());
    expect($mission->lieu_signature)->toBe('Bamako');

    // La mise à jour remplace la liste des services demandeurs.
    $this->actingAs($admin)->put(route('missions.update', $mission), [
        'type' => Mission::TYPE_REGION,
        'reference' => '302/MSDS-CANAM-DAGRH',
        'structures_demandeuses' => [$second->id],
        'objet' => 'Mission de supervision conjointe',
        'code_budgetaire' => '',
        'point_depart' => 'Bamako',
        'destination' => 'Mopti',
        'date_depart' => '2026-08-20',
        'date_retour' => '2026-08-24',
        'statut' => 'brouillon',
        'participants' => [
            ['nom_complet' => 'MOUSSA TRAORE', 'categorie' => 'cat_4'],
        ],
        'etapes' => [
            [
                'type_etape' => 'region',
                'bareme' => 'national',
                'localite' => 'Mopti',
                'date_depart' => '2026-08-20',
                'date_retour' => '2026-08-24',
            ],
        ],
        'signataires' => missionSignatairesPayload(),
    ])->assertRedirect()->assertSessionHasNoErrors();

    $mission->refresh()->load('departements');

    expect($mission->departements->pluck('id')->all())->toBe([$second->id]);
    expect($mission->departement_id)->toBe($second->id);
    expect($mission->code_budgetaire)->toBeNull();
    expect($mission->point_depart)->toBe('Bamako');
    // La date du document posée à la création n'est pas perdue par la modification.
    expect($mission->date_document?->toDateString())->toBe(today()->toDateString());
});

test('le formulaire de mission ne demande plus la date du document ni le lieu de signature', function () {
    $admin = userWithRole('dbcgoq');

    foreach ([Mission::TYPE_MEME_VILLE, Mission::TYPE_EXTERIEURE, Mission::TYPE_REGION] as $type) {
        $response = $this->actingAs($admin)->get(route('missions.create', ['type' => $type]));

        $response->assertOk();
        $response->assertDontSee('name="date_document"', false);
        $response->assertDontSee('name="lieu_signature"', false);
        $response->assertSee('name="structures_demandeuses[]"', false);
        $response->assertSee('name="code_budgetaire"', false);
    }

    $this->actingAs($admin)->get(route('missions.create', ['type' => Mission::TYPE_REGION]))
        ->assertSee('name="point_depart"', false);
});
