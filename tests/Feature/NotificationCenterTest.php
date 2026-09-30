<?php

use App\Models\Activite;
use App\Models\User;
use App\Notifications\ActiviteValidee;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Crée une notification en base pour un utilisateur donné.
 */
function notifie(User $user): void
{
    $activite = Activite::factory()->valide()->create();
    $user->notify(new ActiviteValidee($activite, 'Bravo'));
}

test('un invité est redirigé vers la connexion', function () {
    $this->get(route('notifications.index'))->assertRedirect(route('login'));
});

test('un utilisateur voit sa page de notifications', function () {
    $user = User::factory()->create();
    notifie($user);

    $this->actingAs($user)->get(route('notifications.index'))->assertOk();
});

test('ouvrir une notification la marque comme lue et redirige vers sa cible', function () {
    $user = User::factory()->create();
    notifie($user);
    $notification = $user->notifications()->first();

    expect($notification->read_at)->toBeNull();

    $this->actingAs($user)
        ->get(route('notifications.read', $notification->id))
        ->assertRedirect($notification->data['url']);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('tout marquer comme lu vide les notifications non lues', function () {
    $user = User::factory()->create();
    notifie($user);
    notifie($user);

    expect($user->unreadNotifications()->count())->toBe(2);

    $this->actingAs($user)
        ->from(route('notifications.index'))
        ->post(route('notifications.read-all'))
        ->assertRedirect(route('notifications.index'));

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('supprimer une notification la retire de la base', function () {
    $user = User::factory()->create();
    notifie($user);
    $notification = $user->notifications()->first();

    $this->actingAs($user)
        ->from(route('notifications.index'))
        ->delete(route('notifications.destroy', $notification->id))
        ->assertRedirect(route('notifications.index'));

    expect($user->fresh()->notifications()->count())->toBe(0);
});

test('un utilisateur ne peut pas ouvrir la notification d\'un autre', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    notifie($a);
    $notification = $a->notifications()->first();

    $this->actingAs($b)
        ->get(route('notifications.read', $notification->id))
        ->assertNotFound();
});
