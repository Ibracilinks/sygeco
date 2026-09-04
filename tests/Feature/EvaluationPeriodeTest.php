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
use App\Support\ActiveExercice;
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
        'valeur_indicateur' => 1,
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
        'observation' => 'Observation de contrôle',
        'valeur_indicateur' => 1,
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
        'observation' => 'Observation de contrôle',
        'valeur_indicateur' => 1,
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
        'observation' => 'Observation de contrôle',
        'valeur_indicateur' => 1,
    ])->assertSessionHas('success');

    expect($activite->evaluation('fin_annee')->statut_execution)->toBe('realise');
});

test('la cellule suivi évaluation voit et renseigne toutes les activités en évaluation', function () {
    seedRolesAndPermissions();

    $departementSuivi = Departement::factory()->create();
    $autreDepartement = Departement::factory()->create();
    $user = User::factory()->dansDepartement($departementSuivi)->create();
    $user->assignRole('suivi-evaluation');

    $exercice = Exercice::factory()->actif()->create();
    $activiteHorsPerimetre = activitePourExercice($exercice, $autreDepartement);
    $activiteHorsPerimetre->update([
        'trimestre_1' => 'oui',
        'trimestre_2' => 'non',
        'trimestre_3' => 'oui',
        'trimestre_4' => 'non',
    ]);

    $this->actingAs($user)->get(route('evaluations.index', 'mi-parcours'))
        ->assertOk()
        ->assertSee($activiteHorsPerimetre->nom_activite);

    $this->actingAs($user)->post(route('evaluations.enregistrer', [$activiteHorsPerimetre, 'fin-annee']), [
        'statut_execution' => 'en_cours',
        'observation' => 'Suivi transversal',
        'valeur_indicateur' => 1,
    ])->assertSessionHas('success');

    expect($activiteHorsPerimetre->fresh()->evaluation('fin_annee'))
        ->statut_execution->toBe('en_cours')
        ->observation->toBe('Suivi transversal')
        ->maj_par->toBe($user->id);
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
        'observation' => 'Observation de contrôle',
        'valeur_indicateur' => 1,
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

    $validee = Activite::factory()->valide()->pourExtrant($extrant)->pourDepartement($dep)->create(['trimestre_1' => 'oui', 'trimestre_2' => 'oui', 'trimestre_3' => 'non', 'trimestre_4' => 'non']);
    $brouillon = Activite::factory()->brouillon()->pourExtrant($extrant)->pourDepartement($dep)->create(['trimestre_1' => 'oui', 'trimestre_2' => 'oui', 'trimestre_3' => 'non', 'trimestre_4' => 'non']);
    $enAttente = Activite::factory()->soumis()->pourExtrant($extrant)->pourDepartement($dep)->create(['trimestre_1' => 'oui', 'trimestre_2' => 'oui', 'trimestre_3' => 'non', 'trimestre_4' => 'non']);

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

    $evaluee = Activite::factory()->valide()->pourExtrant($extrant)->pourDepartement($dep)->create(['trimestre_1' => 'oui', 'trimestre_2' => 'oui', 'trimestre_3' => 'non', 'trimestre_4' => 'non']);
    Activite::factory()->valide()->pourExtrant($extrant)->pourDepartement($dep)->create(['trimestre_1' => 'oui', 'trimestre_2' => 'oui', 'trimestre_3' => 'non', 'trimestre_4' => 'non']); // jamais évaluée

    $this->actingAs($admin)->post(route('evaluations.enregistrer', [$evaluee, 'mi-parcours']), [
        'statut_execution' => 'realise',
        'observation' => 'Observation de contrôle',
        'valeur_indicateur' => 1,
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

test("tous les champs de la fiche d'évaluation sont obligatoires", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $activite = Activite::factory()->pourExercice($exercice)->create(['statut' => 'valide']);

    // Seul l'état d'exécution est transmis : les trois autres champs doivent bloquer.
    $this->actingAs($admin)
        ->from(route('evaluations.index', 'mi-parcours'))
        ->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
            'statut_execution' => 'realise',
        ])
        ->assertSessionHasErrors(['observation', 'valeur_indicateur']);

    expect($activite->evaluations()->count())->toBe(0);
});

test("la fiche d'évaluation complète est acceptée", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $activite = Activite::factory()->pourExercice($exercice)->create(['statut' => 'valide']);

    $this->actingAs($admin)
        ->post(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
            'statut_execution' => 'realise',
            'observation' => 'Activité menée à son terme.',
            'valeur_indicateur' => 12,
        ])
        ->assertSessionHasNoErrors();

    expect($activite->evaluations()->count())->toBe(1);
});

test('la saisie AJAX renvoie les erreurs en JSON, sans rechargement', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $activite = Activite::factory()->pourExercice($exercice)->create(['statut' => 'valide']);

    $this->actingAs($admin)
        ->postJson(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
            'statut_execution' => 'realise',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['observation', 'valeur_indicateur']);
});

test('la saisie AJAX renvoie la ligne rafraîchie', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $activite = Activite::factory()->pourExercice($exercice)->create(['statut' => 'valide', 'cout' => 1000000]);

    $reponse = $this->actingAs($admin)
        ->postJson(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
            'statut_execution' => 'realise',
            'observation' => 'Terminée dans les délais.',
            'valeur_indicateur' => 7.5,
        ])
        ->assertOk()
        ->assertJsonStructure(['message', 'ligne' => ['badge', 'maj', 'observation', 'montant', 'valeur_indicateur']]);

    // Le badge est rendu côté serveur pour ne pas dupliquer ses classes en JavaScript.
    expect($reponse->json('ligne.badge'))->toContain('Réalisé')
        ->and($reponse->json('ligne.observation'))->toBe('Terminée dans les délais.')
        ->and($reponse->json('ligne.montant'))->toBe('—');
});

test('un budget déjà enregistré survit à une nouvelle saisie', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $activite = Activite::factory()->pourExercice($exercice)->create(['statut' => 'valide', 'cout' => 500000]);

    // Montant hérité d'avant le retrait du champ : la saisie ne doit pas l'effacer.
    $activite->evaluations()->create([
        'periode' => 'mi_parcours',
        'statut_execution' => 'en_cours',
        'montant_utilise' => 800000,
    ]);

    $this->actingAs($admin)
        ->postJson(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
            'statut_execution' => 'en_cours',
            'observation' => 'Coût supérieur au prévisionnel.',
            'valeur_indicateur' => 2,
        ])
        ->assertOk()
        ->assertJsonPath('ligne.montant', '800 000 FCFA')
        ->assertJsonPath('ligne.ecart_depassement', true)
        ->assertJsonPath('ligne.ecart', '(dépassement 300 000)');
});

test('le mi-parcours ne liste que les activités programmées sur T1 ou T2', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $dep = Departement::factory()->create();

    $creer = fn (array $trimestres, string $nom) => Activite::factory()
        ->pourExercice($exercice)->pourDepartement($dep)
        ->create($trimestres + ['statut' => 'valide', 'nom_activite' => $nom]);

    $creer(['trimestre_1' => 'oui', 'trimestre_2' => 'non', 'trimestre_3' => 'non', 'trimestre_4' => 'non'], 'ACTT1');
    $creer(['trimestre_1' => 'non', 'trimestre_2' => 'oui', 'trimestre_3' => 'non', 'trimestre_4' => 'non'], 'ACTT2');
    $creer(['trimestre_1' => 'non', 'trimestre_2' => 'non', 'trimestre_3' => 'oui', 'trimestre_4' => 'oui'], 'ACTT3T4');

    $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('evaluations.index', 'mi-parcours'))
        ->assertOk()
        ->assertSee('ACTT1')
        ->assertSee('ACTT2')
        ->assertDontSee('ACTT3T4');
});

test("la fin d'année liste tout le chronogramme", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $dep = Departement::factory()->create();

    Activite::factory()->pourExercice($exercice)->pourDepartement($dep)->create([
        'statut' => 'valide', 'nom_activite' => 'ACTT4SEUL',
        'trimestre_1' => 'non', 'trimestre_2' => 'non', 'trimestre_3' => 'non', 'trimestre_4' => 'oui',
    ]);

    $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('evaluations.index', 'fin-annee'))
        ->assertOk()
        ->assertSee('ACTT4SEUL');
});

test("la valeur de l'indicateur accepte du texte", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $activite = Activite::factory()->pourExercice($exercice)->create(['statut' => 'valide']);

    $this->actingAs($admin)
        ->postJson(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
            'statut_execution' => 'realise',
            'observation' => 'Indicateur qualitatif.',
            'valeur_indicateur' => '3 sur 5 établissements conventionnés',
        ])
        ->assertOk()
        ->assertJsonPath('ligne.valeur_indicateur', '3 sur 5 établissements conventionnés');

    expect($activite->fresh()->valeur_indicateur)->toBe('3 sur 5 établissements conventionnés');
});

test("un nombre envoye pour l'indicateur est conserve tel quel", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $activite = Activite::factory()->pourExercice($exercice)->create(['statut' => 'valide']);

    // Un appelant peut poster un entier : il est normalise en chaine, sans arrondi
    // ni formatage decimal comme le faisait l'ancienne colonne.
    $this->actingAs($admin)
        ->postJson(route('evaluations.enregistrer', [$activite, 'mi-parcours']), [
            'statut_execution' => 'realise',
            'observation' => 'Indicateur chiffre.',
            'valeur_indicateur' => 12,
        ])
        ->assertOk()
        ->assertJsonPath('ligne.valeur_indicateur', '12');
});
