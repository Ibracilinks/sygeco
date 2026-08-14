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

/**
 * Crée une activité rattachée au cadre logique d'un exercice donné.
 */
function activitePourAnalytique(Exercice $exercice, Departement $departement, array $attributs = []): Activite
{
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    return Activite::factory()
        ->pourExtrant($extrant)
        ->pourDepartement($departement)
        ->create($attributs);
}

test("l'analytique d'une Direction Centrale agrège les activités de ses services", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();

    $dc = Departement::factory()->departement()->create();
    $service = Departement::factory()->service()->enfantDe($dc)->create(['nom' => 'Service Budget']);

    activitePourAnalytique($exercice, $dc, ['cout' => 1000000, 'statut' => 'valide']);
    activitePourAnalytique($exercice, $service, ['cout' => 3000000, 'statut' => 'valide']);
    activitePourAnalytique($exercice, $service, ['cout' => 1000000, 'statut' => 'brouillon']);

    $response = $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('departements.show', $dc))
        ->assertOk();

    $analytique = $response->viewData('analytique');

    expect($analytique['nb_activites'])->toBe(3)
        ->and($analytique['nb_resultats'])->toBe(3)
        ->and($analytique['nb_extrants'])->toBe(3)
        ->and($analytique['budget_planifie'])->toBe(5000000.0)
        ->and($analytique['statuts']['valide'])->toBe(2)
        ->and($analytique['statuts']['brouillon'])->toBe(1)
        // Les validées non encore évaluées ne sont pas comptées comme non réalisées.
        ->and($analytique['execution']['non_evaluee'])->toBe(2)
        ->and($analytique['execution']['non_realise'])->toBe(0);

    // La ventilation situe la contribution de chaque entité du sous-arbre.
    $response->assertSee('Ventilation par entité');
    $response->assertSee('Service Budget');
});

test("l'analytique d'un service ne remonte pas les activités de sa Direction Centrale", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();

    $dc = Departement::factory()->departement()->create();
    $service = Departement::factory()->service()->enfantDe($dc)->create();

    activitePourAnalytique($exercice, $dc, ['cout' => 9000000]);
    activitePourAnalytique($exercice, $service, ['cout' => 2000000]);

    $analytique = $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('departements.show', $service))
        ->assertOk()
        ->viewData('analytique');

    expect($analytique['nb_activites'])->toBe(1)
        ->and($analytique['budget_planifie'])->toBe(2000000.0);
});

test("l'analytique ignore les activités d'un autre exercice", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $autreExercice = Exercice::factory()->create(['annee' => $exercice->annee + 1]);

    $dc = Departement::factory()->departement()->create();

    activitePourAnalytique($exercice, $dc, ['cout' => 1000000]);
    activitePourAnalytique($autreExercice, $dc, ['cout' => 8000000]);

    $analytique = $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('departements.show', $dc))
        ->assertOk()
        ->viewData('analytique');

    expect($analytique['nb_activites'])->toBe(1)
        ->and($analytique['budget_planifie'])->toBe(1000000.0);
});
