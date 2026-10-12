<?php

use App\Models\Departement;
use App\Models\Exercice;
use App\Models\User;
use App\Notifications\OuvertureSaisieActivitesNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Annonce de l'ouverture de la saisie depuis la fiche exercice
|--------------------------------------------------------------------------
*/

function exerciceSaisieOuverte(array $attributs = []): Exercice
{
    return Exercice::factory()->actif()->create($attributs + [
        'annee' => 2026,
        'date_debut' => now()->startOfYear(),
        'date_fin' => now()->endOfYear(),
        'date_ouverture_saisie' => now()->subDay(),
        'date_limite_saisie' => now()->addDays(15),
    ]);
}

test('le dbcgoq annonce l\'ouverture de la saisie à tous les utilisateurs', function () {
    Notification::fake();
    $exercice = exerciceSaisieOuverte();
    $admin = userWithRole('dbcgoq');
    $responsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->create()->id]);
    $chargeMissions = userWithRole('service-budget');

    $this->actingAs($admin)->get(route('exercices.show', $exercice))
        ->assertOk()
        ->assertSee('Envoyer le mail à tous');

    $this->actingAs($admin)->post(route('exercices.annoncer-ouverture', $exercice))
        ->assertRedirect(route('exercices.show', $exercice))
        ->assertSessionHas('success', "Annonce de l'ouverture de la saisie envoyée à 3 utilisateur(s).");

    Notification::assertSentTo([$admin, $responsable, $chargeMissions], OuvertureSaisieActivitesNotification::class);

    $exercice->refresh();
    expect($exercice->ouverture_annoncee_le)->not->toBeNull();
    expect($exercice->ouverture_annoncee_destinataires)->toBe(3);
    // L'envoi automatique des identifiants n'est pas court-circuité.
    expect($exercice->ouverture_notifiee_le)->toBeNull();
});

test('le lien de l\'annonce respecte les accès de chaque profil', function () {
    $exercice = exerciceSaisieOuverte();

    $lien = fn (User $user) => (new OuvertureSaisieActivitesNotification($exercice))->toArray($user)['url'];

    expect($lien(userWithRole('dbcgoq')))->toBe(route('exercices.show', $exercice));
    expect($lien(userWithRole('chef-service')))->toBe(route('activites.index'));
    expect($lien(userWithRole('service-budget')))->toBe(route('dashboard'));
});

test('l\'annonce est refusée tant que la saisie n\'est pas ouverte', function (array $dates) {
    Notification::fake();
    $exercice = exerciceSaisieOuverte($dates);
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->get(route('exercices.show', $exercice))
        ->assertDontSee('Envoyer le mail à tous');

    $this->actingAs($admin)->post(route('exercices.annoncer-ouverture', $exercice))
        ->assertSessionHas('error');

    Notification::assertNothingSent();
    expect($exercice->fresh()->ouverture_annoncee_le)->toBeNull();
})->with([
    'ouverture non définie' => [['date_ouverture_saisie' => null]],
    'ouverture à venir' => [['date_ouverture_saisie' => now()->addDays(3)]],
    'saisie close' => [['date_ouverture_saisie' => now()->subDays(30), 'date_limite_saisie' => now()->subDay()]],
]);

test('seuls les gestionnaires d\'exercices peuvent annoncer l\'ouverture', function () {
    Notification::fake();
    $exercice = exerciceSaisieOuverte();
    $responsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($responsable)->post(route('exercices.annoncer-ouverture', $exercice))->assertForbidden();

    Notification::assertNothingSent();
});
