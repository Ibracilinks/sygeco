<?php

use App\Models\Departement;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('on crée un service rattaché à un département', function () {
    $admin = userWithRole('dbcgoq');
    $direction = Departement::factory()->direction()->create();
    $departement = Departement::factory()->departement()->enfantDe($direction)->create();

    $this->actingAs($admin)->post(route('departements.store'), [
        'code' => 'SRV_TEST',
        'nom' => 'Service de Test',
        'type' => Departement::TYPE_SERVICE,
        'parent_id' => $departement->id,
    ])->assertRedirect(route('departements.index'));

    $service = Departement::where('code', 'SRV_TEST')->first();
    expect($service)->not->toBeNull();
    expect($service->type)->toBe(Departement::TYPE_SERVICE);
    expect($service->parent->is($departement))->toBeTrue();
});

test('un service rattaché à une direction est refusé (incohérence de niveau)', function () {
    $admin = userWithRole('dbcgoq');
    $direction = Departement::factory()->direction()->create();

    $this->actingAs($admin)
        ->from(route('departements.create'))
        ->post(route('departements.store'), [
            'code' => 'SRV_KO',
            'nom' => 'Service mal rattaché',
            'type' => Departement::TYPE_SERVICE,
            'parent_id' => $direction->id,
        ])->assertSessionHasErrors('parent_id');

    expect(Departement::where('code', 'SRV_KO')->exists())->toBeFalse();
});

test('une direction avec un parent est refusée', function () {
    $admin = userWithRole('dbcgoq');
    $autre = Departement::factory()->direction()->create();

    $this->actingAs($admin)
        ->from(route('departements.create'))
        ->post(route('departements.store'), [
            'code' => 'DIR_KO',
            'nom' => 'Direction mal rattachée',
            'type' => Departement::TYPE_DIRECTION,
            'parent_id' => $autre->id,
        ])->assertSessionHasErrors('parent_id');
});

test('un département sans parent est refusé', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)
        ->from(route('departements.create'))
        ->post(route('departements.store'), [
            'code' => 'DEP_KO',
            'nom' => 'Département sans direction',
            'type' => Departement::TYPE_DEPARTEMENT,
        ])->assertSessionHasErrors('parent_id');
});
