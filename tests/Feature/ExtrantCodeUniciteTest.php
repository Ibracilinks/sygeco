<?php

use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Unicité du code d'extrant : par exercice, pas globale
|--------------------------------------------------------------------------
| La nomenclature (« EXT_001 », « Extrant 1.1 ») est réutilisée d'un exercice à
| l'autre : le même code doit pouvoir exister dans deux PTA différents, mais pas
| deux fois dans le même.
*/

/**
 * Chaîne exercice → objectif → résultat, prête à recevoir des extrants.
 */
function resultatPourAnnee(int $annee, string $codeObjectif): Resultat
{
    $exercice = Exercice::factory()->create(['annee' => $annee]);
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['code' => $codeObjectif]);

    return Resultat::factory()->forObjectif($objectif)->create();
}

test('le même code d\'extrant est accepté dans deux exercices différents', function () {
    $admin = userWithRole('dbcgoq');

    $resultat2026 = resultatPourAnnee(2026, 'OG_2026');
    $resultat2027 = resultatPourAnnee(2027, 'OG_2027');

    foreach ([$resultat2026, $resultat2027] as $resultat) {
        $this->actingAs($admin)->post(route('extrants.store'), [
            'resultat_id' => $resultat->id,
            'code' => 'EXT_001',
            'libelle' => 'Extrant réutilisé d\'un exercice à l\'autre',
            'is_active' => 1,
        ])->assertRedirect(route('extrants.index'))->assertSessionHasNoErrors();
    }

    expect(Extrant::where('code', 'EXT_001')->count())->toBe(2);
});

test('le même code d\'extrant est refusé deux fois dans le même exercice', function () {
    $admin = userWithRole('dbcgoq');

    $resultat = resultatPourAnnee(2026, 'OG_2026');
    // Second résultat du même objectif : même exercice, autre branche du cadre logique.
    $autreResultat = Resultat::factory()->forObjectif($resultat->objectif)->create();

    Extrant::factory()->forResultat($resultat)->create(['code' => 'EXT_001']);

    $this->actingAs($admin)->post(route('extrants.store'), [
        'resultat_id' => $autreResultat->id,
        'code' => 'EXT_001',
        'libelle' => 'Doublon dans le même PTA',
        'is_active' => 1,
    ])->assertSessionHasErrors('code');

    expect(Extrant::where('code', 'EXT_001')->count())->toBe(1);
});

test('la mise à jour d\'un extrant ne se heurte pas à son propre code', function () {
    $admin = userWithRole('dbcgoq');

    $resultat = resultatPourAnnee(2026, 'OG_2026');
    $extrant = Extrant::factory()->forResultat($resultat)->create(['code' => 'EXT_001']);

    $this->actingAs($admin)->put(route('extrants.update', $extrant), [
        'resultat_id' => $resultat->id,
        'code' => 'EXT_001',
        'libelle' => 'Libellé corrigé',
        'is_active' => 1,
    ])->assertRedirect(route('extrants.index'))->assertSessionHasNoErrors();

    expect($extrant->fresh()->libelle)->toBe('Libellé corrigé');
});

test('un objectif pluriannuel porte des extrants distincts par exercice', function () {
    $admin = userWithRole('dbcgoq');

    // Plan 2026-2027 : EXT_001 n'occupe le code que dans l'exercice de son résultat.
    $exercices = collect([2026, 2027])
        ->map(fn ($annee) => Exercice::factory()->create(['annee' => $annee]));
    $plan = Objectif::factory()->pourExercices($exercices->all())->create(['code' => 'PS_PLAN']);
    $resultat2026 = Resultat::factory()->forObjectif($plan)->pourExercice($exercices[0])->create();
    Extrant::factory()->forResultat($resultat2026)->create(['code' => 'EXT_001']);

    $resultat2027 = Resultat::factory()->forObjectif($plan)->pourExercice($exercices[1])->create();

    $this->actingAs($admin)->post(route('extrants.store'), [
        'resultat_id' => $resultat2026->id,
        'code' => 'EXT_001',
        'libelle' => 'Doublon en 2026',
        'is_active' => 1,
    ])->assertSessionHasErrors('code');

    $this->actingAs($admin)->post(route('extrants.store'), [
        'resultat_id' => $resultat2027->id,
        'code' => 'EXT_001',
        'libelle' => 'Même code en 2027',
        'is_active' => 1,
    ])->assertRedirect(route('extrants.index'))->assertSessionHasNoErrors();

    expect(Extrant::where('code', 'EXT_001')->where('exercice_id', $exercices[1]->id)->count())->toBe(1);
});
