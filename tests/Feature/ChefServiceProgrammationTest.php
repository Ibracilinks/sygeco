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
use App\Support\ActiveExercice;
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

test('une activité en brouillon de son entité est supprimable, une activité soumise seulement par le responsable, jamais celle d\'une autre entité', function (string $role) {
    $departement = Departement::factory()->departement()->create();
    $user = userWithRole($role, ['departement_id' => $departement->id]);

    $brouillon = Activite::factory()->brouillon()->pourDepartement($departement)->saisiePar($user)->create();
    $soumise = Activite::factory()->enAttente()->pourDepartement($departement)->saisiePar($user)->create();
    $ailleurs = Activite::factory()->brouillon()->pourDepartement(Departement::factory()->departement()->create())->create();

    expect($user->can('delete', $brouillon))->toBeTrue()
        // Le responsable de programme garde la main jusqu'à l'arbitrage ; les autres
        // profils seulement jusqu'à la soumission.
        ->and($user->can('delete', $soumise))->toBe($role === 'responsable-programme')
        ->and($user->can('delete', $ailleurs))->toBeFalse();

    // Depuis la liste filtrée : suppression, puis retour sur la même liste.
    $liste = route('activites.index', ['search' => 'atelier']);
    $this->actingAs($user)->from($liste)->delete(route('activites.destroy', $brouillon))
        ->assertRedirect($liste);

    expect(Activite::find($brouillon->id))->toBeNull();
})->with(['responsable-programme', 'chef-service', 'agent-planification']);

test('à la soumission, l\'activité d\'un service remonte à l\'entité qui le chapeaute', function (string $typeParent) {
    $dg = Departement::factory()->direction()->create();
    $entite = Departement::factory()->state(['type' => $typeParent])->enfantDe($dg)->create(['nom' => 'ENTITE_PARENTE']);
    $service = Departement::factory()->service()->enfantDe($entite)->create(['nom' => 'SERVICE_ORIGINE']);
    $responsable = userWithRole('responsable-programme', ['departement_id' => $entite->id]);
    $activite = Activite::factory()->brouillon()->pourDepartement($service)->create();

    $this->actingAs($responsable)->post(route('activites.soumettre', $activite))->assertRedirect();

    $activite->refresh();
    expect($activite->statut)->toBe('en_attente')
        ->and($activite->departement_id)->toBe($entite->id)
        // Le service d'origine n'apparaît plus : seul l'historique en garde la trace.
        ->and($activite->departements->pluck('id')->all())->not->toContain($service->id)
        ->and($activite->validationHistoriques()->latest('id')->value('commentaire'))
        ->toBe('Structure : SERVICE_ORIGINE → ENTITE_PARENTE');
})->with([Departement::TYPE_DEPARTEMENT, Departement::TYPE_AGENCE_COMPTABLE]);

test('à la soumission, une activité rattachée à la DG ou d\'une entité de premier niveau garde sa structure', function () {
    $dg = Departement::factory()->direction()->create();
    $serviceRattache = Departement::factory()->service()->enfantDe($dg)->create();
    $directionCentrale = Departement::factory()->departement()->enfantDe($dg)->create();

    foreach ([$serviceRattache, $directionCentrale] as $structure) {
        $activite = Activite::factory()->brouillon()->pourDepartement($structure)->create();

        $this->actingAs(userWithRole('dbcgoq'))->post(route('activites.soumettre', $activite))->assertRedirect();

        expect($activite->fresh()->departement_id)->toBe($structure->id)
            ->and($activite->fresh()->departements)->toBeEmpty();
    }
});

test('un service sous un autre service remonte au premier parent qui n\'est ni un service ni la DG', function () {
    $dg = Departement::factory()->direction()->create();
    $dc = Departement::factory()->departement()->enfantDe($dg)->create();
    $service = Departement::factory()->service()->enfantDe($dc)->create();
    $sousService = Departement::factory()->service()->enfantDe($service)->create();

    expect($sousService->entiteDeSoumission()?->is($dc))->toBeTrue()
        ->and($dc->entiteDeSoumission())->toBeNull();
});

test('le responsable de programme modifie et supprime les activités de ses sous-entités jusqu\'à l\'arbitrage', function () {
    ['direction' => $direction, 'service' => $service, 'responsable' => $responsable] = organigrammeChefService();
    $ailleurs = Departement::factory()->departement()->create();

    $brouillonService = Activite::factory()->brouillon()->pourDepartement($service)->create();
    $enAttente = Activite::factory()->enAttente()->pourDepartement($direction)->create();
    $rejetee = Activite::factory()->state(['statut' => 'rejete'])->pourDepartement($service)->create();
    $validee = Activite::factory()->valide()->pourDepartement($direction)->create();
    $horsPerimetre = Activite::factory()->brouillon()->pourDepartement($ailleurs)->create();

    foreach ([$brouillonService, $enAttente, $rejetee] as $activite) {
        expect($responsable->can('update', $activite))->toBeTrue()
            ->and($responsable->can('delete', $activite))->toBeTrue();
    }

    // Après l'arbitrage, ou hors de son périmètre : plus la main.
    foreach ([$validee, $horsPerimetre] as $activite) {
        expect($responsable->can('update', $activite))->toBeFalse()
            ->and($responsable->can('delete', $activite))->toBeFalse();
    }

    // La liste lui propose les actions sur l'activité en attente d'arbitrage.
    // Année hors de la plage des fabriques (2020-2030) : pas de collision.
    $exercice = Exercice::factory()->actif()->create(['annee' => 2049, 'date_debut' => '2049-01-01', 'date_fin' => '2049-12-31']);
    $enAttente->update(['exercice_id' => $exercice->id]);
    $this->actingAs($responsable)->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))->assertOk()
        ->assertSee(route('activites.edit', $enAttente), false)
        ->assertSee(route('activites.destroy', $enAttente), false);
});

test('une activité en attente supprimée avant arbitrage laisse une trace dans l\'historique', function () {
    ['direction' => $direction, 'responsable' => $responsable] = organigrammeChefService();
    $activite = Activite::factory()->enAttente()->pourDepartement($direction)->create();

    $this->actingAs($responsable)->delete(route('activites.destroy', $activite))->assertRedirect();

    expect(Activite::find($activite->id))->toBeNull()
        ->and($activite->validationHistoriques()->where('action', 'suppression')->value('commentaire'))
        ->toBe('Supprimée avant arbitrage par '.$responsable->name.'.');
});
