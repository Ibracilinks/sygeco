<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Extrant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Accès & rôles
|--------------------------------------------------------------------------
*/

test('un invité est redirigé vers la connexion', function () {
    $this->get(route('activites.index'))->assertRedirect(route('login'));
});

test('un utilisateur sans rôle ne peut pas accéder à la liste', function () {
    seedRolesAndPermissions();
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user)->get(route('activites.index'))->assertForbidden();
});

test('un agent peut voir la liste des activités', function () {
    $agent = userWithRole('agent');

    $this->actingAs($agent)->get(route('activites.index'))->assertOk();
});

test('le dbcgoq peut voir le formulaire de création', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->get(route('activites.create'))->assertOk();
});

test('un agent ne peut pas créer d\'activité (pas de permission create)', function () {
    $agent = userWithRole('agent');

    $this->actingAs($agent)->get(route('activites.create'))->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Création
|--------------------------------------------------------------------------
*/

test('le dbcgoq peut créer une activité', function () {
    $admin = userWithRole('dbcgoq');
    $extrant = Extrant::factory()->create();
    $departement = Departement::factory()->create();

    $response = $this->actingAs($admin)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $departement->id,
        'nom_activite' => 'Former les agents',
        'indicateur_objectivement_verifiable' => 'Nombre de sessions',
        'moyen_verification' => 'Liste de présence',
        'cout' => 1500000,
        'trimestre_1' => 'oui',
        'commentaires' => null,
    ]);

    $response->assertRedirect(route('activites.index'));
    $this->assertDatabaseHas('activites', [
        'nom_activite' => 'Former les agents',
        'statut' => 'brouillon',
        'trimestre_1' => 'oui',
        'trimestre_2' => 'non',
        'saisi_par' => $admin->id,
    ]);
});

test('la création échoue sans les champs requis', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)
        ->from(route('activites.create'))
        ->post(route('activites.store'), [])
        ->assertSessionHasErrors(['extrant_id', 'departement_id', 'nom_activite', 'cout']);
});

test('un chef de département voit son département forcé à la création', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $autreDep = Departement::factory()->create();
    $chef = \App\Models\User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');
    $extrant = Extrant::factory()->create();

    $this->actingAs($chef)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $autreDep->id, // tentative de contournement
        'nom_activite' => 'Activité du chef',
        'indicateur_objectivement_verifiable' => 'Indicateur',
        'moyen_verification' => 'PV',
        'cout' => 500000,
    ]);

    $this->assertDatabaseHas('activites', [
        'nom_activite' => 'Activité du chef',
        'departement_id' => $dep->id, // forcé sur le département du chef
    ]);
});

/*
|--------------------------------------------------------------------------
| Workflow : soumettre / valider / refuser
|--------------------------------------------------------------------------
*/

test('un chef peut soumettre une activité de son département', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $chef = \App\Models\User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');
    $activite = Activite::factory()->brouillon()->pourDepartement($dep)->create();

    $this->actingAs($chef)->post(route('activites.soumettre', $activite))
        ->assertRedirect(route('activites.index'));

    expect($activite->fresh()->statut)->toBe('soumis');
});

test('un chef ne peut pas soumettre une activité d\'un autre département', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $autreDep = Departement::factory()->create();
    $chef = \App\Models\User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');
    $activite = Activite::factory()->brouillon()->pourDepartement($autreDep)->create();

    $this->actingAs($chef)->post(route('activites.soumettre', $activite))->assertForbidden();

    expect($activite->fresh()->statut)->toBe('brouillon');
});

test('le dbcgoq peut valider une activité soumise', function () {
    $admin = userWithRole('dbcgoq');
    $activite = Activite::factory()->soumis()->create();

    $this->actingAs($admin)->post(route('activites.valider', $activite))
        ->assertRedirect(route('activites.index'))
        ->assertSessionHas('success');

    expect($activite->fresh())
        ->statut->toBe('valide')
        ->valide_par->toBe($admin->id);
});

test('un agent ne peut pas valider une activité', function () {
    $agent = userWithRole('agent');
    $activite = Activite::factory()->soumis()->create();

    $this->actingAs($agent)->post(route('activites.valider', $activite))
        ->assertSessionHas('error');

    expect($activite->fresh()->statut)->toBe('soumis');
});

test('le dbcgoq peut refuser une activité avec un motif', function () {
    $admin = userWithRole('dbcgoq');
    $activite = Activite::factory()->soumis()->create();

    $this->actingAs($admin)->post(route('activites.refuser', $activite), [
        'motif_refus' => 'Budget non justifié, merci de revoir',
    ])->assertRedirect(route('activites.show', $activite));

    expect($activite->fresh())
        ->statut->toBe('brouillon')
        ->motif_refus->toBe('Budget non justifié, merci de revoir');
});

test('refuser exige un motif d\'au moins 10 caractères', function () {
    $admin = userWithRole('dbcgoq');
    $activite = Activite::factory()->soumis()->create();

    $this->actingAs($admin)
        ->from(route('activites.show', $activite))
        ->post(route('activites.refuser', $activite), ['motif_refus' => 'court'])
        ->assertSessionHasErrors('motif_refus');
});
