<?php

use App\Models\Activite;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un invité est redirigé vers la connexion', function () {
    $this->get(route('sap.analytics'))->assertRedirect(route('login'));
});

test('le dbcgoq accède au dashboard SAP', function () {
    $user = userWithRole('dbcgoq');

    $this->actingAs($user)
        ->get(route('sap.analytics'))
        ->assertOk()
        ->assertSee('SAP', false)
        ->assertSee('Analytics Cloud', false)
        ->assertSee('Plan de Travail Annuel', false);
});

test('le dashboard SAP n\'utilise pas le layout de base (page autonome)', function () {
    $user = userWithRole('dbcgoq');

    $response = $this->actingAs($user)->get(route('sap.analytics'));

    // Page HTML autonome avec sa propre balise <html> et Chart.js, sans la sidebar Flux.
    $response->assertSee('<!DOCTYPE html>', false);
    $response->assertSee('chart.js', false);
    $response->assertDontSee('app-sidebar', false);
});

test('les vraies données du projet alimentent le dashboard', function () {
    $exercice = Exercice::factory()->actif()->create(['annee' => 2026]);
    // Une activité reliée à l'exercice 2026 via extrant -> objectif.
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => 2026]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();
    Activite::factory()->pourExtrant($extrant)->avecCout(5000000)->create();

    $user = userWithRole('dbcgoq');

    $this->actingAs($user)
        ->get(route('sap.analytics', ['annee' => 2026]))
        ->assertOk()
        ->assertSee('Exercice 2026', false);
});

test('le dashboard SAP est refusé aux autres profils, et absent de leur menu', function (string $role) {
    $user = userWithRole($role);

    $this->actingAs($user)->get(route('sap.analytics'))->assertForbidden();
    $this->actingAs($user)->get(route('dashboard'))->assertDontSee(route('sap.analytics'));
})->with(['superadmin', 'responsable-programme', 'chef-service', 'suivi-evaluation']);

test('un compte sans rôle n\'accède pas au dashboard SAP', function () {
    seedRolesAndPermissions();

    $this->actingAs(User::factory()->create())->get(route('sap.analytics'))->assertForbidden();
});

test('le lien SAP figure dans le menu du dbcgoq et du contrôle de gestion, qui y accèdent', function (string $role) {
    $user = userWithRole($role);

    $this->actingAs($user)->get(route('dashboard'))->assertSee(route('sap.analytics'));
    $this->actingAs($user)->get(route('sap.analytics'))->assertOk();
})->with(['dbcgoq', 'service-controle-gestion']);
