<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
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

test('le rôle cumule les permissions des cellules planification et suivi & évaluation, plus l\'administration du référentiel', function () {
    seedRolesAndPermissions();

    $attendues = Role::findByName('agent-planification')->permissions->pluck('name')
        ->merge(Role::findByName('suivi-evaluation')->permissions->pluck('name'))
        ->merge(RoleAndPermissionSeeder::ADMINISTRATION_CONTROLE_GESTION)
        ->unique()->sort()->values()->all();

    expect(Role::findByName('service-controle-gestion')->permissions->pluck('name')->sort()->values()->all())
        ->toBe($attendues)
        // Il supprime les activités encore modifiables (brouillons, rejetées), sans les valider ;
        // il administre le référentiel sans rien y supprimer.
        ->toContain('delete_activites', 'create_users', 'edit_departements', 'manage_exercices', 'create_resultats')
        ->not->toContain('validate_activites', 'delete_users', 'delete_departements', 'delete_exercices', 'delete_objectifs', 'delete_resultats', 'delete_extrants');
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
    // Brouillon : supprimable, comme pour les profils qui saisissent.
    expect($user->can('delete', $activite))->toBeTrue();
});

test('le rôle ne modifie plus une activité validée', function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);
    $activite = activiteScg(Departement::factory()->create());

    expect($user->can('view', $activite))->toBeTrue();
    expect($user->can('update', $activite))->toBeFalse();
});

test('le rôle voit les activités de toutes les entités dans la programmation', function () {
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

test("le rôle est interdit sur l'arbitrage", function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route('validations.index'))->assertForbidden();
});

test('le rôle consulte et ouvre les formulaires d\'ajout du référentiel', function (string $name) {
    Exercice::factory()->actif()->create();
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route($name))->assertOk();
})->with([
    'departements.index', 'departements.create',
    'users.index', 'users.create',
    'exercices.index', 'exercices.create',
    'objectifs.index', 'objectifs.create',
    'resultats.index', 'resultats.create',
    'extrants.index', 'extrants.create',
]);

test('le rôle ajoute et modifie sans pouvoir supprimer', function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);
    $exercice = Exercice::factory()->create(['annee' => 2041, 'statut' => 'brouillon']);
    $dg = Departement::factory()->direction()->create();

    $this->actingAs($user)->post(route('departements.store'), [
        'code' => 'DC_SCG', 'nom' => 'Direction créée par le contrôle de gestion',
        'type' => Departement::TYPE_DEPARTEMENT, 'parent_id' => $dg->id,
    ])->assertSessionHasNoErrors();
    expect(Departement::where('code', 'DC_SCG')->exists())->toBeTrue();

    $this->actingAs($user)->put(route('exercices.update', $exercice), [
        'annee' => 2041, 'date_debut' => '2041-01-01', 'date_fin' => '2041-12-31', 'statut' => 'brouillon', 'libelle' => 'Exercice 2041',
    ])->assertSessionHasNoErrors();

    foreach ([route('exercices.destroy', $exercice), route('departements.destroy', $dg)] as $suppression) {
        $this->actingAs($user)->delete($suppression)->assertForbidden();
    }
    $this->actingAs($user)->post(route('exercices.annoncer-ouverture', $exercice))->assertForbidden();
});

test('le rôle attribue tous les rôles sauf superadmin, dbcgoq, service-controle-gestion et service-budget', function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);

    // Le formulaire ne propose que les rôles attribuables.
    $this->actingAs($user)->get(route('users.create'))->assertOk()
        ->assertViewHas('roles', fn ($roles) => $roles->pluck('name')->sort()->values()->all() === [
            'agent-planification', 'chef-service', 'responsable-programme', 'suivi-evaluation',
        ]);

    foreach (['superadmin', 'dbcgoq', 'service-controle-gestion', 'service-budget'] as $role) {
        $this->actingAs($user)->post(route('users.store'), [
            'name' => 'Compte '.$role, 'email' => $role.'@test.local',
            'roles' => [Role::findByName($role)->id],
        ])->assertSessionHasErrors('roles.0');
    }

    $this->actingAs($user)->post(route('users.store'), [
        'name' => 'Nouvel agent', 'email' => 'agent@test.local',
        'roles' => [Role::findByName('agent-planification')->id],
    ])->assertSessionHasNoErrors();
    expect(User::where('email', 'agent@test.local')->first()->hasRole('agent-planification'))->toBeTrue();
});

test('le rôle ne touche pas aux comptes superadmin et dbcgoq', function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);

    foreach (['superadmin', 'dbcgoq'] as $role) {
        $protege = userWithRole($role);

        $this->actingAs($user)->get(route('users.edit', $protege))->assertForbidden();
        $this->actingAs($user)->put(route('users.update', $protege), [
            'name' => 'Renommé', 'email' => $protege->email, 'roles' => [Role::findByName('chef-service')->id],
        ])->assertForbidden();
        $this->actingAs($user)->post(route('users.reinitialiser-mot-de-passe', $protege))->assertForbidden();
        // Pas de bouton Modifier sur sa fiche.
        $this->actingAs($user)->get(route('users.show', $protege))->assertOk()
            ->assertDontSee(route('users.edit', $protege), false);
    }
});

test('modifier un compte conserve les rôles que le contrôle de gestion ne peut pas attribuer', function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);
    $chargeMissions = userWithRole('service-budget');

    $this->actingAs($user)->put(route('users.update', $chargeMissions), [
        'name' => $chargeMissions->name, 'email' => $chargeMissions->email,
        'roles' => [Role::findByName('chef-service')->id],
    ])->assertSessionHasNoErrors();

    expect($chargeMissions->fresh()->getRoleNames()->sort()->values()->all())->toBe(['chef-service', 'service-budget']);
});

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
