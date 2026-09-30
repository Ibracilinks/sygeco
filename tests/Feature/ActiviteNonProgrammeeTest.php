<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un dbcgoq enregistre une activité non programmée dans le suivi', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $service = Departement::factory()->service()->create();

    $this->actingAs($admin)->post(route('activites.non-programmee.store'), [
        'departement_id' => $service->id,
        'nom_activite' => 'Réunion imprévue de gestion de crise',
        'cout' => 750000,
        'statut_execution' => 'realise',
    ])->assertRedirect(route('evaluations.index', 'mi-parcours'));

    $activite = Activite::where('nom_activite', 'Réunion imprévue de gestion de crise')->first();

    expect($activite)->not->toBeNull();
    expect($activite->non_programmee)->toBeTrue();
    expect($activite->extrant_id)->toBeNull();
    expect((int) $activite->exercice_id)->toBe($exercice->id);
    expect($activite->statut)->toBe('valide');

    // Elle est rattachée à l'exercice actif et donc visible dans le suivi.
    expect(Activite::forExercice($exercice->id)->whereKey($activite->id)->exists())->toBeTrue();
});

test('une activité non programmée sans exercice actif est refusée', function () {
    $admin = userWithRole('dbcgoq');
    $service = Departement::factory()->service()->create();

    $this->actingAs($admin)
        ->from(route('evaluations.index', 'mi-parcours'))
        ->post(route('activites.non-programmee.store'), [
            'departement_id' => $service->id,
            'nom_activite' => 'Activité sans exercice',
            'cout' => 100000,
        ])->assertSessionHas('error');

    expect(Activite::where('nom_activite', 'Activité sans exercice')->exists())->toBeFalse();
});
