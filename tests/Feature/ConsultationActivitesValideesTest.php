<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Consultation des activités validées
|--------------------------------------------------------------------------
| Toute activité validée est visible de tous les utilisateurs, en lecture seule.
| Brouillons, soumissions et rejets restent bornés au périmètre habituel, comme
| l'évaluation et l'ajout de pièces jointes.
*/

test('la liste montre les activités validées des autres entités, pas leurs brouillons ni leurs soumissions', function () {
    $exercice = Exercice::factory()->actif()->create();
    $monService = Departement::factory()->create();
    $autre = Departement::factory()->create();
    $chef = userWithRole('chef-service', ['departement_id' => $monService->id]);

    Activite::factory()->valide()->pourDepartement($autre)->pourExercice($exercice)->create(['nom_activite' => 'Validée ailleurs']);
    Activite::factory()->enAttente()->pourDepartement($autre)->pourExercice($exercice)->create(['nom_activite' => 'En attente ailleurs']);
    Activite::factory()->brouillon()->pourDepartement($autre)->pourExercice($exercice)->create(['nom_activite' => 'Brouillon ailleurs']);

    $this->actingAs($chef)->get(route('activites.index'))
        ->assertOk()
        ->assertSee('Validée ailleurs')
        ->assertDontSee('En attente ailleurs')
        ->assertDontSee('Brouillon ailleurs');
});

test('le périmètre habituel reste visible en plus des activités validées', function () {
    $exercice = Exercice::factory()->actif()->create();
    $direction = Departement::factory()->create();
    $autre = Departement::factory()->create();
    $responsable = userWithRole('responsable-programme', ['departement_id' => $direction->id]);

    Activite::factory()->brouillon()->pourDepartement($direction)->pourExercice($exercice)->create(['nom_activite' => 'Mon brouillon']);
    Activite::factory()->valide()->pourDepartement($autre)->pourExercice($exercice)->create(['nom_activite' => 'Validée ailleurs']);

    $this->actingAs($responsable)->get(route('activites.index'))
        ->assertOk()
        ->assertSee('Mon brouillon')
        ->assertSee('Validée ailleurs');
});

test('la fiche d\'une activité validée d\'une autre entité s\'ouvre, sans formulaire de pièce jointe', function () {
    $responsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->create()->id]);
    $validee = Activite::factory()->valide()->pourDepartement(Departement::factory()->create())->create();
    $enAttente = Activite::factory()->enAttente()->pourDepartement(Departement::factory()->create())->create();

    $this->actingAs($responsable)->get(route('activites.show', $validee))
        ->assertOk()
        ->assertDontSee(route('activites.pieces-jointes.store', $validee));

    $this->actingAs($responsable)->get(route('activites.show', $enAttente))->assertForbidden();
});

test('on ne joint pas de fichier à une activité validée hors de son périmètre', function () {
    Storage::fake('public');
    $responsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->create()->id]);
    $validee = Activite::factory()->valide()->pourDepartement(Departement::factory()->create())->create();

    $this->actingAs($responsable)
        ->post(route('activites.pieces-jointes.store', $validee), ['fichier' => UploadedFile::fake()->create('preuve.pdf', 10)])
        ->assertSessionHas('error');

    expect($validee->piecesJointes()->count())->toBe(0);
});

test('on n\'évalue pas une activité validée hors de son périmètre', function () {
    $responsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->create()->id]);
    $validee = Activite::factory()->valide()->pourTrimestre(1)->pourDepartement(Departement::factory()->create())->create();

    $this->actingAs($responsable)
        ->post(route('evaluations.enregistrer', [$validee, 'fin-annee']), [
            'statut_execution' => 'realise',
            'observation' => 'Fait',
            'valeur_indicateur' => '1',
        ])
        ->assertForbidden();

    expect($validee->evaluations()->count())->toBe(0);
});
