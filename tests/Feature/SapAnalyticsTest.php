<?php

use App\Models\Activite;
use App\Models\Exercice;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('un invité est redirigé vers la connexion', function () {
    $this->get(route('sap.analytics'))->assertRedirect(route('login'));
});

test('un utilisateur connecté accède au dashboard SAP', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('sap.analytics'))
        ->assertOk()
        ->assertSee('SAP', false)
        ->assertSee('Analytics Cloud', false)
        ->assertSee("Plan de Travail Annuel", false);
});

test('le dashboard SAP n\'utilise pas le layout de base (page autonome)', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('sap.analytics'));

    // Page HTML autonome avec sa propre balise <html> et Chart.js, sans la sidebar Flux.
    $response->assertSee('<!DOCTYPE html>', false);
    $response->assertSee('chart.js', false);
    $response->assertDontSee('app-sidebar', false);
});

test('les vraies données du projet alimentent le dashboard', function () {
    $exercice = Exercice::factory()->actif()->create(['annee' => 2026]);
    // Une activité reliée à l'exercice 2026 via extrant -> objectif.
    $objectif = \App\Models\Objectif::factory()->create(['exercice_id' => $exercice->id, 'annee' => 2026]);
    $resultat = \App\Models\Resultat::factory()->forObjectif($objectif)->create();
    $extrant = \App\Models\Extrant::factory()->forResultat($resultat)->create();
    Activite::factory()->pourExtrant($extrant)->avecCout(5000000)->create();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('sap.analytics', ['annee' => 2026]))
        ->assertOk()
        ->assertSee('Exercice 2026', false);
});
