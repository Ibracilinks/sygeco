<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('initials renvoie les initiales des deux premiers mots', function () {
    expect(User::factory()->make(['name' => 'Fatou Diop'])->initials())->toBe('FD');
    expect(User::factory()->make(['name' => 'Jean'])->initials())->toBe('J');
    expect(User::factory()->make(['name' => 'Amadou Bocar Tall'])->initials())->toBe('AB');
});

test('le mot de passe est haché automatiquement (cast)', function () {
    $user = User::factory()->create(['password' => 'plain-secret']);

    expect($user->password)->not->toBe('plain-secret');
    expect(\Illuminate\Support\Facades\Hash::check('plain-secret', $user->password))->toBeTrue();
});

test('les helpers de rôle reflètent le rôle assigné', function () {
    $dbcgoq = userWithRole('dbcgoq');
    $chef = userWithRole('chef');
    $agent = userWithRole('agent');

    expect($dbcgoq->isDbcgoq())->toBeTrue();
    expect($dbcgoq->isChef())->toBeFalse();
    expect($chef->isChef())->toBeTrue();
    expect($agent->isAgent())->toBeTrue();
});

test('un utilisateur appartient à un département', function () {
    $dep = Departement::factory()->create();
    $user = User::factory()->dansDepartement($dep)->create();

    expect($user->departement)->toBeInstanceOf(Departement::class);
    expect($user->departement->id)->toBe($dep->id);
});
