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

test('un service rattaché directement à une direction est accepté (service rattaché)', function () {
    $admin = userWithRole('dbcgoq');
    $direction = Departement::factory()->direction()->create();

    $this->actingAs($admin)
        ->from(route('departements.create'))
        ->post(route('departements.store'), [
            'code' => 'SRV_RATTACHE',
            'nom' => 'Service rattaché à la DG',
            'type' => Departement::TYPE_SERVICE,
            'parent_id' => $direction->id,
        ])->assertRedirect(route('departements.index'));

    $service = Departement::where('code', 'SRV_RATTACHE')->first();
    expect($service)->not->toBeNull();
    expect($service->type)->toBe(Departement::TYPE_SERVICE);
    expect($service->parent->is($direction))->toBeTrue();
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

test('l\'index affiche l\'organigramme jusqu\'aux services', function () {
    $admin = userWithRole('dbcgoq');
    $direction = Departement::factory()->direction()->create(['nom' => 'Direction Alpha']);
    $departement = Departement::factory()->departement()->enfantDe($direction)->create(['nom' => 'Département Beta']);
    $service = Departement::factory()->service()->enfantDe($departement)->create(['nom' => 'Service Gamma']);

    $this->actingAs($admin)->get(route('departements.index'))
        ->assertOk()
        ->assertSee('Organigramme')
        ->assertSee('Direction Alpha')
        ->assertSee('Service Gamma');
});

test('la fiche affiche le fil d\'ariane et les sous-entités', function () {
    $admin = userWithRole('dbcgoq');
    $direction = Departement::factory()->direction()->create(['nom' => 'Direction Alpha']);
    $departement = Departement::factory()->departement()->enfantDe($direction)->create(['nom' => 'Département Beta']);
    $service = Departement::factory()->service()->enfantDe($departement)->create(['nom' => 'Service Gamma']);

    // Fiche du département : montre la direction parente (ariane) et le service enfant.
    $this->actingAs($admin)->get(route('departements.show', $departement))
        ->assertOk()
        ->assertSee('Direction Alpha')
        ->assertSee('Sous-entités')
        ->assertSee('Service Gamma');
});

test('l\'agence comptable et un bureau régional se rattachent à la Direction Générale', function () {
    $admin = userWithRole('dbcgoq');
    $dg = Departement::factory()->direction()->create(['nom' => 'Direction Générale']);

    foreach ([
        ['AC', 'Agence Comptable', Departement::TYPE_AGENCE_COMPTABLE],
        ['BR_BKO', 'Bureau Régional de Bamako', Departement::TYPE_BUREAU_REGIONAL],
    ] as [$code, $nom, $type]) {
        $this->actingAs($admin)
            ->from(route('departements.create'))
            ->post(route('departements.store'), [
                'code' => $code,
                'nom' => $nom,
                'type' => $type,
                'parent_id' => $dg->id,
            ])->assertRedirect(route('departements.index'));

        $entite = Departement::where('code', $code)->first();
        expect($entite->type)->toBe($type);
        expect($entite->parent->is($dg))->toBeTrue();
        expect($entite->estRattacheeDg())->toBeTrue();
        expect($entite->peutAvoirDesEnfants())->toBeFalse();
    }
});

test('un bureau régional rattaché à une direction centrale est refusé', function () {
    $admin = userWithRole('dbcgoq');
    $dg = Departement::factory()->direction()->create();
    $directionCentrale = Departement::factory()->departement()->enfantDe($dg)->create();

    $this->actingAs($admin)
        ->from(route('departements.create'))
        ->post(route('departements.store'), [
            'code' => 'BR_KO',
            'nom' => 'Bureau mal rattaché',
            'type' => Departement::TYPE_BUREAU_REGIONAL,
            'parent_id' => $directionCentrale->id,
        ])->assertSessionHasErrors('parent_id');
});

test('un service ne peut pas être rattaché à un bureau régional', function () {
    $admin = userWithRole('dbcgoq');
    $dg = Departement::factory()->direction()->create();
    $bureau = Departement::factory()->bureauRegional()->enfantDe($dg)->create();

    $this->actingAs($admin)
        ->from(route('departements.create'))
        ->post(route('departements.store'), [
            'code' => 'SRV_KO',
            'nom' => 'Service sous bureau régional',
            'type' => Departement::TYPE_SERVICE,
            'parent_id' => $bureau->id,
        ])->assertSessionHasErrors('parent_id');
});

test('une seconde Direction Générale est refusée', function () {
    $admin = userWithRole('dbcgoq');
    Departement::factory()->direction()->create(['nom' => 'Direction Générale']);

    $this->actingAs($admin)
        ->from(route('departements.create'))
        ->post(route('departements.store'), [
            'code' => 'DG_BIS',
            'nom' => 'Seconde Direction Générale',
            'type' => Departement::TYPE_DIRECTION,
        ])->assertSessionHasErrors('type');
});

test('la Direction Générale ne figure pas parmi les entités qui formulent des activités', function () {
    $dg = Departement::factory()->direction()->create(['nom' => 'Direction Générale']);
    $directionCentrale = Departement::factory()->departement()->enfantDe($dg)->create();
    $bureau = Departement::factory()->bureauRegional()->enfantDe($dg)->create();

    $formulatrices = Departement::query()->formulatrices()->pluck('id');

    expect($formulatrices)->not->toContain($dg->id)
        ->and($formulatrices)->toContain($directionCentrale->id)
        ->and($formulatrices)->toContain($bureau->id);
});
