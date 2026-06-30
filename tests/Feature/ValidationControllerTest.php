<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\User;
use App\Notifications\ActiviteRefusee;
use App\Notifications\ActiviteValidee;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('un invité est redirigé vers la connexion', function () {
    $this->get(route('validations.index'))->assertRedirect(route('login'));
});

test('un agent ne peut pas accéder à l\'espace de validation', function () {
    $agent = userWithRole('agent');

    $this->actingAs($agent)->get(route('validations.index'))->assertForbidden();
});

test('le dbcgoq peut accéder à l\'espace de validation', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->get(route('validations.index'))->assertOk();
});

test('le dbcgoq peut valider une activité et notifier le saisisseur', function () {
    Notification::fake();
    $admin = userWithRole('dbcgoq');
    $saisisseur = User::factory()->create();
    $activite = Activite::factory()->soumis()->saisiePar($saisisseur)->create();

    $this->actingAs($admin)->post(route('validations.valider', $activite), [
        'commentaire' => 'Validé après vérification',
    ])->assertRedirect(route('validations.index'));

    expect($activite->fresh()->statut)->toBe('valide');
    Notification::assertSentTo($saisisseur, ActiviteValidee::class);
});

test('valider une activité non soumise renvoie une erreur', function () {
    $admin = userWithRole('dbcgoq');
    $activite = Activite::factory()->brouillon()->create();

    $this->actingAs($admin)
        ->from(route('validations.index'))
        ->post(route('validations.valider', $activite))
        ->assertSessionHas('error');

    expect($activite->fresh()->statut)->toBe('brouillon');
});

test('un chef ne peut pas valider une activité hors de son département', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $autreDep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');
    $activite = Activite::factory()->soumis()->pourDepartement($autreDep)->create();

    $this->actingAs($chef)->post(route('validations.valider', $activite))->assertForbidden();
});

test('un chef valide les activités de ses entités enfants (flux montant)', function () {
    seedRolesAndPermissions();
    $direction = Departement::factory()->direction()->create();
    $service = Departement::factory()->service()->enfantDe($direction)->create();
    $chef = User::factory()->dansDepartement($direction)->create();
    $chef->assignRole('chef');
    $activite = Activite::factory()->soumis()->pourDepartement($service)->create();

    $this->actingAs($chef)->post(route('validations.valider', $activite))
        ->assertRedirect(route('validations.index'));

    expect($activite->fresh()->statut)->toBe('valide');
});

test('un chef ne valide pas les activités de sa propre entité (flux montant)', function () {
    seedRolesAndPermissions();
    $direction = Departement::factory()->direction()->create();
    $chef = User::factory()->dansDepartement($direction)->create();
    $chef->assignRole('chef');
    $activite = Activite::factory()->soumis()->pourDepartement($direction)->create();

    $this->actingAs($chef)->post(route('validations.valider', $activite))->assertForbidden();
});

test('le dbcgoq peut refuser et notifier si demandé', function () {
    Notification::fake();
    $admin = userWithRole('dbcgoq');
    $saisisseur = User::factory()->create();
    $activite = Activite::factory()->soumis()->saisiePar($saisisseur)->create();

    $this->actingAs($admin)->post(route('validations.refuser', $activite), [
        'motif_refus' => 'Le budget proposé est incohérent',
        'notifier_utilisateur' => 'on',
    ])->assertRedirect(route('validations.index'));

    expect($activite->fresh())
        ->statut->toBe('brouillon')
        ->motif_refus->toBe('Le budget proposé est incohérent');
    Notification::assertSentTo($saisisseur, ActiviteRefusee::class);
});

test('refuser sans notifier n\'envoie pas de notification', function () {
    Notification::fake();
    $admin = userWithRole('dbcgoq');
    $saisisseur = User::factory()->create();
    $activite = Activite::factory()->soumis()->saisiePar($saisisseur)->create();

    $this->actingAs($admin)->post(route('validations.refuser', $activite), [
        'motif_refus' => 'Motif suffisamment long pour passer',
    ])->assertRedirect(route('validations.index'));

    Notification::assertNothingSent();
});

test('refuser exige un motif valide', function () {
    $admin = userWithRole('dbcgoq');
    $activite = Activite::factory()->soumis()->create();

    $this->actingAs($admin)
        ->from(route('validations.index'))
        ->post(route('validations.refuser', $activite), ['motif_refus' => 'court'])
        ->assertSessionHasErrors('motif_refus');
});
