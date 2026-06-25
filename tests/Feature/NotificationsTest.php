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

test('la soumission notifie les chefs du même département, pas ceux des autres', function () {
    Notification::fake();
    seedRolesAndPermissions();

    $dep = Departement::factory()->create();
    $autreDep = Departement::factory()->create();

    $chefAuteur = User::factory()->dansDepartement($dep)->create();
    $chefAuteur->assignRole('chef_departement');
    $coChef = User::factory()->dansDepartement($dep)->create();
    $coChef->assignRole('chef_departement');
    $chefAutreDep = User::factory()->dansDepartement($autreDep)->create();
    $chefAutreDep->assignRole('chef_departement');

    $activite = Activite::factory()->brouillon()->pourDepartement($dep)->create();

    $this->actingAs($chefAuteur)->post(route('activites.soumettre', $activite))
        ->assertRedirect(route('activites.index'));

    // Le co-chef du département reçoit la notification...
    Notification::assertSentTo($coChef, ActiviteSoumiseNotification::class);
    // ...mais ni l'auteur de la soumission, ni le chef d'un autre département.
    Notification::assertNotSentTo($chefAuteur, ActiviteSoumiseNotification::class);
    Notification::assertNotSentTo($chefAutreDep, ActiviteSoumiseNotification::class);
});

test('si le département n\'a pas d\'autre chef, le repli notifie les validateurs dbcgoq', function () {
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

    $agent = User::factory()->dansDepartement($dep)->create();
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

test('le payload database contient un message et une url vers l\'entité', function () {
    seedRolesAndPermissions();
    $activite = Activite::factory()->create();

    // Un chef ouvre la fiche de l'activité...
    $chef = User::factory()->create();
    $chef->assignRole('chef_departement');
    $dataChef = (new ActiviteSoumiseNotification($activite))->toArray($chef);

    expect($dataChef)->toHaveKeys(['message', 'url', 'activite_id']);
    expect($dataChef['url'])->toContain('/activites/'.$activite->id);

    // ...le DBCGOQ ouvre l'écran de validation de cette même activité.
    $admin = User::factory()->create();
    $admin->assignRole('dbcgoq');
    $dataAdmin = (new ActiviteSoumiseNotification($activite))->toArray($admin);

    expect($dataAdmin['url'])->toContain('/validations/'.$activite->id);
});

test('le lien de soumission reçu par le chef pointe vers une page accessible (pas 403)', function () {
    seedRolesAndPermissions();
    $dep = \App\Models\Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef_departement');
    $activite = Activite::factory()->soumis()->pourDepartement($dep)->create();

    $url = (new ActiviteSoumiseNotification($activite))->toArray($chef)['url'];

    // Le chef doit pouvoir réellement ouvrir le lien de la notification.
    $this->actingAs($chef)->get($url)->assertOk();
});

test('le lien d\'arbitrage pointe vers l\'activité concernée', function () {
    seedRolesAndPermissions();
    $dep = \App\Models\Departement::factory()->create();
    $auteur = User::factory()->dansDepartement($dep)->create();
    $auteur->assignRole('chef_departement');
    $activite = Activite::factory()->pourDepartement($dep)->create();

    // Cas « modifiée » : lien vers la fiche de l'activité (accessible à l'auteur).
    $modif = (new \App\Notifications\ActiviteArbitrageNotification('modifiee', $activite->nom_activite, 'motif', null, $activite))->toArray($auteur);
    expect($modif['url'])->toContain('/activites/'.$activite->id);

    // Cas « supprimée » : l'entité n'existe plus -> repli sur la liste.
    $suppr = (new \App\Notifications\ActiviteArbitrageNotification('supprimee', 'Nom snapshot', 'motif', null, null))->toArray($auteur);
    expect($suppr['url'])->toContain('/activites');
    expect($suppr['url'])->not->toContain('/activites/');
});

test('le lien des notifications d\'exercice est adapté au rôle', function () {
    seedRolesAndPermissions();
    $exercice = \App\Models\Exercice::factory()->create(['annee' => 2026]);

    $admin = User::factory()->create();
    $admin->assignRole('dbcgoq');
    $chef = User::factory()->create();
    $chef->assignRole('chef_departement');

    $notif = new \App\Notifications\MiParcoursOuvertNotification($exercice);

    expect($notif->toArray($admin)['url'])->toContain('/exercices/'.$exercice->id);
    expect($notif->toArray($chef)['url'])->toContain('/activites/suivi');
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
