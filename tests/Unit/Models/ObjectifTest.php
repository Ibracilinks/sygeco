<?php

use App\Models\Activite;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un objectif agrège résultats, extrants et activités', function () {
    $objectif = Objectif::factory()->create();
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();
    Activite::factory()->pourExtrant($extrant)->count(2)->create();

    expect($objectif->nb_resultats)->toBe(1);
    expect($objectif->nb_extrants)->toBe(1);
    expect($objectif->nb_activites)->toBe(2);
});

test('le budget total de l\'objectif somme le coût des activités', function () {
    $objectif = Objectif::factory()->create();
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();
    Activite::factory()->pourExtrant($extrant)->avecCout(1000000)->create();
    Activite::factory()->pourExtrant($extrant)->avecCout(500000)->create();

    expect((float) $objectif->budget_total)->toBe(1500000.0);
});

test('le scope actif filtre les objectifs actifs', function () {
    Objectif::factory()->actif()->count(2)->create();
    Objectif::factory()->inactif()->create();

    expect(Objectif::actif()->count())->toBe(2);
});

test('le scope byAnnee filtre par année', function () {
    Objectif::factory()->pourAnnee(2025)->create();
    Objectif::factory()->pourAnnee(2024)->create();

    expect(Objectif::byAnnee(2025)->count())->toBe(1);
});

test('le scope forExercice filtre par exercice et ignore null', function () {
    $a = Objectif::factory()->pourAnnee(2025)->create();
    Objectif::factory()->pourAnnee(2024)->create();

    expect(Objectif::forExercice($a->exercices()->value('exercices.id'))->count())->toBe(1);
    expect(Objectif::forExercice(null)->count())->toBe(2);
});

test('un objectif pluriannuel est retenu sur chacun de ses exercices', function () {
    $exercices = collect([2026, 2027, 2028, 2029, 2030])
        ->map(fn ($annee) => Exercice::factory()->create(['annee' => $annee]));

    $plan = Objectif::factory()->pourExercices($exercices->all())->create(['code' => 'PS_2026_2030']);
    Objectif::factory()->pourExercice($exercices->first())->create();

    expect($plan->fresh()->exercices)->toHaveCount(5);
    expect($plan->periode_libelle)->toBe('2026-2030');
    expect($plan->est_pluriannuel)->toBeTrue();
    // L'année de départ est recalculée depuis les exercices couverts.
    expect($plan->fresh()->annee)->toBe(2026);

    // Il apparaît aussi bien en 2026 (avec l'objectif annuel) qu'en 2029 (seul).
    expect(Objectif::byAnnee(2026)->count())->toBe(2);
    expect(Objectif::byAnnee(2029)->pluck('id')->all())->toBe([$plan->id]);
});

test('getStatutLabel et getStatutColor reflètent le statut', function () {
    $actif = Objectif::factory()->actif()->create();
    $inactif = Objectif::factory()->inactif()->create();

    expect($actif->statut_label)->toContain('Actif');
    expect($actif->statut_color)->toBe('green');
    expect($inactif->statut_label)->toContain('Inactif');
    expect($inactif->statut_color)->toBe('red');
});

test('extrants via hasManyThrough remonte les extrants du résultat', function () {
    $objectif = Objectif::factory()->create();
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    Extrant::factory()->forResultat($resultat)->count(2)->create();

    expect($objectif->extrants)->toHaveCount(2);
});
