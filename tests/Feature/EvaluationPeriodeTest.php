<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Models\User;
use App\Notifications\EvaluationOuverteNotification;
use App\Notifications\MiParcoursOuvertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/**
 * Construit une activité validée (seul statut évaluable) rattachée à un exercice
 * donné, dans un département donné.
 */
function activitePourExercice(Exercice $exercice, Departement $departement): Activite
{
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    return Activite::factory()->valide()->pourExtrant($extrant)->pourDepartement($departement)->create();
}

/*
|--------------------------------------------------------------------------
| Verrouillage de la saisie d'exécution par fenêtre
|--------------------------------------------------------------------------
*/

test('un chef peut renseigner l\'évaluation mi-parcours pendant sa fenêtre', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');

    $exercice = Exercice::factory()->actif()->create([
        'date_debut_mi_parcours' => now()->subDay()->toDateString(),
        'date_fin_mi_parcours' => now()->addDays(5)->toDateString(),
    ]);
    $activite = activitePourExercice($exercice, $dep);

    $this->actingAs($chef)->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
        'statut_execution' => 'realise',
        'observation' => 'Activité terminée',
    ])->assertSessionHas('success');

    expect($activite->evaluation('mi_parcours'))
        ->statut_execution->toBe('realise')
        ->observation->toBe('Activité terminée')
        ->maj_par->toBe($chef->id);

    expect($activite->fresh()->statut_execution)->toBe('realise');
});

test('un chef peut renseigner l\'évaluation hors fenêtre : la saisie reste ouverte en permanence', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');

    // Fenêtre mi-parcours close depuis 15 jours : la saisie doit rester possible.
    $exercice = Exercice::factory()->actif()->create([
        'date_debut_mi_parcours' => now()->subDays(20)->toDateString(),
        'date_fin_mi_parcours' => now()->subDays(15)->toDateString(),
        'date_debut_evaluation' => null,
        'date_fin_evaluation' => null,
    ]);
    $activite = activitePourExercice($exercice, $dep);

    $this->actingAs($chef)->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
        'statut_execution' => 'realise',
    ])->assertSessionHas('success');

    expect($activite->evaluation('mi_parcours')->statut_execution)->toBe('realise');
});

test('le dbcgoq peut renseigner l\'évaluation même hors fenêtre', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create([
        'date_debut_mi_parcours' => null,
        'date_fin_mi_parcours' => null,
    ]);
    $dep = Departement::factory()->create();
    $activite = activitePourExercice($exercice, $dep);

    $this->actingAs($admin)->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
        'statut_execution' => 'en_cours',
    ])->assertSessionHas('success');

    expect($activite->evaluation('mi_parcours')->statut_execution)->toBe('en_cours');
});

test('un chef peut renseigner l\'évaluation de fin d\'année pendant sa fenêtre', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');

    $exercice = Exercice::factory()->actif()->create([
        'date_debut_evaluation' => now()->subDay()->toDateString(),
        'date_fin_evaluation' => now()->addDays(3)->toDateString(),
    ]);
    $activite = activitePourExercice($exercice, $dep);

    $this->actingAs($chef)->post(route('evaluations.enregistrer', [$activite, 'fin-annee']), [
        'statut_execution' => 'realise',
    ])->assertSessionHas('success');

    expect($activite->evaluation('fin_annee')->statut_execution)->toBe('realise');
});

/*
|--------------------------------------------------------------------------
| Commande de notification activites:notifier-suivi
|--------------------------------------------------------------------------
*/

test('la commande notifie les chefs à l\'ouverture du mi-parcours', function () {
    Notification::fake();
    seedRolesAndPermissions();

    $dep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $exercice = Exercice::factory()->actif()->create([
        'date_debut_mi_parcours' => now()->subDay()->toDateString(),
        'date_fin_mi_parcours' => now()->addDays(5)->toDateString(),
    ]);

    $this->artisan('activites:notifier-suivi')->assertSuccessful();

    Notification::assertSentTo($chef, MiParcoursOuvertNotification::class);
    Notification::assertNotSentTo($agent, MiParcoursOuvertNotification::class);
    expect($exercice->fresh()->mi_parcours_notifiee_le)->not->toBeNull();
});

test('la commande n\'envoie pas deux fois la notification mi-parcours', function () {
    Notification::fake();
    seedRolesAndPermissions();

    $chef = User::factory()->create();
    $chef->assignRole('chef');

    Exercice::factory()->actif()->create([
        'date_debut_mi_parcours' => now()->subDay()->toDateString(),
        'date_fin_mi_parcours' => now()->addDays(5)->toDateString(),
        'mi_parcours_notifiee_le' => now()->subHour(),
    ]);

    $this->artisan('activites:notifier-suivi')->assertSuccessful();

    Notification::assertNothingSent();
});

test('la commande notifie l\'ouverture de l\'évaluation', function () {
    Notification::fake();
    seedRolesAndPermissions();

    $chef = User::factory()->create();
    $chef->assignRole('chef');

    $exercice = Exercice::factory()->actif()->create([
        'date_debut_evaluation' => now()->subDay()->toDateString(),
        'date_fin_evaluation' => now()->addDays(3)->toDateString(),
    ]);

    $this->artisan('activites:notifier-suivi')->assertSuccessful();

    Notification::assertSentTo($chef, EvaluationOuverteNotification::class);
    expect($exercice->fresh()->evaluation_notifiee_le)->not->toBeNull();
});

test('la commande ne notifie rien hors fenêtre', function () {
    Notification::fake();
    seedRolesAndPermissions();

    $chef = User::factory()->create();
    $chef->assignRole('chef');

    Exercice::factory()->actif()->create([
        'date_debut_mi_parcours' => now()->addDays(10)->toDateString(),
        'date_fin_mi_parcours' => now()->addDays(20)->toDateString(),
    ]);

    $this->artisan('activites:notifier-suivi')->assertSuccessful();

    Notification::assertNothingSent();
});

test('une activité non validée ne peut pas être évaluée', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $dep = Departement::factory()->create();

    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();
    $activite = Activite::factory()->brouillon()->pourExtrant($extrant)->pourDepartement($dep)->create();

    $this->actingAs($admin)->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
        'statut_execution' => 'realise',
    ])->assertSessionHas('error');

    expect($activite->evaluation('mi_parcours'))->toBeNull();
});

test('seules les activités validées apparaissent dans l\'évaluation', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $dep = Departement::factory()->create();

    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    $validee = Activite::factory()->valide()->pourExtrant($extrant)->pourDepartement($dep)->create();
    $brouillon = Activite::factory()->brouillon()->pourExtrant($extrant)->pourDepartement($dep)->create();
    $enAttente = Activite::factory()->soumis()->pourExtrant($extrant)->pourDepartement($dep)->create();

    $this->actingAs($admin)->get(route('evaluations.index', 'mi-parcours'))
        ->assertOk()
        ->assertSee($validee->nom_activite)
        ->assertDontSee($brouillon->nom_activite)
        ->assertDontSee($enAttente->nom_activite);
});

test('une activité non évaluée n\'est pas comptée comme non réalisée', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $dep = Departement::factory()->create();

    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    $evaluee = Activite::factory()->valide()->pourExtrant($extrant)->pourDepartement($dep)->create();
    Activite::factory()->valide()->pourExtrant($extrant)->pourDepartement($dep)->create(); // jamais évaluée

    $this->actingAs($admin)->post(route('evaluations.enregistrer', [$evaluee, 'mi-parcours']), [
        'statut_execution' => 'realise',
    ])->assertSessionHas('success');

    $summary = $this->actingAs($admin)->get(route('evaluations.index', 'mi-parcours'))
        ->assertOk()
        ->viewData('summary');

    expect($summary)
        ->total->toBe(2)
        ->evaluees->toBe(1)
        ->realise->toBe(1)
        ->non_realise->toBe(0)
        ->non_evaluee->toBe(1)
        ->taux_realisation->toBe(100.0);
});
