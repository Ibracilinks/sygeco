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

test('un objectif pluriannuel propage le conflit à tous ses exercices', function () {
    $admin = userWithRole('dbcgoq');

    // Plan 2026-2028 : son extrant EXT_001 occupe le code sur les trois exercices.
    $exercices = collect([2026, 2027, 2028])
        ->map(fn ($annee) => Exercice::factory()->create(['annee' => $annee]));
    $plan = Objectif::factory()->pourExercices($exercices->all())->create(['code' => 'PS_PLAN']);
    $resultatPlan = Resultat::factory()->forObjectif($plan)->create();
    Extrant::factory()->forResultat($resultatPlan)->create(['code' => 'EXT_001']);

    // Objectif annuel 2027 : chevauche la période du plan → conflit.
    $annuel = Objectif::factory()->pourExercice($exercices->firstWhere('annee', 2027))->create(['code' => 'OG_2027']);
    $resultatAnnuel = Resultat::factory()->forObjectif($annuel)->create();

    $this->actingAs($admin)->post(route('extrants.store'), [
        'resultat_id' => $resultatAnnuel->id,
        'code' => 'EXT_001',
        'libelle' => 'Chevauche le plan pluriannuel',
        'is_active' => 1,
    ])->assertSessionHasErrors('code');

    // Objectif annuel 2030 : hors période du plan → accepté.
    $horsPlan = Objectif::factory()->pourAnnee(2030)->create(['code' => 'OG_2030']);
    $resultatHorsPlan = Resultat::factory()->forObjectif($horsPlan)->create();

    $this->actingAs($admin)->post(route('extrants.store'), [
        'resultat_id' => $resultatHorsPlan->id,
        'code' => 'EXT_001',
        'libelle' => 'Hors période du plan',
        'is_active' => 1,
    ])->assertRedirect(route('extrants.index'))->assertSessionHasNoErrors();
});
