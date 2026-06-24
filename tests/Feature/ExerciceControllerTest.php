<?php

use App\Models\Exercice;
use App\Support\ActiveExercice;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('un invité est redirigé vers la connexion', function () {
    $this->get(route('exercices.index'))->assertRedirect(route('login'));
});

test('un agent (sans rôle dbcgoq) ne peut pas accéder aux exercices', function () {
    $agent = userWithRole('agent');

    $this->actingAs($agent)->get(route('exercices.index'))->assertForbidden();
});

test('le dbcgoq peut lister les exercices', function () {
    $admin = userWithRole('dbcgoq');
    Exercice::factory()->count(3)->create();

    $this->actingAs($admin)->get(route('exercices.index'))->assertOk();
});

test('le dbcgoq peut créer un exercice', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('exercices.store'), [
        'annee' => 2031,
        'date_debut' => '2031-01-01',
        'date_fin' => '2031-12-31',
        'statut' => 'brouillon',
    ])->assertRedirect(route('exercices.index'));

    $this->assertDatabaseHas('exercices', ['annee' => 2031, 'statut' => 'brouillon']);
});

test('la création échoue avec une année dupliquée', function () {
    $admin = userWithRole('dbcgoq');
    Exercice::factory()->create(['annee' => 2031]);

    $this->actingAs($admin)
        ->from(route('exercices.create'))
        ->post(route('exercices.store'), [
            'annee' => 2031,
            'date_debut' => '2031-01-01',
            'date_fin' => '2031-12-31',
            'statut' => 'brouillon',
        ])->assertSessionHasErrors('annee');
});

test('la création échoue si la date de fin précède la date de début', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)
        ->from(route('exercices.create'))
        ->post(route('exercices.store'), [
            'annee' => 2032,
            'date_debut' => '2032-06-01',
            'date_fin' => '2032-01-01',
            'statut' => 'brouillon',
        ])->assertSessionHasErrors('date_fin');
});

test('créer un exercice actif clôture les autres exercices actifs', function () {
    $admin = userWithRole('dbcgoq');
    $ancien = Exercice::factory()->actif()->create(['annee' => 2030]);

    $this->actingAs($admin)->post(route('exercices.store'), [
        'annee' => 2031,
        'date_debut' => '2031-01-01',
        'date_fin' => '2031-12-31',
        'statut' => 'actif',
    ]);

    expect($ancien->fresh()->statut)->toBe('cloture');
    $this->assertDatabaseHas('exercices', ['annee' => 2031, 'statut' => 'actif']);
});

test('activate fixe l\'exercice actif de la session', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->create(['annee' => 2031, 'statut' => 'brouillon']);

    $this->actingAs($admin)
        ->from(route('exercices.index'))
        ->post(route('exercices.activate', $exercice))
        ->assertRedirect(route('exercices.index'));

    expect(ActiveExercice::id())->toBe($exercice->id);
});

test('la création enregistre les fenêtres de mi-parcours et d\'évaluation', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('exercices.store'), [
        'annee' => 2033,
        'date_debut' => '2033-01-01',
        'date_fin' => '2033-12-31',
        'date_debut_mi_parcours' => '2033-06-01',
        'date_fin_mi_parcours' => '2033-06-30',
        'date_debut_evaluation' => '2033-12-01',
        'date_fin_evaluation' => '2033-12-20',
        'statut' => 'brouillon',
    ])->assertRedirect(route('exercices.index'));

    $exercice = \App\Models\Exercice::where('annee', 2033)->firstOrFail();
    expect($exercice->date_debut_mi_parcours->format('Y-m-d'))->toBe('2033-06-01');
    expect($exercice->date_fin_mi_parcours->format('Y-m-d'))->toBe('2033-06-30');
    expect($exercice->date_debut_evaluation->format('Y-m-d'))->toBe('2033-12-01');
    expect($exercice->date_fin_evaluation->format('Y-m-d'))->toBe('2033-12-20');
});

test('les dates de mi-parcours doivent rester dans l\'exercice', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)
        ->from(route('exercices.create'))
        ->post(route('exercices.store'), [
            'annee' => 2034,
            'date_debut' => '2034-01-01',
            'date_fin' => '2034-12-31',
            'date_debut_mi_parcours' => '2035-06-01', // hors exercice
            'statut' => 'brouillon',
        ])->assertSessionHasErrors('date_debut_mi_parcours');
});

test('la fin du mi-parcours ne peut précéder son début', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)
        ->from(route('exercices.create'))
        ->post(route('exercices.store'), [
            'annee' => 2035,
            'date_debut' => '2035-01-01',
            'date_fin' => '2035-12-31',
            'date_debut_mi_parcours' => '2035-06-15',
            'date_fin_mi_parcours' => '2035-06-01',
            'statut' => 'brouillon',
        ])->assertSessionHasErrors('date_fin_mi_parcours');
});

test('le dbcgoq peut voir le détail d\'un exercice', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->create(['annee' => 2031]);

    $this->actingAs($admin)->get(route('exercices.show', $exercice))->assertOk();
});
