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

test('la cellule planification crée et soumet une activité de son entité', function () {
    $departement = Departement::factory()->create();
    $user = userWithRole('agent-planification', ['departement_id' => $departement->id]);
    // Un brouillon : seul état encore modifiable / soumettable.
    $activite = activiteValideePour($departement);
    $activite->update(['statut' => 'brouillon']);

    expect($user->can('create', Activite::class))->toBeTrue();
    expect($user->can('update', $activite->fresh()))->toBeTrue();
    expect($user->can('submit', $activite->fresh()))->toBeTrue();
});

test('la cellule planification ne peut pas toucher une activité hors de son entité', function () {
    $user = userWithRole('agent-planification', ['departement_id' => Departement::factory()->create()->id]);
    $activite = activiteValideePour(Departement::factory()->create());

    expect($user->can('view', $activite))->toBeFalse();
    expect($user->can('update', $activite))->toBeFalse();
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
