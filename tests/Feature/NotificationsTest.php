<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\User;
use App\Notifications\ActiviteRefusee;
use App\Notifications\ActiviteSoumiseNotification;
use App\Notifications\ActiviteValidee;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Déclencheur : soumission d'une activité -> notifie les validateurs
|--------------------------------------------------------------------------
*/

test('soumettre une activité notifie les validateurs dbcgoq', function () {
    Notification::fake();
    seedRolesAndPermissions();

    $dep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef_departement');

    $validateur = User::factory()->create();
    $validateur->assignRole('dbcgoq');

    $activite = Activite::factory()->brouillon()->pourDepartement($dep)->create();

    $this->actingAs($chef)->post(route('activites.soumettre', $activite))
        ->assertRedirect(route('activites.index'));

    Notification::assertSentTo($validateur, ActiviteSoumiseNotification::class);
});

test('un agent ne reçoit pas la notification de soumission', function () {
    Notification::fake();
    seedRolesAndPermissions();

    $dep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef_departement');

    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $activite = Activite::factory()->brouillon()->pourDepartement($dep)->create();

    $this->actingAs($chef)->post(route('activites.soumettre', $activite));

    Notification::assertNotSentTo($agent, ActiviteSoumiseNotification::class);
});

/*
|--------------------------------------------------------------------------
| Canaux : mail + database
|--------------------------------------------------------------------------
*/

test('la notification de soumission passe par mail et database', function () {
    $activite = Activite::factory()->create();
    $notification = new ActiviteSoumiseNotification($activite);

    expect($notification->via(new User()))->toBe(['mail', 'database']);
});

test('ActiviteValidee et ActiviteRefusee passent par mail et database', function () {
    $activite = Activite::factory()->create();

    expect((new ActiviteValidee($activite))->via(new User()))->toBe(['mail', 'database']);
    expect((new ActiviteRefusee($activite, 'motif assez long'))->via(new User()))->toBe(['mail', 'database']);
});

test('le payload database contient un message et une url', function () {
    $activite = Activite::factory()->create();
    $data = (new ActiviteSoumiseNotification($activite))->toArray(new User());

    expect($data)->toHaveKeys(['message', 'url', 'activite_id']);
    expect($data['url'])->toContain('/validations/');
});

/*
|--------------------------------------------------------------------------
| Validation -> notifie l'auteur (in-app inclus)
|--------------------------------------------------------------------------
*/

test('valider via ValidationController écrit une notification en base pour l\'auteur', function () {
    seedRolesAndPermissions();
    $admin = User::factory()->create();
    $admin->assignRole('dbcgoq');
    $auteur = User::factory()->create();
    $activite = Activite::factory()->soumis()->saisiePar($auteur)->create();

    $this->actingAs($admin)->post(route('validations.valider', $activite), [
        'commentaire' => 'Validée',
    ]);

    expect($auteur->fresh()->notifications()->count())->toBe(1);
    expect($auteur->fresh()->unreadNotifications()->count())->toBe(1);
});
