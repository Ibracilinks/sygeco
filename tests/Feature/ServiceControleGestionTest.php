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
| Rôle « service-controle-gestion »
|--------------------------------------------------------------------------
| Cumule les cellules planification et suivi & évaluation, sur l'ensemble des
| entités : il met à jour le PTA de toutes les structures tant que les
| activités restent modifiables, et renseigne les évaluations.
*/

function activiteScg(Departement $departement, string $statut = 'valide'): Activite
{
    $exercice = Exercice::factory()->actif()->create();
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    $activite = Activite::factory()->valide()->pourExtrant($extrant)->pourDepartement($departement)->create();
    $activite->update(['statut' => $statut]);

    return $activite->fresh();
}

test('le rôle cumule les permissions des cellules planification et suivi & évaluation', function () {
    seedRolesAndPermissions();

    $attendues = Role::findByName('agent-planification')->permissions->pluck('name')
        ->merge(Role::findByName('suivi-evaluation')->permissions->pluck('name'))
        ->unique()->sort()->values()->all();

    expect(Role::findByName('service-controle-gestion')->permissions->pluck('name')->sort()->values()->all())
        ->toBe($attendues)
        ->not->toContain('validate_activites', 'delete_activites', 'view_users');
});

test("le rôle n'est pas restreint à son entité", function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);

    expect($user->perimetreActivitesIds())->toBeNull();
});

test('le rôle met à jour et soumet une activité brouillon de toute entité', function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);
    $activite = activiteScg(Departement::factory()->create(), 'brouillon');

    expect($user->can('view', $activite))->toBeTrue();
    expect($user->can('create', Activite::class))->toBeTrue();
    expect($user->can('update', $activite))->toBeTrue();
    expect($user->can('submit', $activite))->toBeTrue();
    expect($user->can('delete', $activite))->toBeFalse();
});

test('le rôle ne modifie plus une activité validée', function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);
    $activite = activiteScg(Departement::factory()->create());

    expect($user->can('view', $activite))->toBeTrue();
    expect($user->can('update', $activite))->toBeFalse();
});

test("le rôle voit les activités de toutes les entités dans la programmation", function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);
    $autre = activiteScg(Departement::factory()->create(), 'brouillon');

    $this->actingAs($user)->get(route('activites.index'))
        ->assertOk()
        ->assertSee($autre->nom_activite);
});

test("le rôle accède à la programmation, au cadre logique et à l'évaluation", function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route('activites.index'))->assertOk();
    $this->actingAs($user)->get(route('objectifs.index'))->assertOk();
    $this->actingAs($user)->get(route('extrants.index'))->assertOk();
    $this->actingAs($user)->get(route('evaluations.index', 'mi-parcours'))->assertOk();
});

test("le rôle est interdit sur l'administration et l'arbitrage", function (string $name) {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route($name))->assertForbidden();
})->with([
    'departements.index',
    'users.index',
    'exercices.index',
    'validations.index',
]);

test("le rôle évalue une activité validée d'une autre entité", function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);
    $activite = activiteScg(Departement::factory()->create());

    $this->actingAs($user)->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
        'statut_execution' => 'en_cours',
        'observation' => 'Contrôle de gestion',
        'valeur_indicateur' => 1,
    ])->assertSessionHas('success');

    expect($activite->fresh()->evaluation('mi_parcours'))
        ->statut_execution->toBe('en_cours')
        ->maj_par->toBe($user->id);
});
