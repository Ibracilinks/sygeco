<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Rôles « agent-planification » et « suivi-evaluation »
|--------------------------------------------------------------------------
| Deux cellules rattachées à une entité : la première prépare et soumet le PTA,
| la seconde renseigne l'exécution et les évaluations. Chacune ne doit voir que
| les écrans de son périmètre.
*/

/**
 * Chaîne minimale exercice → objectif → résultat → extrant → activité validée.
 */
function activiteValideePour(Departement $departement): Activite
{
    $exercice = Exercice::factory()->actif()->create();
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    return Activite::factory()->valide()->pourExtrant($extrant)->pourDepartement($departement)->create();
}

test('les deux rôles existent avec leurs permissions', function () {
    seedRolesAndPermissions();

    $planification = Role::findByName('agent-planification');
    $suivi = Role::findByName('suivi-evaluation');

    expect($planification->permissions->pluck('name')->all())
        ->toContain('create_activites', 'edit_activites', 'submit_activites', 'view_objectifs')
        ->not->toContain('evaluate_activites', 'validate_activites', 'view_users', 'delete_activites');

    expect($suivi->permissions->pluck('name')->all())
        ->toContain('evaluate_activites', 'view_activites', 'edit_indicateurs')
        ->not->toContain('create_activites', 'edit_activites', 'validate_activites');
});

test('les deux rôles sont limités aux activités de leur seule entité', function (string $role) {
    $departement = Departement::factory()->create();
    $user = userWithRole($role, ['departement_id' => $departement->id]);

    expect($user->perimetreActivitesIds())->toBe([$departement->id]);
})->with(['agent-planification', 'suivi-evaluation']);

test('les deux rôles accèdent à la programmation et au cadre logique', function (string $role) {
    $user = userWithRole($role, ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route('activites.index'))->assertOk();
    $this->actingAs($user)->get(route('objectifs.index'))->assertOk();
    $this->actingAs($user)->get(route('extrants.index'))->assertOk();
})->with(['agent-planification', 'suivi-evaluation']);

test('les deux rôles sont interdits sur l\'administration et l\'arbitrage', function (string $name) {
    foreach (['agent-planification', 'suivi-evaluation'] as $role) {
        $user = userWithRole($role, ['departement_id' => Departement::factory()->create()->id]);

        $this->actingAs($user)->get(route($name))->assertForbidden();
    }
})->with([
    'departements.index',
    'users.index',
    'budget.analysis',
    'journal.index',
    'exercices.index',
    'validations.index',
]);

test('la cellule planification n\'accède pas à l\'évaluation', function () {
    $user = userWithRole('agent-planification', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route('evaluations.index', 'mi-parcours'))->assertForbidden();

    $this->actingAs($user)->get(route('activites.index'))
        ->assertOk()
        ->assertDontSee('Évaluation : Mi-parcours');
});

test('la cellule planification crée et modifie une activité de son entité', function () {
    $departement = Departement::factory()->create();
    $user = userWithRole('agent-planification', ['departement_id' => $departement->id]);
    // Un brouillon : seul état encore modifiable.
    $activite = activiteValideePour($departement);
    $activite->update(['statut' => 'brouillon']);

    expect($user->can('create', Activite::class))->toBeTrue();
    expect($user->can('update', $activite->fresh()))->toBeTrue();
});

test('la cellule planification voit les activités de toutes les entités sans pouvoir y toucher', function () {
    $user = userWithRole('agent-planification', ['departement_id' => Departement::factory()->create()->id]);
    $activite = activiteValideePour(Departement::factory()->create());

    // Elle voit les activités de toutes les entités, quel que soit leur statut,
    // mais elles restent hors de son périmètre de travail.
    $enAttente = Activite::factory()->enAttente()->pourDepartement($activite->departement)->create();
    $brouillon = Activite::factory()->brouillon()->pourDepartement($activite->departement)->create();

    expect($user->can('view', $activite))->toBeTrue();
    expect($user->can('view', $enAttente))->toBeTrue();
    expect($user->can('view', $brouillon))->toBeTrue();
    expect($user->can('update', $activite))->toBeFalse();
    expect($user->can('update', $brouillon))->toBeFalse();
    expect($user->can('joindre', $activite))->toBeFalse();
    expect($user->can('submit', $brouillon))->toBeFalse();
});

test('la cellule suivi & évaluation accède à l\'évaluation sans pouvoir modifier le PTA', function () {
    $departement = Departement::factory()->create();
    $user = userWithRole('suivi-evaluation', ['departement_id' => $departement->id]);
    $activite = activiteValideePour($departement);

    $this->actingAs($user)->get(route('evaluations.index', 'mi-parcours'))->assertOk();

    expect($user->can('create', Activite::class))->toBeFalse();
    expect($user->can('update', $activite))->toBeFalse();
    expect($user->can('delete', $activite))->toBeFalse();
});

test('la cellule planification modifie les activités de son entité sans pouvoir les soumettre', function () {
    $departement = Departement::factory()->create();
    $user = userWithRole('agent-planification', ['departement_id' => $departement->id]);
    $brouillon = Activite::factory()->brouillon()->pourDepartement($departement)->create();

    expect($user->can('update', $brouillon))->toBeTrue();
    expect($user->can('submit', $brouillon))->toBeFalse();

    $this->actingAs($user)->post(route('activites.soumettre', $brouillon))->assertForbidden();
    expect($brouillon->fresh()->statut)->toBe('brouillon');
});
