<?php

use App\Models\Exercice;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

test('le formulaire de création d\'exercice est accessible (pas de 404)', function () {
    $admin = userWithRole('dbcgoq');

    // Régression : « /exercices/create » ne doit pas être capturé par le joker {exercice}.
    $this->actingAs($admin)->get(route('exercices.create'))->assertOk();
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

// Les fenêtres se règlent depuis la fiche de l'exercice : le formulaire de création
// ne demande plus que l'année, la période et le statut.
test('la mise à jour enregistre les fenêtres de mi-parcours et d\'évaluation', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->create([
        'annee' => 2033,
        'date_debut' => '2033-01-01',
        'date_fin' => '2033-12-31',
    ]);

    $this->actingAs($admin)->put(route('exercices.update', $exercice), [
        'annee' => 2033,
        'date_debut' => '2033-01-01',
        'date_fin' => '2033-12-31',
        'date_debut_mi_parcours' => '2033-06-01',
        'date_fin_mi_parcours' => '2033-06-30',
        'date_debut_evaluation' => '2033-12-01',
        'date_fin_evaluation' => '2033-12-20',
        'statut' => 'brouillon',
    ])->assertRedirect(route('exercices.index'));

    $exercice = $exercice->fresh();
    expect($exercice->date_debut_mi_parcours->format('Y-m-d'))->toBe('2033-06-01');
    expect($exercice->date_fin_mi_parcours->format('Y-m-d'))->toBe('2033-06-30');
    expect($exercice->date_debut_evaluation->format('Y-m-d'))->toBe('2033-12-01');
    expect($exercice->date_fin_evaluation->format('Y-m-d'))->toBe('2033-12-20');
});

test('les dates de mi-parcours doivent rester dans l\'exercice', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->create([
        'annee' => 2034,
        'date_debut' => '2034-01-01',
        'date_fin' => '2034-12-31',
    ]);

    $this->actingAs($admin)
        ->from(route('exercices.edit', $exercice))
        ->put(route('exercices.update', $exercice), [
            'annee' => 2034,
            'date_debut' => '2034-01-01',
            'date_fin' => '2034-12-31',
            'date_debut_mi_parcours' => '2035-06-01', // hors exercice
            'statut' => 'brouillon',
        ])->assertSessionHasErrors('date_debut_mi_parcours');
});

test('la fin du mi-parcours ne peut précéder son début', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->create([
        'annee' => 2035,
        'date_debut' => '2035-01-01',
        'date_fin' => '2035-12-31',
    ]);

    $this->actingAs($admin)
        ->from(route('exercices.edit', $exercice))
        ->put(route('exercices.update', $exercice), [
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

test('le bandeau du centre de pilotage suit l\'exercice actif de la session', function () {
    $admin = userWithRole('dbcgoq');
    Exercice::factory()->create(['annee' => 2040, 'statut' => 'actif']);
    $autre = Exercice::factory()->create(['annee' => 2041, 'statut' => 'brouillon']);

    // On isole le bandeau : l'année apparaît ailleurs dans la page (listes, sélecteurs).
    $anneeDuBandeau = function () {
        $html = $this->get(route('dashboard'))->assertOk()->getContent();
        $bandeau = substr($html, (int) strpos($html, 'app-sidebar-intro-chip'), 300);
        preg_match('/\\d{4}/', strip_tags($bandeau), $trouve);

        return $trouve[0] ?? null;
    };

    $this->actingAs($admin);
    expect($anneeDuBandeau())->toBe('2040');

    $this->post(route('exercices.activate', $autre));
    expect($anneeDuBandeau())->toBe('2041');
});
