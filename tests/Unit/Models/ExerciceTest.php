<?php

use App\Models\Exercice;
use App\Models\Objectif;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('actifCourant renvoie l\'exercice actif le plus récent', function () {
    Exercice::factory()->create(['annee' => 2023, 'statut' => 'cloture']);
    $actif2024 = Exercice::factory()->actif()->create(['annee' => 2024]);
    $actif2025 = Exercice::factory()->actif()->create(['annee' => 2025]);

    expect(Exercice::actifCourant()->id)->toBe($actif2025->id);
});

test('actifCourant renvoie null quand aucun exercice actif', function () {
    Exercice::factory()->create(['annee' => 2022, 'statut' => 'cloture']);

    expect(Exercice::actifCourant())->toBeNull();
});

test('le scope actif ne retourne que les exercices actifs', function () {
    Exercice::factory()->actif()->create(['annee' => 2025]);
    Exercice::factory()->create(['annee' => 2024, 'statut' => 'brouillon']);

    expect(Exercice::actif()->count())->toBe(1);
});

test('le scope ordered trie par année décroissante', function () {
    Exercice::factory()->create(['annee' => 2022]);
    Exercice::factory()->create(['annee' => 2025]);
    Exercice::factory()->create(['annee' => 2023]);

    expect(Exercice::ordered()->pluck('annee')->all())->toBe([2025, 2023, 2022]);
});

test('joursAvantLimite renvoie null sans date limite', function () {
    $exercice = Exercice::factory()->create(['date_limite_saisie' => null]);

    expect($exercice->joursAvantLimite())->toBeNull();
});

test('joursAvantLimite est positif pour une limite future', function () {
    $exercice = Exercice::factory()->create([
        'date_limite_saisie' => now()->addDays(10)->toDateString(),
    ]);

    expect($exercice->joursAvantLimite())->toBe(10);
});

test('joursAvantLimite est négatif pour une limite dépassée', function () {
    $exercice = Exercice::factory()->create([
        'date_limite_saisie' => now()->subDays(5)->toDateString(),
    ]);

    expect($exercice->joursAvantLimite())->toBe(-5);
});

test('un exercice a plusieurs objectifs', function () {
    $exercice = Exercice::factory()->create(['annee' => 2027]);
    Objectif::factory()->count(3)->create(['exercice_id' => $exercice->id, 'annee' => 2027]);

    expect($exercice->objectifs)->toHaveCount(3);
});
