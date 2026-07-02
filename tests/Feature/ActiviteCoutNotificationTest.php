<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\User;
use App\Notifications\ActiviteCoutModifieNotification;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('modifier le coût notifie le directeur de la Direction Centrale et le chef de service', function () {
    Notification::fake();
    $admin = userWithRole('dbcgoq');

    $directeurDC = User::factory()->create();
    $chefService = User::factory()->create();

    $direction = Departement::factory()->direction()->create();
    $dc = Departement::factory()->departement()->enfantDe($direction)->create(['responsable_id' => $directeurDC->id]);
    $service = Departement::factory()->service()->enfantDe($dc)->create(['responsable_id' => $chefService->id]);

    $activite = Activite::factory()->brouillon()->pourDepartement($service)->avecCout(1_000_000)->create();

    $this->actingAs($admin)->put(route('activites.update', $activite), [
        'extrant_id' => $activite->extrant_id,
        'departement_id' => $service->id,
        'nom_activite' => $activite->nom_activite,
        'indicateur_objectivement_verifiable' => $activite->indicateur_objectivement_verifiable,
        'moyen_verification' => $activite->moyen_verification,
        'cout' => 2_500_000,
    ])->assertRedirect(route('activites.index'));

    Notification::assertSentTo($directeurDC, ActiviteCoutModifieNotification::class);
    Notification::assertSentTo($chefService, ActiviteCoutModifieNotification::class);
});

test('modifier une activité sans changer le coût ne notifie personne', function () {
    Notification::fake();
    $admin = userWithRole('dbcgoq');
    $chefService = User::factory()->create();

    $direction = Departement::factory()->direction()->create();
    $service = Departement::factory()->service()->enfantDe($direction)->create(['responsable_id' => $chefService->id]);
    $activite = Activite::factory()->brouillon()->pourDepartement($service)->avecCout(1_000_000)->create();

    $this->actingAs($admin)->put(route('activites.update', $activite), [
        'extrant_id' => $activite->extrant_id,
        'departement_id' => $service->id,
        'nom_activite' => 'Nom modifié sans toucher au budget',
        'indicateur_objectivement_verifiable' => $activite->indicateur_objectivement_verifiable,
        'moyen_verification' => $activite->moyen_verification,
        'cout' => 1_000_000,
    ])->assertRedirect(route('activites.index'));

    Notification::assertNothingSent();
});
