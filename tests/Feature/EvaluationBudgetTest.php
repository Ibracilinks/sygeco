<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Budget consommé : saisie réservée à l'administration
|--------------------------------------------------------------------------
*/

test("le budget utilisé ne se saisit plus depuis l'évaluation", function () {
    $exercice = Exercice::factory()->actif()->create();

    // Le champ a été retiré du formulaire : plus aucun profil ne le voit.
    foreach (['dbcgoq', 'superadmin', 'chef'] as $role) {
        $this->actingAs(userWithRole($role))
            ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
            ->get(route('evaluations.index', 'mi-parcours'))
            ->assertOk()
            ->assertDontSee('Budget utilisé (FCFA)');
    }
});

test('un budget posté directement reste ignoré', function () {
    $exercice = Exercice::factory()->actif()->create([
        'date_debut_mi_parcours' => now()->subDay(),
        'date_fin_mi_parcours' => now()->addDay(),
    ]);

    $departement = Departement::factory()->create();
    $chef = userWithRole('chef', ['departement_id' => $departement->id]);

    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    $activite = Activite::factory()
        ->pourExtrant($extrant)
        ->pourDepartement($departement)
        ->create(['statut' => 'valide']);

    $this->actingAs($chef)->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
        'statut_execution' => 'realise',
        'observation' => 'Activité menée à son terme',
        'valeur_indicateur' => 1,
        'montant_utilise' => 999999,
    ]);

    $evaluation = $activite->evaluations()->where('periode', 'mi_parcours')->first();

    // L'état d'exécution est bien enregistré, le montant posté est ignoré.
    expect($evaluation)->not->toBeNull()
        ->and($evaluation->statut_execution)->toBe('realise')
        ->and($evaluation->montant_utilise)->toBeNull();
});
