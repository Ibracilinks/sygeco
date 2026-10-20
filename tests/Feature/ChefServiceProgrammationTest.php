<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Models\User;
use App\Notifications\ActiviteSaisieNotification;
use App\Notifications\ActiviteSoumiseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Programmation par le chef de service
|--------------------------------------------------------------------------
| Le chef de service saisit les activités de son service sans les soumettre :
| la soumission revient au responsable de programme de l'entité parente.
*/

/**
 * Direction centrale chapeautant un service, avec un chef de service et le
 * responsable de programme de la direction centrale.
 *
 * @return array{direction: Departement, service: Departement, chefService: User, responsable: User}
 */
function organigrammeChefService(): array
{
    $direction = Departement::factory()->departement()->create();
    $service = Departement::factory()->service()->enfantDe($direction)->create();

    return [
        'direction' => $direction,
        'service' => $service,
        'chefService' => userWithRole('chef-service', ['departement_id' => $service->id]),
        'responsable' => userWithRole('responsable-programme', ['departement_id' => $direction->id]),
    ];
}

test('le chef de service programme une activité pour son service', function () {
    ['service' => $service, 'chefService' => $chefService] = organigrammeChefService();

    $exercice = Exercice::factory()->actif()->create();
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $extrant = Extrant::factory()->forResultat(Resultat::factory()->forObjectif($objectif)->create())->create();

    $this->actingAs($chefService)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $service->id,
        'nom_activite' => 'Formation des agents du service',
        'indicateur_objectivement_verifiable' => 'Nombre d\'agents formés',
        'moyen_verification' => 'Rapport de formation',
        'cout' => 1_500_000,
        'trimestre_1' => 'on',
    ])->assertSessionHasNoErrors()->assertRedirect(route('activites.index'));

    $activite = Activite::firstWhere('nom_activite', 'Formation des agents du service');
    expect($activite->departement_id)->toBe($service->id);
    expect($activite->statut)->toBe('brouillon');
});

test('le responsable de programme est notifié quand son chef de service enregistre une activité', function () {
    Notification::fake();
    ['service' => $service, 'chefService' => $chefService, 'responsable' => $responsable] = organigrammeChefService();
    $autreResponsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->departement()->create()->id]);

    $exercice = Exercice::factory()->actif()->create();
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $extrant = Extrant::factory()->forResultat(Resultat::factory()->forObjectif($objectif)->create())->create();

    $this->actingAs($chefService)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $service->id,
        'nom_activite' => 'Inventaire du matériel du service',
        'indicateur_objectivement_verifiable' => 'Inventaire réalisé',
        'moyen_verification' => 'Fiche d\'inventaire',
        'cout' => 500_000,
        'trimestre_2' => 'on',
    ])->assertSessionHasNoErrors();

    Notification::assertSentTo($responsable, ActiviteSaisieNotification::class,
        fn ($notification) => $notification->activite->nom_activite === 'Inventaire du matériel du service');
    Notification::assertNotSentTo($autreResponsable, ActiviteSaisieNotification::class);
});

test('une activité enregistrée par le responsable lui-même ne notifie personne', function () {
    Notification::fake();
    ['direction' => $direction, 'responsable' => $responsable] = organigrammeChefService();

    $exercice = Exercice::factory()->actif()->create();
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $extrant = Extrant::factory()->forResultat(Resultat::factory()->forObjectif($objectif)->create())->create();

    $this->actingAs($responsable)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $direction->id,
        'nom_activite' => 'Activité de la direction',
        'indicateur_objectivement_verifiable' => 'Indicateur',
        'moyen_verification' => 'Rapport',
        'cout' => 100_000,
        'trimestre_1' => 'on',
    ])->assertSessionHasNoErrors();

    Notification::assertNothingSentTo($responsable);
});

test('le chef de service modifie une activité de son service mais ne la soumet pas', function () {
    ['service' => $service, 'chefService' => $chefService] = organigrammeChefService();
    $activite = Activite::factory()->brouillon()->pourDepartement($service)->saisiePar($chefService)->create();

    $this->actingAs($chefService)->get(route('activites.edit', $activite))->assertOk();
    $this->actingAs($chefService)->post(route('activites.soumettre', $activite))->assertForbidden();
    $this->actingAs($chefService)->get(route('activites.show', $activite))
        ->assertOk()
        ->assertDontSee(route('activites.soumettre', $activite));

    expect($activite->fresh()->statut)->toBe('brouillon');
});

test('le responsable de programme soumet l\'activité saisie par son chef de service', function () {
    Notification::fake();
    ['service' => $service, 'chefService' => $chefService, 'responsable' => $responsable] = organigrammeChefService();
    $dbcgoq = userWithRole('dbcgoq');
    $activite = Activite::factory()->brouillon()->pourDepartement($service)->saisiePar($chefService)->create();

    $this->actingAs($responsable)->post(route('activites.soumettre', $activite))
        ->assertRedirect(route('activites.index'));

    expect($activite->fresh()->statut)->toBe('en_attente');
    Notification::assertSentTo($dbcgoq, ActiviteSoumiseNotification::class);
});

test('le chef de service ne touche pas aux activités d\'une autre entité', function () {
    ['chefService' => $chefService] = organigrammeChefService();
    $activite = Activite::factory()->brouillon()->pourDepartement(Departement::factory()->service()->create())->create();

    $this->actingAs($chefService)->get(route('activites.edit', $activite))->assertForbidden();
    $this->actingAs($chefService)->post(route('activites.soumettre', $activite))->assertForbidden();
});

test('le chef de service ne valide pas ses propres soumissions', function () {
    ['service' => $service, 'chefService' => $chefService] = organigrammeChefService();
    $activite = Activite::factory()->enAttente()->pourDepartement($service)->create();

    $this->actingAs($chefService)->post(route('validations.valider', $activite))->assertForbidden();
    expect($activite->fresh()->statut)->toBe('en_attente');
});

test('le responsable de programme de l\'entité parente valide la soumission du chef de service', function () {
    ['service' => $service, 'responsable' => $responsable] = organigrammeChefService();
    $activite = Activite::factory()->enAttente()->pourDepartement($service)->create();

    $this->actingAs($responsable)->post(route('validations.valider', $activite))->assertRedirect();

    expect($activite->fresh()->statut)->toBe('valide');
});

test('un responsable de programme d\'une autre entité ne peut pas valider', function () {
    ['service' => $service] = organigrammeChefService();
    $autreResponsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->departement()->create()->id]);
    $activite = Activite::factory()->enAttente()->pourDepartement($service)->create();

    $this->actingAs($autreResponsable)->post(route('validations.valider', $activite))->assertForbidden();
    expect($activite->fresh()->statut)->toBe('en_attente');
});

test('le chef d\'un service rattaché à la DG soumet lui-même les activités de son service', function () {
    Notification::fake();
    $dg = Departement::factory()->direction()->create();
    $serviceRattache = Departement::factory()->service()->enfantDe($dg)->create();
    $chefService = userWithRole('chef-service', ['departement_id' => $serviceRattache->id]);
    $activite = Activite::factory()->brouillon()->pourDepartement($serviceRattache)->saisiePar($chefService)->create();

    expect($serviceRattache->estRattacheeDg())->toBeTrue();

    $this->actingAs($chefService)->post(route('activites.soumettre', $activite))
        ->assertRedirect(route('activites.index'));

    expect($activite->fresh()->statut)->toBe('en_attente');
});

test('les services rattachés à la DG sont proposés comme structures intervenantes', function () {
    $dg = Departement::factory()->direction()->create();
    $serviceRattache = Departement::factory()->service()->enfantDe($dg)->create();
    $serviceDeDc = Departement::factory()->service()
        ->enfantDe(Departement::factory()->departement()->enfantDe($dg)->create())->create();

    $ids = Departement::query()->structuresIntervenantes()->pluck('id')->all();

    expect($ids)->toContain($dg->id, $serviceRattache->id)->not->toContain($serviceDeDc->id);
});

test('une activité en brouillon de son entité est supprimable, pas une activité soumise ni celle d\'une autre entité', function (string $role) {
    $departement = Departement::factory()->departement()->create();
    $user = userWithRole($role, ['departement_id' => $departement->id]);

    $brouillon = Activite::factory()->brouillon()->pourDepartement($departement)->saisiePar($user)->create();
    $soumise = Activite::factory()->enAttente()->pourDepartement($departement)->saisiePar($user)->create();
    $ailleurs = Activite::factory()->brouillon()->pourDepartement(Departement::factory()->departement()->create())->create();

    expect($user->can('delete', $brouillon))->toBeTrue()
        ->and($user->can('delete', $soumise))->toBeFalse()
        ->and($user->can('delete', $ailleurs))->toBeFalse();

    // Depuis la liste filtrée : suppression, puis retour sur la même liste.
    $liste = route('activites.index', ['search' => 'atelier']);
    $this->actingAs($user)->from($liste)->delete(route('activites.destroy', $brouillon))
        ->assertRedirect($liste);

    expect(Activite::find($brouillon->id))->toBeNull();
})->with(['responsable-programme', 'chef-service', 'agent-planification']);
