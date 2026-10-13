<?php

use App\Models\User;
use App\Notifications\MotDePasseReinitialiseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Mot de passe provisoire
|--------------------------------------------------------------------------
| Création et réinitialisation envoient un mot de passe provisoire ; tant qu'il
| n'est pas remplacé, l'utilisateur est renvoyé vers la page de sécurité.
*/

test('les formulaires de création et de modification n\'ont plus de champ mot de passe', function () {
    $admin = userWithRole('dbcgoq');
    $user = User::factory()->create();

    $this->actingAs($admin)->get(route('users.create'))->assertOk()->assertDontSee('name="password"', false);
    $this->actingAs($admin)->get(route('users.edit', $user))->assertOk()->assertDontSee('name="password"', false);
});

test('la modification d\'un compte ignore un mot de passe envoyé', function () {
    $admin = userWithRole('dbcgoq');
    $user = User::factory()->create();
    $hash = $user->password;

    $this->actingAs($admin)->put(route('users.update', $user), [
        'name' => 'Nom modifié',
        'email' => $user->email,
        'password' => 'Pirate123!',
        'password_confirmation' => 'Pirate123!',
        'roles' => [Role::where('name', 'chef-service')->value('id')],
    ])->assertRedirect();

    expect($user->fresh()->name)->toBe('Nom modifié')
        ->and($user->fresh()->password)->toBe($hash);
});

test('la réinitialisation envoie un mot de passe provisoire et invalide l\'ancien', function () {
    Notification::fake();
    $admin = userWithRole('dbcgoq');
    $user = User::factory()->create(['password' => 'Ancien123!']);

    $this->actingAs($admin)->post(route('users.reinitialiser-mot-de-passe', $user))
        ->assertRedirect(route('users.show', $user))
        ->assertSessionHas('success');

    $user->refresh();
    expect($user->mot_de_passe_provisoire)->toBeTrue()
        ->and(Hash::check('Ancien123!', $user->password))->toBeFalse();
    Notification::assertSentTo($user, MotDePasseReinitialiseNotification::class,
        fn ($n) => Hash::check($n->motDePasse, $user->password));
});

test('la réinitialisation est réservée aux administrateurs', function () {
    $responsable = userWithRole('responsable-programme');
    $user = User::factory()->create();

    $this->actingAs($responsable)->post(route('users.reinitialiser-mot-de-passe', $user))->assertForbidden();
    expect($user->fresh()->mot_de_passe_provisoire)->toBeFalse();
});

test('la fiche utilisateur propose la réinitialisation', function () {
    $admin = userWithRole('dbcgoq');
    $user = User::factory()->create();

    $this->actingAs($admin)->get(route('users.show', $user))
        ->assertOk()
        ->assertSee(route('users.reinitialiser-mot-de-passe', $user));
});

test('le mail de réinitialisation ne stocke pas le mot de passe in-app', function () {
    $notification = new MotDePasseReinitialiseNotification('Provisoire123');
    $user = User::factory()->make();

    expect($notification->via($user))->toBe(['mail', 'database'])
        ->and(json_encode($notification->toArray($user)))->not->toContain('Provisoire123');
});

test('avec un mot de passe provisoire, toute page renvoie vers la page de sécurité', function () {
    $user = userWithRole('responsable-programme');
    $user->forceFill(['mot_de_passe_provisoire' => true])->save();

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('security.edit'));
    $this->actingAs($user)->get(route('activites.index'))->assertRedirect(route('security.edit'));
    // La page de sécurité demande d'abord de confirmer le mot de passe (provisoire), puis s'ouvre.
    $this->actingAs($user)->get(route('password.confirm'))->assertOk();
    $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))->assertOk()->assertSee('Votre mot de passe est provisoire.');
    $this->actingAs($user)->post(route('logout'))->assertRedirect();
});

test('changer le mot de passe provisoire rouvre l\'application', function () {
    $user = userWithRole('responsable-programme', ['password' => 'Provisoire123']);
    $user->forceFill(['mot_de_passe_provisoire' => true])->save();

    $this->actingAs($user);
    Livewire::test('pages::settings.security')
        ->set('current_password', 'Provisoire123')
        ->set('password', 'Personnel-2026!')
        ->set('password_confirmation', 'Personnel-2026!')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->mot_de_passe_provisoire)->toBeFalse()
        ->and(Hash::check('Personnel-2026!', $user->password))->toBeTrue();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});
