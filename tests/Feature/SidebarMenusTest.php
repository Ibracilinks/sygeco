<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Le menu doit refléter exactement les rôles autorisés sur la route.
 * Une condition en liste noire s'était inversée sans que rien ne le signale.
 */
test('le menu Suivi & Évaluation suit les rôles autorisés sur la route', function (string $role, bool $visible) {
    $user = userWithRole($role);

    $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    expect(str_contains($html, 'data-groupe="evaluation"'))->toBe($visible)
        ->and(str_contains($html, 'evaluations/mi-parcours'))->toBe($visible);
})->with([
    ['superadmin', true],
    ['dbcgoq', true],
    ['chef', true],
    ['agent', true],
    ['suivi-evaluation', true],
    ['agent-planification', false],
]);

test('le menu Suivi & Évaluation reste visible quand suivi-evaluation cumule un autre rôle', function () {
    $user = userWithRole('suivi-evaluation');
    $user->assignRole('agent-planification');

    // Le rôle le plus large l'emporte : un cumul ne doit pas retirer un accès.
    $html = $this->actingAs($user->fresh())->get(route('dashboard'))->assertOk()->getContent();

    expect($html)->toContain('data-groupe="evaluation"');
});
