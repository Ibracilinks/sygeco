<?php

use App\Models\Activite;
use App\Models\Extrant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('getFullName concatène code et libellé', function () {
    $extrant = Extrant::factory()->create(['code' => 'EXT_1', 'libelle' => 'Formation']);

    expect($extrant->full_name)->toBe('EXT_1 - Formation');
});

test('nb_activites et budget_total agrègent les activités', function () {
    $extrant = Extrant::factory()->create();
    Activite::factory()->pourExtrant($extrant)->avecCout(300000)->create();
    Activite::factory()->pourExtrant($extrant)->avecCout(200000)->create();

    expect($extrant->nb_activites)->toBe(2);
    expect((float) $extrant->budget_total)->toBe(500000.0);
});

test('statut label/color selon is_active', function () {
    expect(Extrant::factory()->actif()->create()->statut_label)->toContain('Actif');
    expect(Extrant::factory()->actif()->create()->statut_color)->toBe('green');
    expect(Extrant::factory()->inactif()->create()->statut_label)->toContain('Inactif');
    expect(Extrant::factory()->inactif()->create()->statut_color)->toBe('red');
});

test('le scope actif ne retourne que les extrants actifs', function () {
    Extrant::factory()->actif()->count(2)->create();
    Extrant::factory()->inactif()->create();

    expect(Extrant::actif()->count())->toBe(2);
});
