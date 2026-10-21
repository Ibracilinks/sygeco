<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Support\ActiveExercice;
use App\Support\AnalyseActivites;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Analyse des activités : indicateurs de la programmation et doublons potentiels,
| pour la cellule planification, le contrôle de gestion et la DBCGOQ.
*/

test('même intitulé aux accents et à la casse près, dans deux entités : doublon fort', function () {
    $extrant = Extrant::factory()->create();
    $a = Activite::factory()->pourExtrant($extrant)->pourDepartement(Departement::factory()->create())->create(['nom_activite' => 'Atelier de validation du PTA']);
    $b = Activite::factory()->pourDepartement(Departement::factory()->create())->create(['nom_activite' => 'ATELIER DE VALIDATION DU PTA.']);
    $autre = Activite::factory()->pourExtrant($extrant)->create(['nom_activite' => 'Formation des agents comptables']);

    $doublons = (new AnalyseActivites(collect([$a, $b, $autre])->each->load(['extrant', 'departement'])))->doublons();

    expect($doublons)->toHaveCount(1)
        ->and($doublons[0]['niveau'])->toBe('fort')
        ->and($doublons[0]['motif'])->toBe('Même intitulé dans plusieurs entités')
        ->and(collect($doublons[0]['activites'])->pluck('id')->sort()->values()->all())->toBe(collect([$a->id, $b->id])->sort()->values()->all());
});

test('intitulés très proches sous le même extrant : doublon moyen ; sous deux extrants : rien', function () {
    $extrant = Extrant::factory()->create();
    $a = Activite::factory()->pourExtrant($extrant)->create(['nom_activite' => 'Organiser l\'atelier régional de validation du manuel de procédures']);
    $b = Activite::factory()->pourExtrant($extrant)->create(['nom_activite' => 'Organiser un atelier régional de validation du manuel des procédures']);
    $ailleurs = Activite::factory()->pourExtrant(Extrant::factory()->create())->create(['nom_activite' => 'Organiser un atelier régional de validation du manuel des procédures budgétaires']);

    $doublons = (new AnalyseActivites(collect([$a, $b, $ailleurs])->each->load(['extrant', 'departement'])))->doublons();

    expect($doublons)->toHaveCount(1)
        ->and($doublons[0]['niveau'])->toBe('moyen')
        ->and(collect($doublons[0]['activites'])->pluck('id')->all())->toBe([$a->id, $b->id]);
});

test('les indicateurs comptent statuts, budgets programmé et validé, taux de validation', function () {
    $activites = collect([
        Activite::factory()->brouillon()->create(['cout' => 1_000_000, 'pour_memoire' => false]),
        Activite::factory()->enAttente()->create(['cout' => 2_000_000, 'pour_memoire' => false]),
        Activite::factory()->valide()->create(['cout' => 3_000_000, 'pour_memoire' => false]),
        Activite::factory()->valide()->create(['cout' => 0, 'pour_memoire' => true]),
    ])->each->load(['extrant', 'departement']);

    $indicateurs = (new AnalyseActivites($activites))->indicateurs();

    expect($indicateurs['nombre'])->toBe(4)
        ->and($indicateurs['par_statut']['valide'])->toBe(2)
        ->and($indicateurs['budget_programme'])->toBe(6_000_000.0)
        ->and($indicateurs['budget_valide'])->toBe(3_000_000.0)
        // 2 validées sur 3 soumises.
        ->and($indicateurs['taux_validation'])->toBe(66.7)
        ->and($indicateurs['pour_memoire'])->toBe(1);
});

test('la page est ouverte à la cellule planification, au contrôle de gestion et à la DBCGOQ', function (string $role) {
    $exercice = Exercice::factory()->actif()->create(['annee' => 2048, 'date_debut' => '2048-01-01', 'date_fin' => '2048-12-31']);
    Activite::factory()->pourExercice($exercice)->create(['nom_activite' => 'ACTIVITE_ANALYSEE']);
    $user = userWithRole($role, ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.analyse'))->assertOk()->assertSee('Analyse des activités')->assertSee('2048');
    $this->actingAs($user)->get(route('dashboard'))->assertSee(route('activites.analyse'));
})->with(['agent-planification', 'service-controle-gestion', 'dbcgoq']);

test('la page est refusée aux autres profils', function (string $role) {
    $this->actingAs(userWithRole($role, ['departement_id' => Departement::factory()->create()->id]))
        ->get(route('activites.analyse'))->assertForbidden();
})->with(['responsable-programme', 'chef-service', 'suivi-evaluation']);
