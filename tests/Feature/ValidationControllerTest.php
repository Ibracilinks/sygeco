<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\User;
use App\Notifications\ActiviteRefusee;
use App\Notifications\ActiviteValidee;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/**
 * L'espace d'arbitrage ne porte que sur l'exercice en cours. Sans exercice épinglé,
 * `ActiveExercice::id()` retomberait sur les exercices d'années aléatoires créés au
 * passage par les factories, et le scénario ne serait pas reproductible.
 */
function exerciceArbitre(): Exercice
{
    $exercice = Exercice::factory()->actif()->create();
    ActiveExercice::set($exercice->id);

    return $exercice;
}

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
    ])->assertRedirect(route('validations.entite', $activite->departement_id));

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
        ->assertRedirect(route('validations.entite', $service));

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
    ])->assertRedirect(route('validations.entite', $activite->departement_id));

    expect($activite->fresh())
        ->statut->toBe('rejete')
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
    ])->assertRedirect(route('validations.entite', $activite->departement_id));

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

/*
|--------------------------------------------------------------------------
| Concentration de l'arbitrage au niveau de la Direction Centrale
|--------------------------------------------------------------------------
*/

test("l'index concentre les activités des services dans leur Direction Centrale", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = exerciceArbitre();
    $dc = Departement::factory()->departement()->create(['nom' => 'DAGRH']);
    $service = Departement::factory()->service()->enfantDe($dc)->create(['nom' => 'SJC']);

    Activite::factory()->pourExercice($exercice)->pourDepartement($service)->create(['statut' => 'en_attente', 'cout' => 2000000]);
    Activite::factory()->pourExercice($exercice)->pourDepartement($dc)->create(['statut' => 'en_attente', 'cout' => 3000000]);

    $response = $this->actingAs($admin)->get(route('validations.index'))->assertOk();

    // Le service n'apparaît nulle part : ni carte, ni lien, ni mention. Ses activités
    // et son budget sont comptés dans la carte de sa Direction Centrale.
    $response->assertSee('DAGRH');
    $response->assertDontSee('SJC');
    $response->assertSee(route('validations.entite', $dc));
    $response->assertDontSee(route('validations.entite', $service));
    $response->assertSee('2 act.');
    $response->assertSee('5 000 000');
});

test('un service rattaché à une Direction remonte lui aussi dans son entité', function () {
    $admin = userWithRole('dbcgoq');

    // Cas réel : un service peut dépendre d'une Direction et non d'une Direction Centrale.
    $exercice = exerciceArbitre();
    $direction = Departement::factory()->direction()->create(['nom' => 'DAGRH']);
    $service = Departement::factory()->service()->enfantDe($direction)->create(['nom' => 'SJC']);

    Activite::factory()->pourExercice($exercice)->pourDepartement($service)->create(['statut' => 'en_attente']);

    $this->actingAs($admin)->get(route('validations.index'))
        ->assertOk()
        ->assertSee('DAGRH')
        ->assertDontSee('SJC')
        ->assertSee(route('validations.entite', $direction))
        ->assertDontSee(route('validations.entite', $service));
});

test('un service à deux niveaux de profondeur remonte à son entité non-service', function () {
    $admin = userWithRole('dbcgoq');

    $exercice = exerciceArbitre();
    $direction = Departement::factory()->direction()->create(['nom' => 'Direction Generale']);
    $dc = Departement::factory()->departement()->enfantDe($direction)->create(['nom' => 'DBCGOQ']);
    $service = Departement::factory()->service()->enfantDe($dc)->create(['nom' => 'Service Qualite']);

    Activite::factory()->pourExercice($exercice)->pourDepartement($service)->create(['statut' => 'en_attente']);

    // Le rattachement vise le plus proche ancêtre non-service, pas la racine.
    $this->actingAs($admin)->get(route('validations.index'))
        ->assertOk()
        ->assertSee('DBCGOQ')
        ->assertDontSee('Service Qualite')
        ->assertSee(route('validations.entite', $dc))
        ->assertDontSee(route('validations.entite', $service));
});

test("la page d'une Direction Centrale liste les activités de ses services", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = exerciceArbitre();
    $dc = Departement::factory()->departement()->create();
    $service = Departement::factory()->service()->enfantDe($dc)->create(['nom' => 'Service Marchés']);

    $activite = Activite::factory()->pourExercice($exercice)->pourDepartement($service)->create([
        'statut' => 'en_attente',
        'nom_activite' => 'Activité portée par le service',
    ]);

    $this->actingAs($admin)->get(route('validations.entite', $dc))
        ->assertOk()
        ->assertSee('Activité portée par le service')
        ->assertSee('Service Marchés')
        ->assertSee($activite->indicateur_objectivement_verifiable)
        ->assertSee('Validé le');
});

test("les activités d'un même extrant sont classées par service alphabétiquement", function () {
    $admin = userWithRole('dbcgoq');
    $dc = Departement::factory()->departement()->create();
    $zeta = Departement::factory()->service()->enfantDe($dc)->create(['nom' => 'Zeta Service']);
    $alpha = Departement::factory()->service()->enfantDe($dc)->create(['nom' => 'Alpha Service']);

    // Deux services sous un même extrant : c'est la comparaison entre les deux
    // qui exerce la clé de tri.
    $extrant = Extrant::factory()->create();
    Activite::factory()->pourExtrant($extrant)->pourDepartement($zeta)->create(['statut' => 'en_attente']);
    Activite::factory()->pourExtrant($extrant)->pourDepartement($alpha)->create(['statut' => 'en_attente']);

    $contenu = $this->actingAs($admin)->get(route('validations.entite', $dc))
        ->assertOk()
        ->getContent();

    expect(strpos($contenu, 'Alpha Service'))->toBeLessThan(strpos($contenu, 'Zeta Service'));
});

test("la page d'arbitrage affiche les activités déjà validées avec leur date de validation", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = exerciceArbitre();
    $dc = Departement::factory()->departement()->create();

    Activite::factory()->pourExercice($exercice)->pourDepartement($dc)->create([
        'statut' => 'valide',
        'nom_activite' => 'Activité déjà validée',
        'date_validation' => now()->setDate(2026, 4, 3)->setTime(9, 30),
    ]);

    $this->actingAs($admin)->get(route('validations.entite', $dc))
        ->assertOk()
        ->assertSee('Activité déjà validée')
        ->assertSee('03/04/2026 09:30');
});
