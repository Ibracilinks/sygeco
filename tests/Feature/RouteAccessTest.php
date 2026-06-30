<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Garde-fous d'accès aux routes selon les rôles
|--------------------------------------------------------------------------
| Ces routes de référentiel (objectifs, résultats, extrants, départements,
| utilisateurs, exercices, budget, journal) sont réservées au rôle dbcgoq
| (ou chef en lecture pour certaines).
*/

$routesDbcgoqOnly = [
    'departements.index',
    'users.index',
    'budget.analysis',
    'journal.index',
    'exercices.index',
];

test('les invités sont redirigés vers la connexion', function (string $name) {
    $this->get(route($name))->assertRedirect(route('login'));
})->with($routesDbcgoqOnly);

test('un agent est interdit sur les routes réservées au dbcgoq', function (string $name) {
    $agent = userWithRole('agent');

    $this->actingAs($agent)->get(route($name))->assertForbidden();
})->with($routesDbcgoqOnly);

test('le dbcgoq accède aux routes réservées', function (string $name) {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->get(route($name))->assertOk();
})->with($routesDbcgoqOnly);

test('un chef de département peut lister objectifs, résultats et extrants', function (string $name) {
    $chef = userWithRole('chef');

    $this->actingAs($chef)->get(route($name))->assertOk();
})->with([
    'objectifs.index',
    'resultats.index',
    'extrants.index',
]);
