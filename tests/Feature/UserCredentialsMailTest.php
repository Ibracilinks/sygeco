<?php

use App\Models\Exercice;
use App\Models\User;
use App\Notifications\BienvenueUtilisateurNotification;
use App\Notifications\IdentifiantsExerciceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Mail de bienvenue à la création d'un utilisateur
|--------------------------------------------------------------------------
*/

test('la création d\'un utilisateur envoie un mail de bienvenue avec les identifiants', function () {
    Notification::fake();
    $admin = userWithRole('dbcgoq');
    $roleId = Role::where('name', 'agent')->value('id');

    $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Nouvel Agent',
        'email' => 'nouvel.agent@canam.ml',
        'password' => 'Secret123!',
        'password_confirmation' => 'Secret123!',
        'roles' => [$roleId],
    ])->assertRedirect(route('users.index'));

    $cree = User::where('email', 'nouvel.agent@canam.ml')->firstOrFail();

    Notification::assertSentTo(
        $cree,
        BienvenueUtilisateurNotification::class,
        fn ($notification) => $notification->motDePasse === 'Secret123!'
    );
});

test('le mail de bienvenue passe par mail + database mais ne stocke pas le mot de passe in-app', function () {
    $notification = new BienvenueUtilisateurNotification('Secret123!');
    $user = User::factory()->make(['email' => 'x@canam.ml']);

    expect($notification->via($user))->toBe(['mail', 'database']);
    // Le canal database (in-app) ne doit pas contenir le mot de passe en clair.
    expect($notification->toArray($user))->not->toHaveKey('motDePasse');
    expect(json_encode($notification->toArray($user)))->not->toContain('Secret123!');
});

/*
|--------------------------------------------------------------------------
| Régénération du mot de passe à l'ouverture de l'exercice
|--------------------------------------------------------------------------
*/

test('à l\'ouverture de l\'exercice, chaque utilisateur reçoit un nouveau mot de passe', function () {
    Notification::fake();

    $exercice = Exercice::factory()->actif()->create([
        'annee' => 2026,
        'date_ouverture_saisie' => now()->subDay()->toDateString(),
        'date_limite_saisie' => null,
        'ouverture_notifiee_le' => null,
    ]);

    $user = User::factory()->create();
    $ancienHash = $user->password;

    $this->artisan('activites:notifier-saisie')->assertSuccessful();

    // Le mot de passe a été régénéré...
    expect($user->fresh()->password)->not->toBe($ancienHash);
    // ...et les nouveaux accès envoyés.
    Notification::assertSentTo($user, IdentifiantsExerciceNotification::class);
    // L'ouverture est marquée comme notifiée (idempotence).
    expect($exercice->fresh()->ouverture_notifiee_le)->not->toBeNull();
});

test('les administrateurs dbcgoq sont exclus de la régénération du mot de passe', function () {
    Notification::fake();

    Exercice::factory()->actif()->create([
        'annee' => 2026,
        'date_ouverture_saisie' => now()->subDay()->toDateString(),
        'date_limite_saisie' => null,
        'ouverture_notifiee_le' => null,
    ]);

    $admin = userWithRole('dbcgoq');
    $hashAdmin = $admin->password;
    $agent = User::factory()->create();

    $this->artisan('activites:notifier-saisie')->assertSuccessful();

    // L'admin n'est pas touché...
    expect($admin->fresh()->password)->toBe($hashAdmin);
    Notification::assertNotSentTo($admin, IdentifiantsExerciceNotification::class);
    // ...mais les comptes non-admin le sont.
    Notification::assertSentTo($agent, IdentifiantsExerciceNotification::class);
});

test('l\'ouverture ne régénère pas les mots de passe deux fois', function () {
    Notification::fake();

    Exercice::factory()->actif()->create([
        'annee' => 2026,
        'date_ouverture_saisie' => now()->subDay()->toDateString(),
        'date_limite_saisie' => null,
        'ouverture_notifiee_le' => now()->subHour(),
    ]);

    $user = User::factory()->create();
    $hash = $user->password;

    $this->artisan('activites:notifier-saisie')->assertSuccessful();

    expect($user->fresh()->password)->toBe($hash);
    Notification::assertNotSentTo($user, IdentifiantsExerciceNotification::class);
});

test('le mail d\'identifiants d\'exercice ne stocke pas le mot de passe in-app', function () {
    $exercice = Exercice::factory()->make(['annee' => 2026]);
    $notification = new IdentifiantsExerciceNotification($exercice, 'Nouveau123!');
    $user = User::factory()->make();

    expect($notification->via($user))->toBe(['mail', 'database']);
    expect(json_encode($notification->toArray($user)))->not->toContain('Nouveau123!');
});
