<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('le scope active ne retourne que les départements actifs', function () {
    Departement::factory()->active()->count(2)->create();
    Departement::factory()->inactive()->create();

    expect(Departement::active()->count())->toBe(2);
});

test('le scope ordered trie par ordre puis nom', function () {
    Departement::factory()->create(['ordre' => 2, 'nom' => 'B']);
    Departement::factory()->create(['ordre' => 1, 'nom' => 'A']);

    expect(Departement::ordered()->pluck('ordre')->all())->toBe([1, 2]);
});

test('un département a des utilisateurs et des activités', function () {
    $dep = Departement::factory()->create();
    User::factory()->dansDepartement($dep)->count(2)->create();
    Activite::factory()->pourDepartement($dep)->count(3)->create();

    expect($dep->users)->toHaveCount(2);
    expect($dep->activites)->toHaveCount(3);
});

test('un département est soft-deletable', function () {
    $dep = Departement::factory()->create();
    $dep->delete();

    expect(Departement::find($dep->id))->toBeNull();
    expect(Departement::withTrashed()->find($dep->id))->not->toBeNull();
});

test('la hiérarchie parent/enfants relie les trois niveaux', function () {
    $direction = Departement::factory()->direction()->create();
    $departement = Departement::factory()->departement()->enfantDe($direction)->create();
    $service = Departement::factory()->service()->enfantDe($departement)->create();

    expect($service->parent->is($departement))->toBeTrue();
    expect($departement->parent->is($direction))->toBeTrue();
    expect($direction->enfants->pluck('id'))->toContain($departement->id);
    expect($departement->enfants->pluck('id'))->toContain($service->id);
});

test('le scope ofType filtre par type d\'entité', function () {
    Departement::factory()->direction()->create();
    Departement::factory()->departement()->count(2)->create();
    Departement::factory()->service()->create();

    expect(Departement::ofType(Departement::TYPE_DIRECTION)->count())->toBe(1);
    expect(Departement::ofType(Departement::TYPE_DEPARTEMENT)->count())->toBe(2);
    expect(Departement::ofType(Departement::TYPE_SERVICE)->count())->toBe(1);
});
