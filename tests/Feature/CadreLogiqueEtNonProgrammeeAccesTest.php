<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Export du cadre logique et activité non programmée
|--------------------------------------------------------------------------
| Réservés au dbcgoq, au service contrôle de gestion et à la cellule suivi &
| évaluation.
*/

test('les profils autorisés exportent le cadre logique de l\'évaluation', function (string $role) {
    Exercice::factory()->actif()->create();
    $user = userWithRole($role, ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route('evaluations.index', 'mi-parcours'))
        ->assertSee('Exporter (Cadre logique)')
        ->assertSee('+ Activité non programmée');
    $this->actingAs($user)->get(route('evaluations.export', 'mi-parcours'))->assertOk();
})->with(['dbcgoq', 'service-controle-gestion', 'suivi-evaluation']);

test('les autres profils ne voient ni l\'export ni l\'activité non programmée', function (string $role) {
    Exercice::factory()->actif()->create();
    $user = userWithRole($role, ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route('evaluations.index', 'mi-parcours'))
        ->assertOk()
        ->assertDontSee('Exporter (Cadre logique)')
        ->assertDontSee('+ Activité non programmée');
    $this->actingAs($user)->get(route('evaluations.export', 'mi-parcours'))->assertForbidden();
})->with(['responsable-programme', 'chef-service']);

test('l\'export du cadre logique d\'arbitrage est réservé au dbcgoq', function () {
    Exercice::factory()->actif()->create();
    $responsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($responsable)->get(route('validations.index'))
        ->assertOk()
        ->assertDontSee('Exporter (Cadre logique)');
    $this->actingAs($responsable)->get(route('validations.exporter'))->assertForbidden();

    $this->actingAs(userWithRole('dbcgoq'))->get(route('validations.exporter'))->assertOk();
});

test('la cellule suivi & évaluation enregistre une activité non programmée', function () {
    Exercice::factory()->actif()->create();
    $departement = Departement::factory()->create();
    $suivi = userWithRole('suivi-evaluation', ['departement_id' => $departement->id]);

    $this->actingAs($suivi)->post(route('activites.non-programmee.store'), [
        'departement_id' => $departement->id,
        'nom_activite' => 'Mission imprévue',
        'cout' => 100000,
        'statut_execution' => 'realise',
    ])->assertSessionHasNoErrors();

    expect(Activite::where('nom_activite', 'Mission imprévue')->value('non_programmee'))->toBeTrue();
});

test('un responsable de programme ne peut pas enregistrer d\'activité non programmée', function () {
    Exercice::factory()->actif()->create();
    $departement = Departement::factory()->create();
    $responsable = userWithRole('responsable-programme', ['departement_id' => $departement->id]);

    $this->actingAs($responsable)->post(route('activites.non-programmee.store'), [
        'departement_id' => $departement->id,
        'nom_activite' => 'Mission imprévue',
        'cout' => 100000,
    ])->assertForbidden();

    expect(Activite::where('nom_activite', 'Mission imprévue')->exists())->toBeFalse();
});
