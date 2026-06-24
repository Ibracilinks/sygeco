<?php

use App\Models\Exercice;
use App\Support\ActiveExercice;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('id renvoie null quand aucun exercice n\'existe', function () {
    expect(ActiveExercice::id())->toBeNull();
});

test('id retombe sur l\'exercice actif quand la session est vide', function () {
    Exercice::factory()->create(['annee' => 2024, 'statut' => 'cloture']);
    $actif = Exercice::factory()->actif()->create(['annee' => 2025]);

    expect(ActiveExercice::id())->toBe($actif->id);
});

test('id retombe sur le plus récent quand aucun actif', function () {
    Exercice::factory()->create(['annee' => 2023, 'statut' => 'cloture']);
    $recent = Exercice::factory()->create(['annee' => 2024, 'statut' => 'cloture']);

    expect(ActiveExercice::id())->toBe($recent->id);
});

test('set fixe l\'exercice actif en session et model() le retrouve', function () {
    Exercice::factory()->actif()->create(['annee' => 2025]);
    $choisi = Exercice::factory()->create(['annee' => 2026, 'statut' => 'brouillon']);

    ActiveExercice::set($choisi->id);

    expect(ActiveExercice::id())->toBe($choisi->id);
    expect(ActiveExercice::model()->id)->toBe($choisi->id);
});

test('set null efface le choix et retombe sur la valeur par défaut', function () {
    $actif = Exercice::factory()->actif()->create(['annee' => 2025]);
    $autre = Exercice::factory()->create(['annee' => 2026, 'statut' => 'brouillon']);

    ActiveExercice::set($autre->id);
    ActiveExercice::set(null);

    expect(ActiveExercice::id())->toBe($actif->id);
});

test('un id de session pointant vers un exercice supprimé est ignoré', function () {
    $actif = Exercice::factory()->actif()->create(['annee' => 2025]);

    ActiveExercice::set(99999);

    expect(ActiveExercice::id())->toBe($actif->id);
});
