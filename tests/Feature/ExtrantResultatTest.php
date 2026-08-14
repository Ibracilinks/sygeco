<?php

use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('créer un extrant le rattache au résultat et dérive automatiquement l\'objectif', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['statut' => 'actif']);
    $resultat = Resultat::factory()->create(['objectif_id' => $objectif->id, 'is_active' => true]);

    $this->actingAs($admin)->post(route('extrants.store'), [
        'resultat_id' => $resultat->id,
        'code' => 'EXT_RES_1',
        'libelle' => 'Extrant rattaché à un résultat',
        'is_active' => 1,
    ])->assertRedirect(route('extrants.index'));

    $extrant = Extrant::where('code', 'EXT_RES_1')->first();

    expect($extrant)->not->toBeNull();
    expect((int) $extrant->resultat_id)->toBe($resultat->id);
    expect((int) $extrant->objectif_id)->toBe($objectif->id);
});

test('créer un extrant sans résultat est refusé', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)
        ->from(route('extrants.create'))
        ->post(route('extrants.store'), [
            'code' => 'EXT_KO',
            'libelle' => 'Extrant sans résultat',
        ])->assertSessionHasErrors('resultat_id');

    expect(Extrant::where('code', 'EXT_KO')->exists())->toBeFalse();
});
