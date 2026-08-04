<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Support\ActiveExercice;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Budget consommé : saisie réservée à l'administration
|--------------------------------------------------------------------------
*/

test("le champ budget utilisé n'est proposé qu'à l'administration", function () {
    $exercice = Exercice::factory()->actif()->create();

    $admin = userWithRole('dbcgoq');
    $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('evaluations.index', 'mi-parcours'))
        ->assertOk()
        ->assertSee('Budget utilisé (FCFA)');

    $chef = userWithRole('chef');
    $this->actingAs($chef)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('evaluations.index', 'mi-parcours'))
        ->assertOk()
        ->assertDontSee('Budget utilisé (FCFA)');
});

test('un chef ne peut pas renseigner le budget consommé par soumission directe', function () {
    $exercice = Exercice::factory()->actif()->create([
        'date_debut_mi_parcours' => now()->subDay(),
        'date_fin_mi_parcours' => now()->addDay(),
    ]);

    $departement = Departement::factory()->create();
    $chef = userWithRole('chef', ['departement_id' => $departement->id]);

    $objectif = Objectif::factory()->create(['exercice_id' => $exercice->id, 'annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    $activite = Activite::factory()
        ->pourExtrant($extrant)
        ->pourDepartement($departement)
        ->create(['statut' => 'valide']);

    $this->actingAs($chef)->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
        'statut_execution' => 'realise',
        'observation' => 'Activité menée à son terme',
        'montant_utilise' => 999999,
    ]);

    $evaluation = $activite->evaluations()->where('periode', 'mi_parcours')->first();

    // L'état d'exécution est bien enregistré, le montant posté est ignoré.
    expect($evaluation)->not->toBeNull()
        ->and($evaluation->statut_execution)->toBe('realise')
        ->and($evaluation->montant_utilise)->toBeNull();
});
