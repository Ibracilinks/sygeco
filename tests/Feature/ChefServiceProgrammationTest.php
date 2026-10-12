<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Models\User;
use App\Notifications\ActiviteSoumiseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Programmation par le chef de service
|--------------------------------------------------------------------------
| Le chef de service programme et soumet les activités de son service ; la
| soumission est validée par le responsable de programme de l'entité parente.
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

test('le chef de service modifie et soumet une activité de son service, son responsable est notifié', function () {
    Notification::fake();
    ['service' => $service, 'chefService' => $chefService, 'responsable' => $responsable] = organigrammeChefService();
    $autreResponsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->departement()->create()->id]);

    $activite = Activite::factory()->brouillon()->pourDepartement($service)->create();

    $this->actingAs($chefService)->get(route('activites.edit', $activite))->assertOk();
    $this->actingAs($chefService)->post(route('activites.soumettre', $activite))
        ->assertRedirect(route('activites.index'));

    expect($activite->fresh()->statut)->toBe('en_attente');
    Notification::assertSentTo($responsable, ActiviteSoumiseNotification::class);
    Notification::assertNotSentTo($autreResponsable, ActiviteSoumiseNotification::class);
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
