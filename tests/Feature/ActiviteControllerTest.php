<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Models\User;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Crée une activité nommée, rattachée à un exercice et un département donnés,
 * de sorte qu'elle apparaisse dans l'index (filtré par l'exercice actif).
 */
function activiteNommee(Exercice $exercice, Departement $departement, string $nom, ?User $saisiPar = null, array $attributs = []): Activite
{
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    $factory = Activite::factory()->pourExtrant($extrant)->pourDepartement($departement);

    if ($saisiPar) {
        $factory = $factory->saisiePar($saisiPar);
    }

    return $factory->create($attributs + ['nom_activite' => $nom]);
}

/*
|--------------------------------------------------------------------------
| Accès & rôles
|--------------------------------------------------------------------------
*/

test('un invité est redirigé vers la connexion', function () {
    $this->get(route('activites.index'))->assertRedirect(route('login'));
});

test('un utilisateur sans rôle ne peut pas accéder à la liste', function () {
    seedRolesAndPermissions();
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('activites.index'))->assertForbidden();
});

test('un agent peut voir la liste des activités', function () {
    $agent = userWithRole('agent');

    $this->actingAs($agent)->get(route('activites.index'))->assertOk();
});

test('le dbcgoq peut voir le formulaire de création', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->get(route('activites.create'))->assertOk();
});

test('un agent ne peut pas créer d\'activité (pas de permission create)', function () {
    $agent = userWithRole('agent');

    $this->actingAs($agent)->get(route('activites.create'))->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Création
|--------------------------------------------------------------------------
*/

test('le dbcgoq peut créer une activité', function () {
    $admin = userWithRole('dbcgoq');
    $extrant = Extrant::factory()->create();
    $departement = Departement::factory()->create();

    $response = $this->actingAs($admin)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $departement->id,
        'nom_activite' => 'Former les agents',
        'indicateur_objectivement_verifiable' => 'Nombre de sessions',
        'moyen_verification' => 'Liste de présence',
        'cout' => 1500000,
        'trimestre_1' => 'oui',
        'commentaires' => null,
    ]);

    $response->assertRedirect(route('activites.index'));
    $this->assertDatabaseHas('activites', [
        'nom_activite' => 'Former les agents',
        'statut' => 'brouillon',
        'trimestre_1' => 'oui',
        'trimestre_2' => 'non',
        'saisi_par' => $admin->id,
    ]);
});

test('la création échoue sans les champs requis', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)
        ->from(route('activites.create'))
        ->post(route('activites.store'), [])
        ->assertSessionHasErrors(['extrant_id', 'departement_id', 'nom_activite', 'cout']);
});

test('un chef de département voit son département forcé à la création', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $autreDep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');
    $extrant = Extrant::factory()->create();

    $this->actingAs($chef)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $autreDep->id, // tentative de contournement
        'nom_activite' => 'Activité du chef',
        'indicateur_objectivement_verifiable' => 'Indicateur',
        'moyen_verification' => 'PV',
        'cout' => 500000,
        'trimestre_1' => 'oui',
    ]);

    $this->assertDatabaseHas('activites', [
        'nom_activite' => 'Activité du chef',
        'departement_id' => $dep->id, // forcé sur le département du chef
    ]);
});

test('les structures intervenantes sont enregistrées, sans la structure porteuse', function () {
    $admin = userWithRole('dbcgoq');
    $porteuse = Departement::factory()->create();
    $intervenanteA = Departement::factory()->create();
    $intervenanteB = Departement::factory()->create();
    $extrant = Extrant::factory()->create();

    $this->actingAs($admin)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $porteuse->id,
        'structures_intervenantes' => [$intervenanteA->id, $intervenanteB->id, $porteuse->id],
        'nom_activite' => 'Activité multi-structures',
        'indicateur_objectivement_verifiable' => 'Indicateur',
        'moyen_verification' => 'PV',
        'cout' => 500000,
        'trimestre_1' => 'oui',
    ])->assertRedirect(route('activites.index'));

    $activite = Activite::where('nom_activite', 'Activité multi-structures')->firstOrFail();

    expect($activite->departements->pluck('id')->sort()->values()->all())
        ->toBe(collect([$intervenanteA->id, $intervenanteB->id])->sort()->values()->all());
});

test('la mise à jour remplace les structures intervenantes', function () {
    $admin = userWithRole('dbcgoq');
    $ancienne = Departement::factory()->create();
    $nouvelle = Departement::factory()->create();
    $porteuse = Departement::factory()->create();
    $activite = Activite::factory()->pourDepartement($porteuse)->create(['statut' => 'brouillon']);
    $activite->departements()->sync([$ancienne->id]);

    $this->actingAs($admin)->put(route('activites.update', $activite), [
        'extrant_id' => $activite->extrant_id,
        'departement_id' => $activite->departement_id,
        'structures_intervenantes' => [$nouvelle->id],
        'nom_activite' => $activite->nom_activite,
        'indicateur_objectivement_verifiable' => 'Indicateur',
        'moyen_verification' => 'PV',
        'cout' => $activite->cout,
        'trimestre_1' => 'oui',
    ])->assertRedirect(route('activites.index'));

    expect($activite->refresh()->departements->pluck('id')->all())->toBe([$nouvelle->id]);
});

test('une structure intervenante inexistante est refusée', function () {
    $admin = userWithRole('dbcgoq');
    $extrant = Extrant::factory()->create();
    $dep = Departement::factory()->create();

    $this->actingAs($admin)
        ->from(route('activites.create'))
        ->post(route('activites.store'), [
            'extrant_id' => $extrant->id,
            'departement_id' => $dep->id,
            'structures_intervenantes' => [999999],
            'nom_activite' => 'Activité',
            'indicateur_objectivement_verifiable' => 'Indicateur',
            'moyen_verification' => 'PV',
            'cout' => 1000,
            'trimestre_1' => 'oui',
        ])
        ->assertSessionHasErrors('structures_intervenantes.0');
});

/*
|--------------------------------------------------------------------------
| Workflow : soumettre / valider / refuser
|--------------------------------------------------------------------------
*/

test('un chef peut soumettre une activité de son département', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');
    $activite = Activite::factory()->brouillon()->pourDepartement($dep)->create();

    $this->actingAs($chef)->post(route('activites.soumettre', $activite))
        ->assertRedirect(route('activites.index'));

    expect($activite->fresh()->statut)->toBe('en_attente');
});

test('un chef ne peut pas soumettre une activité d\'un autre département', function () {
    seedRolesAndPermissions();
    $dep = Departement::factory()->create();
    $autreDep = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($dep)->create();
    $chef->assignRole('chef');
    $activite = Activite::factory()->brouillon()->pourDepartement($autreDep)->create();

    $this->actingAs($chef)->post(route('activites.soumettre', $activite))->assertForbidden();

    expect($activite->fresh()->statut)->toBe('brouillon');
});

test('le dbcgoq peut valider une activité soumise', function () {
    $admin = userWithRole('dbcgoq');
    $activite = Activite::factory()->soumis()->create();

    $this->actingAs($admin)->post(route('activites.valider', $activite))
        ->assertRedirect(route('activites.index'))
        ->assertSessionHas('success');

    expect($activite->fresh())
        ->statut->toBe('valide')
        ->valide_par->toBe($admin->id);
});

test('un agent ne peut pas valider une activité', function () {
    $agent = userWithRole('agent');
    $activite = Activite::factory()->soumis()->create();

    $this->actingAs($agent)->post(route('activites.valider', $activite))
        ->assertSessionHas('error');

    expect($activite->fresh()->statut)->toBe('en_attente');
});

test('le dbcgoq peut refuser une activité avec un motif', function () {
    $admin = userWithRole('dbcgoq');
    $activite = Activite::factory()->soumis()->create();

    $this->actingAs($admin)->post(route('activites.refuser', $activite), [
        'motif_refus' => 'Budget non justifié, merci de revoir',
    ])->assertRedirect(route('activites.show', $activite));

    expect($activite->fresh())
        ->statut->toBe('rejete')
        ->motif_refus->toBe('Budget non justifié, merci de revoir');
});

test('un chef voit les activités de son sous-arbre (entité + entités en dessous)', function () {
    seedRolesAndPermissions();
    $exercice = Exercice::factory()->actif()->create();
    $direction = Departement::factory()->direction()->create();
    $departement = Departement::factory()->departement()->enfantDe($direction)->create();
    $service = Departement::factory()->service()->enfantDe($departement)->create();
    $horsArbre = Departement::factory()->direction()->create();

    $chef = User::factory()->dansDepartement($departement)->create();
    $chef->assignRole('chef');

    activiteNommee($exercice, $departement, 'ACTPROPRE');
    activiteNommee($exercice, $service, 'ACTSERVICE');
    activiteNommee($exercice, $horsArbre, 'ACTHORS');

    $this->actingAs($chef)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))
        ->assertOk()
        ->assertSee('ACTPROPRE')
        ->assertSee('ACTSERVICE')
        ->assertDontSee('ACTHORS');
});

test('un agent ne voit que les activités qu\'il a lui-même saisies', function () {
    seedRolesAndPermissions();
    $exercice = Exercice::factory()->actif()->create();
    $direction = Departement::factory()->direction()->create();

    $agent = User::factory()->dansDepartement($direction)->create();
    $agent->assignRole('agent');
    $collegue = User::factory()->dansDepartement($direction)->create();

    // Même entité, même exercice : seule la paternité de la saisie les sépare.
    activiteNommee($exercice, $direction, 'ACTMOI', $agent);
    activiteNommee($exercice, $direction, 'ACTCOLLEGUE', $collegue);

    $this->actingAs($agent)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))
        ->assertOk()
        ->assertSee('ACTMOI')
        ->assertDontSee('ACTCOLLEGUE');
});

test("un chef voit les activités de sa direction et d'aucune autre", function () {
    seedRolesAndPermissions();
    $exercice = Exercice::factory()->actif()->create();
    $direction = Departement::factory()->direction()->create();
    $service = Departement::factory()->service()->enfantDe($direction)->create();
    $autreDirection = Departement::factory()->direction()->create();

    $chef = User::factory()->dansDepartement($direction)->create();
    $chef->assignRole('chef');

    activiteNommee($exercice, $direction, 'ACTDIR');
    activiteNommee($exercice, $service, 'ACTSRV');
    activiteNommee($exercice, $autreDirection, 'ACTAILLEURS');

    // Son entité et les services qu'elle chapeaute, jamais la direction voisine.
    $this->actingAs($chef)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))
        ->assertOk()
        ->assertSee('ACTDIR')
        ->assertSee('ACTSRV')
        ->assertDontSee('ACTAILLEURS');
});

test('le dbcgoq ne voit pas les activités encore en brouillon', function () {
    seedRolesAndPermissions();
    $exercice = Exercice::factory()->actif()->create();
    $direction = Departement::factory()->direction()->create();

    $dbcgoq = User::factory()->create();
    $dbcgoq->assignRole('dbcgoq');
    $agent = User::factory()->dansDepartement($direction)->create();
    $agent->assignRole('agent');

    activiteNommee($exercice, $direction, 'ACTBROUILLON', $agent, ['statut' => 'brouillon']);
    activiteNommee($exercice, $direction, 'ACTSOUMISE', $agent, ['statut' => 'en_attente']);

    // Une programmation n'entre dans le champ du dbcgoq qu'une fois soumise.
    $this->actingAs($dbcgoq)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))
        ->assertOk()
        ->assertSee('ACTSOUMISE')
        ->assertDontSee('ACTBROUILLON');
});

test('le dbcgoq voit ses propres brouillons', function () {
    seedRolesAndPermissions();
    $exercice = Exercice::factory()->actif()->create();
    $direction = Departement::factory()->direction()->create();

    $dbcgoq = User::factory()->dansDepartement($direction)->create();
    $dbcgoq->assignRole('dbcgoq');

    activiteNommee($exercice, $direction, 'MONBROUILLON', $dbcgoq, ['statut' => 'brouillon']);

    // La règle masque les brouillons d'autrui, pas les siens.
    $this->actingAs($dbcgoq)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))
        ->assertOk()
        ->assertSee('MONBROUILLON');
});

test('un chef peut voir (policy) une activité d\'une entité en dessous', function () {
    seedRolesAndPermissions();
    $departement = Departement::factory()->departement()->create();
    $service = Departement::factory()->service()->enfantDe($departement)->create();
    $chef = User::factory()->dansDepartement($departement)->create();
    $chef->assignRole('chef');
    $activite = Activite::factory()->pourDepartement($service)->create();

    expect($chef->can('view', $activite))->toBeTrue();
});

test('refuser exige un motif d\'au moins 10 caractères', function () {
    $admin = userWithRole('dbcgoq');
    $activite = Activite::factory()->soumis()->create();

    $this->actingAs($admin)
        ->from(route('activites.show', $activite))
        ->post(route('activites.refuser', $activite), ['motif_refus' => 'court'])
        ->assertSessionHasErrors('motif_refus');
});

/*
|--------------------------------------------------------------------------
| Index : présentation en cadre logique
|--------------------------------------------------------------------------
*/

test("l'index groupe les activités par résultat puis extrant et affiche la date de transmission", function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();

    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create(['code' => 'RES-CADRE', 'libelle' => 'Résultat du cadre']);
    $extrant = Extrant::factory()->forResultat($resultat)->create(['code' => 'EXT-CADRE']);

    Activite::factory()->pourExtrant($extrant)->create([
        'nom_activite' => 'Activité rattachée au cadre',
        'statut' => 'en_attente',
        'date_soumission' => now()->setDate(2026, 3, 12)->setTime(14, 5),
    ]);

    $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))
        ->assertOk()
        ->assertSee('Transmis le')
        ->assertSee('RES-CADRE')
        ->assertSee('Résultat du cadre')
        ->assertSee('EXT-CADRE')
        ->assertSee('Activité rattachée au cadre')
        ->assertSee('12/03/2026 14:05');
});

test('le sélecteur de structures intervenantes est rendu sur la création et la modification', function () {
    $admin = userWithRole('dbcgoq');
    $intervenante = Departement::factory()->create(['nom' => 'Direction des Systemes']);
    $activite = Activite::factory()->create(['statut' => 'brouillon']);
    $activite->departements()->sync([$intervenante->id]);

    foreach ([route('activites.create'), route('activites.edit', $activite)] as $url) {
        $this->actingAs($admin)->get($url)
            ->assertOk()
            ->assertSee('Structures intervenantes')
            ->assertSee('data-si-widget', escape: false)
            ->assertSee('Rechercher une structure', escape: false)
            ->assertSee('Direction des Systemes');
    }

    // Sur la modification, l'intervenante déjà enregistrée est pré-cochée.
    $contenu = $this->actingAs($admin)->get(route('activites.edit', $activite))->getContent();

    expect($contenu)->toMatch(
        '/value="'.$intervenante->id.'"\s+data-si-case[^>]*\schecked/'
    );
});

test('un chef de Direction Centrale voit sa DC et ses services dans le champ Structure', function () {
    seedRolesAndPermissions();
    $dc = Departement::factory()->departement()->create(['nom' => 'DC Ressources']);
    $serviceA = Departement::factory()->service()->enfantDe($dc)->create(['nom' => 'Service Paie']);
    $serviceB = Departement::factory()->service()->enfantDe($dc)->create(['nom' => 'Service Formation']);
    $horsPerimetre = Departement::factory()->departement()->create(['nom' => 'DC Etrangere']);

    $chef = User::factory()->dansDepartement($dc)->create();
    $chef->assignRole('chef');

    foreach ([route('activites.create'), route('activites.edit', Activite::factory()->pourDepartement($dc)->create(['statut' => 'brouillon']))] as $url) {
        $contenu = $this->actingAs($chef)->get($url)->assertOk()->getContent();

        // Le champ est une liste déroulante, plus un libellé figé.
        expect($contenu)->toContain('<select id="departement_id"');

        // On isole le dropdown : la liste des structures intervenantes, elle, couvre
        // volontairement toute l'organisation et contient donc aussi les entités
        // hors périmètre.
        $debut = strpos($contenu, '<select id="departement_id"');
        $dropdown = substr($contenu, $debut, strpos($contenu, '</select>', $debut) - $debut);

        expect($dropdown)->toContain('DC Ressources')
            ->toContain('Service Paie')
            ->toContain('Service Formation')
            ->not->toContain('DC Etrangere');
    }
});

test('un chef peut rattacher une activité à un service de sa Direction Centrale', function () {
    seedRolesAndPermissions();
    $dc = Departement::factory()->departement()->create();
    $service = Departement::factory()->service()->enfantDe($dc)->create();
    $extrant = Extrant::factory()->create();

    $chef = User::factory()->dansDepartement($dc)->create();
    $chef->assignRole('chef');

    $this->actingAs($chef)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $service->id,
        'nom_activite' => 'Activité portée par un service',
        'indicateur_objectivement_verifiable' => 'Indicateur',
        'moyen_verification' => 'PV',
        'cout' => 400000,
        'trimestre_1' => 'oui',
    ])->assertRedirect(route('activites.index'));

    $this->assertDatabaseHas('activites', [
        'nom_activite' => 'Activité portée par un service',
        'departement_id' => $service->id,
    ]);
});

test('deux activités ne peuvent pas porter le même nom dans un exercice', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $departement = Departement::factory()->create();
    $extrant = Extrant::factory()->create();

    activiteNommee($exercice, $departement, 'Former les agents', $admin);

    $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->from(route('activites.create'))
        ->post(route('activites.store'), [
            'extrant_id' => $extrant->id,
            'departement_id' => $departement->id,
            'nom_activite' => 'Former les agents',
            'indicateur_objectivement_verifiable' => 'Nombre de sessions',
            'moyen_verification' => 'Liste de présence',
            'cout' => 1000000,
            'trimestre_1' => 'oui',
        ])
        ->assertSessionHasErrors('nom_activite');
});

test('le même nom reste possible dans un autre exercice', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $exercicePasse = Exercice::factory()->create(['annee' => $exercice->annee - 1, 'statut' => 'cloture']);
    $departement = Departement::factory()->create();
    $extrant = Extrant::factory()->create();

    // Le même intitulé reconduit d'une année sur l'autre : c'est le cas normal.
    activiteNommee($exercicePasse, $departement, 'Former les agents', $admin);

    $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->post(route('activites.store'), [
            'extrant_id' => $extrant->id,
            'departement_id' => $departement->id,
            'nom_activite' => 'Former les agents',
            'indicateur_objectivement_verifiable' => 'Nombre de sessions',
            'moyen_verification' => 'Liste de présence',
            'cout' => 1000000,
            'trimestre_1' => 'oui',
        ])
        ->assertSessionHasNoErrors();
});

test('dans un extrant, les activités sont classées par structure de A à Z', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();

    $zeta = Departement::factory()->create(['nom' => 'Zeta Direction', 'code' => 'ZETA']);
    $alpha = Departement::factory()->create(['nom' => 'Alpha Direction', 'code' => 'ALPHA']);
    $mid = Departement::factory()->create(['nom' => 'Medina Direction', 'code' => 'MEDINA']);

    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    foreach ([[$zeta, 'ACTZETA'], [$alpha, 'ACTALPHA'], [$mid, 'ACTMEDINA']] as [$dep, $nom]) {
        // Statut explicite : la factory en tire un au hasard, et un brouillon d'autrui
        // serait invisible au dbcgoq — ce qui n'est pas ce que ce test mesure.
        Activite::factory()->pourExtrant($extrant)->pourDepartement($dep)
            ->create(['nom_activite' => $nom, 'exercice_id' => $exercice->id, 'statut' => 'valide']);
    }

    $contenu = $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))
        ->assertOk()
        ->getContent();

    expect(strpos($contenu, 'ACTALPHA'))->toBeLessThan(strpos($contenu, 'ACTMEDINA'))
        ->and(strpos($contenu, 'ACTMEDINA'))->toBeLessThan(strpos($contenu, 'ACTZETA'));
});

test('une activité « pour mémoire » est enregistrée sans coût et ne pèse rien au budget', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $extrant = Extrant::factory()->create();
    $departement = Departement::factory()->create();

    $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->post(route('activites.store'), [
            'extrant_id' => $extrant->id,
            'departement_id' => $departement->id,
            'nom_activite' => 'Activité déjà budgétée ailleurs',
            'indicateur_objectivement_verifiable' => 'Indicateur',
            'moyen_verification' => 'PV',
            'pour_memoire' => 'on',
            // Aucun coût transmis : c'est tout l'intérêt du PM.
            'trimestre_1' => 'oui',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('activites.index'));

    $activite = Activite::where('nom_activite', 'Activité déjà budgétée ailleurs')->firstOrFail();

    expect($activite->pour_memoire)->toBeTrue()
        ->and((float) $activite->cout)->toBe(0.0);

    // La liste affiche « PM » à la place d'un montant.
    $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))
        ->assertOk()
        ->assertSee('PM');
});

test('le coût reste obligatoire quand « pour mémoire » n\'est pas coché', function () {
    $admin = userWithRole('dbcgoq');
    $extrant = Extrant::factory()->create();
    $departement = Departement::factory()->create();

    $this->actingAs($admin)
        ->from(route('activites.create'))
        ->post(route('activites.store'), [
            'extrant_id' => $extrant->id,
            'departement_id' => $departement->id,
            'nom_activite' => 'Activité sans coût',
            'indicateur_objectivement_verifiable' => 'Indicateur',
            'moyen_verification' => 'PV',
            'trimestre_1' => 'oui',
        ])
        ->assertSessionHasErrors('cout');
});
