<?php

use App\Models\Activite;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Résultats et extrants appartiennent au couple « exercice + objectif » : rattacher
| un objectif à un nouvel exercice recrée ses résultats et extrants pour cet
| exercice, sans déplacer ceux de l'exercice d'origine.
*/

test('rattacher un objectif à un nouvel exercice recopie ses résultats et extrants sans les déplacer', function () {
    $admin = userWithRole('dbcgoq');
    $exercice2026 = Exercice::factory()->create(['annee' => 2026]);
    $exercice2027 = Exercice::factory()->create(['annee' => 2027]);

    $objectif = Objectif::factory()->pourExercice($exercice2026)->create(['code' => 'OG_1']);
    $resultat = Resultat::factory()->forObjectif($objectif)->create(['code' => 'RS.I']);
    $extrant = Extrant::factory()->forResultat($resultat)->create(['code' => 'EXT_1']);
    Activite::factory()->create(['extrant_id' => $extrant->id, 'exercice_id' => $exercice2026->id]);

    $this->actingAs($admin)->put(route('objectifs.update', $objectif), [
        'exercice_ids' => [$exercice2026->id, $exercice2027->id],
        'code' => 'OG_1',
        'libelle' => $objectif->libelle,
        'statut' => 'actif',
        'ordre' => 1,
    ])->assertRedirect(route('objectifs.index'));

    // L'original reste dans 2026, avec son activité.
    expect($resultat->fresh()->exercice_id)->toBe($exercice2026->id);
    expect($extrant->fresh()->exercice_id)->toBe($exercice2026->id);
    expect($extrant->activites()->count())->toBe(1);

    // 2027 reçoit sa propre copie, sans activité.
    $copie = Resultat::forExercice($exercice2027->id)->where('objectif_id', $objectif->id)->sole();
    expect($copie->id)->not->toBe($resultat->id);
    expect($copie->code)->toBe('RS.I');

    $copieExtrant = Extrant::forExercice($exercice2027->id)->sole();
    expect($copieExtrant->resultat_id)->toBe($copie->id);
    expect($copieExtrant->code)->toBe('EXT_1');
    expect($copieExtrant->activites()->count())->toBe(0);
});

test('la fiche d\'un exercice ne compte que ses propres résultats et extrants', function () {
    $admin = userWithRole('dbcgoq');
    $exercice2026 = Exercice::factory()->create(['annee' => 2026]);
    $exercice2027 = Exercice::factory()->create(['annee' => 2027]);

    $objectif = Objectif::factory()->pourExercices([$exercice2026, $exercice2027])->create();
    $resultat = Resultat::factory()->forObjectif($objectif)->pourExercice($exercice2026)->create();
    Extrant::factory()->forResultat($resultat)->create();

    $this->actingAs($admin)->get(route('exercices.show', $exercice2027))
        ->assertOk()
        ->assertViewHas('stats', fn ($stats) => $stats['nb_resultats'] === 0 && $stats['nb_extrants'] === 0);
});
