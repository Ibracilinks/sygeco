<?php

use App\Models\Mission;
use App\Models\MissionBareme;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    MissionBareme::oublierCache();
});

test('les barèmes livrés reprennent les constantes du modèle', function () {
    expect(MissionBareme::where('groupe', MissionBareme::GROUPE_CATEGORIE_EXTERIEURE)->count())
        ->toBe(count(Mission::CATEGORIES_EXTERIEURES));
    expect(MissionBareme::where('groupe', MissionBareme::GROUPE_ZONE)->count())
        ->toBe(count(Mission::ZONES_EXTERIEURES));

    expect(Mission::categoriesExterieures()['cat_1']['indemnites'])
        ->toBe((float) Mission::CATEGORIES_EXTERIEURES['cat_1']['indemnites']);
    expect(Mission::zonesExterieures()['zone_c_ouest_cfa']['taux'])
        ->toBe((float) Mission::ZONES_EXTERIEURES['zone_c_ouest_cfa']['taux']);
});

test('un administrateur révise les barèmes depuis l\'application', function () {
    $admin = userWithRole('dbcgoq');

    $categorie = MissionBareme::where('groupe', MissionBareme::GROUPE_CATEGORIE_EXTERIEURE)->where('code', 'cat_1')->firstOrFail();
    $zone = MissionBareme::where('groupe', MissionBareme::GROUPE_ZONE)->where('code', 'zone_c_ouest_cfa')->firstOrFail();

    $this->actingAs($admin)->get(route('mission-baremes.index'))->assertOk()->assertSee('Barèmes des missions');

    $this->actingAs($admin)->put(route('mission-baremes.update'), [
        'baremes' => [
            $categorie->id => [
                'libelle' => 'Catégorie I',
                'description' => 'Ministère de Tutelle, PCA, Directeur Général',
                'frais_mission' => 80000,
                'indemnites' => 450000,
            ],
            $zone->id => [
                'libelle' => $zone->libelle,
                'taux' => 27.5,
            ],
        ],
    ])->assertRedirect(route('mission-baremes.index'));

    MissionBareme::oublierCache();

    expect(Mission::categoriesExterieures()['cat_1']['frais_mission'])->toBe(80000.0);
    expect(Mission::categoriesExterieures()['cat_1']['indemnites'])->toBe(450000.0);
    expect(Mission::zonesExterieures()['zone_c_ouest_cfa']['taux'])->toBe(27.5);
});

test('les nouveaux barèmes pilotent le calcul d\'une mission à l\'étranger', function () {
    $admin = userWithRole('dbcgoq');

    MissionBareme::where('groupe', MissionBareme::GROUPE_CATEGORIE_EXTERIEURE)->where('code', 'cat_1')
        ->update(['frais_mission' => 100000, 'indemnites' => 500000]);
    MissionBareme::where('groupe', MissionBareme::GROUPE_ZONE)->where('code', 'zone_c_ouest_cfa')
        ->update(['taux' => 10]);
    MissionBareme::oublierCache();

    $this->actingAs($admin)->post(route('missions.store'), [
        'type' => Mission::TYPE_EXTERIEURE,
        'reference' => '900/MSDS-CANAM-DAGRH',
        'objet' => 'Mission soumise au nouveau barème',
        'destination' => 'Abidjan',
        'zone_code' => 'zone_c_ouest_cfa',
        'date_document' => '2026-08-18',
        'date_depart' => '2026-08-21',
        'date_retour' => '2026-08-23',
        'lieu_signature' => 'Bamako',
        'statut' => 'brouillon',
        'participants' => [['nom_complet' => 'BOUBACAR DEMBELE', 'categorie' => 'cat_1']],
        'signataires' => [
            ['libelle' => 'Le Directeur Général', 'nom' => 'BOUBACAR DEMBELE', 'fonction' => 'DG'],
        ],
    ])->assertSessionHasNoErrors();

    $mission = Mission::where('reference', '900/MSDS-CANAM-DAGRH')->firstOrFail();

    // 3 jours, 2 nuitées : (100 000 × 3) + (500 000 × 2) = 1 300 000, majoré de 10 %.
    expect((float) $mission->montant_indemnites)->toBe(1300000.0);
    expect((float) $mission->montant_majoration)->toBe(130000.0);
    expect((float) $mission->montant_total)->toBe(1430000.0);
});

test('un chef ne peut pas réviser les barèmes', function () {
    $chef = userWithRole('responsable-programme');

    $this->actingAs($chef)->get(route('mission-baremes.index'))->assertForbidden();
});
