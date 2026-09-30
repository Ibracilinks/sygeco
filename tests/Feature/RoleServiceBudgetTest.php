<?php

use App\Models\Departement;
use App\Models\Mission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Rôle « service-budget » : le chargé des missions
|--------------------------------------------------------------------------
| Il gère le module Missions de bout en bout et ne voit rien d'autre.
*/

test('le chargé des missions accède au module Missions', function (string $name) {
    $user = userWithRole('service-budget');

    $this->actingAs($user)->get(route($name, ['type' => Mission::TYPE_REGION]))->assertOk();
})->with(['missions.index', 'missions.create']);

test('le chargé des missions enregistre une mission', function () {
    $user = userWithRole('service-budget');
    $departement = Departement::factory()->create();

    $this->actingAs($user)->post(route('missions.store'), [
        'type' => Mission::TYPE_MEME_VILLE,
        'reference' => '400/MSDS-CANAM-DAGRH',
        'structures_demandeuses' => [$departement->id],
        'objet' => 'Réunion de travail au ministère',
        'date_depart' => '2026-08-20',
        'date_retour' => '2026-08-21',
        'tickets_carburant_par_jour' => 2,
        'statut' => 'brouillon',
        'participants' => [['nom_complet' => 'MOUSSA TRAORE']],
        'signataires' => [
            ['libelle' => 'LA DBCGOQ', 'nom' => 'AICHATOU DAO', 'fonction' => 'Directrice'],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Mission::where('reference', '400/MSDS-CANAM-DAGRH')->exists())->toBeTrue();
});

test('le chargé des missions est interdit hors des missions', function (string $name) {
    $user = userWithRole('service-budget');

    $this->actingAs($user)->get(route($name))->assertForbidden();
})->with([
    'activites.index',
    'objectifs.index',
    'extrants.index',
    'departements.index',
    'users.index',
    'exercices.index',
    'budget.analysis',
    'journal.index',
    'validations.index',
    'mission-baremes.index',
]);

test('le tableau de bord renvoie le chargé des missions vers ses missions', function () {
    $user = userWithRole('service-budget');

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('missions.index'));
});

test('le menu du chargé des missions se limite aux missions', function () {
    $user = userWithRole('service-budget');

    $html = $this->actingAs($user)->get(route('missions.index'))->assertOk()->getContent();

    expect($html)->toContain('data-groupe="missions"')
        ->and($html)->not->toContain('data-groupe="planification"')
        ->and($html)->not->toContain('data-groupe="evaluation"')
        ->and($html)->not->toContain('data-groupe="organisation"')
        // Les notifications restent joignables malgré le menu réduit.
        ->and($html)->toContain(route('notifications.index'));
});
