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

test("le champ budget utilisé n'est proposé qu'à l'administration", function () {
    $exercice = Exercice::factory()->actif()->create();

    foreach (['dbcgoq', 'superadmin'] as $role) {
        $this->actingAs(userWithRole($role))
            ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
            ->get(route('evaluations.index', 'mi-parcours'))
            ->assertOk()
            ->assertSee('Budget utilisé (FCFA)');
    }

    $this->actingAs(userWithRole('responsable-programme'))
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('evaluations.index', 'mi-parcours'))
        ->assertOk()
        ->assertDontSee('Budget utilisé (FCFA)');
});

test("le budget n'est pas obligatoire : une fiche sans montant est acceptée", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $activite = Activite::factory()->pourExercice($exercice)->create(['statut' => 'valide']);

    $this->actingAs($admin)
        ->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
            'statut_execution' => 'realise',
            'observation' => 'Menée à son terme.',
            'valeur_indicateur' => 'Rapport produit',
        ])
        ->assertSessionHasNoErrors();

    expect($activite->evaluation('mi_parcours')->valeur_indicateur)->toBe('Rapport produit');
});

test('un budget posté directement reste ignoré', function () {
    $exercice = Exercice::factory()->actif()->create([
        'date_debut_mi_parcours' => now()->subDay(),
        'date_fin_mi_parcours' => now()->addDay(),
    ]);

    $departement = Departement::factory()->create();
    $chef = userWithRole('responsable-programme', ['departement_id' => $departement->id]);

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
