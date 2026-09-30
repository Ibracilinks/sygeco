<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Objectifs pluriannuels
|--------------------------------------------------------------------------
| Un objectif couvre un ou plusieurs exercices (ex. plan stratégique 2026-2030).
| Le rattachement passe par le pivot `exercice_objectif` ; l'exercice d'exécution
| d'une activité est porté par l'activité elle-même.
*/

/**
 * @return Collection<int, Exercice>
 */
function exercicesDe(int $debut, int $fin)
{
    return collect(range($debut, $fin))
        ->map(fn ($annee) => Exercice::factory()->create(['annee' => $annee]));
}

test('la création accepte plusieurs exercices et renseigne le pivot', function () {
    $admin = userWithRole('dbcgoq');
    $exercices = exercicesDe(2026, 2030);

    $this->actingAs($admin)->post(route('objectifs.store'), [
        'exercice_ids' => $exercices->pluck('id')->all(),
        'code' => 'PS_2026_2030',
        'libelle' => 'Plan stratégique 2026-2030',
        'statut' => 'actif',
        'ordre' => 1,
    ])->assertRedirect(route('objectifs.index'));

    $objectif = Objectif::where('code', 'PS_2026_2030')->firstOrFail();

    expect($objectif->exercices)->toHaveCount(5);
    // L'année de départ est dérivée du plus ancien exercice couvert.
    expect($objectif->annee)->toBe(2026);
    expect($objectif->periode_libelle)->toBe('2026-2030');
});

test('la création exige au moins un exercice', function () {
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->post(route('objectifs.store'), [
        'code' => 'PS_VIDE',
        'libelle' => 'Sans exercice',
        'statut' => 'actif',
    ])->assertSessionHasErrors('exercice_ids');

    expect(Objectif::where('code', 'PS_VIDE')->exists())->toBeFalse();
});

test('la mise à jour remplace la période couverte', function () {
    $admin = userWithRole('dbcgoq');
    $exercices = exercicesDe(2026, 2030);
    $objectif = Objectif::factory()->pourExercices($exercices->all())->create(['code' => 'PS_MAJ']);

    // On resserre le plan sur 2026-2027.
    $this->actingAs($admin)->put(route('objectifs.update', $objectif), [
        'exercice_ids' => $exercices->take(2)->pluck('id')->all(),
        'code' => 'PS_MAJ',
        'libelle' => $objectif->libelle,
        'statut' => 'actif',
        'ordre' => 1,
    ])->assertRedirect(route('objectifs.index'));

    expect($objectif->fresh()->exercices->pluck('annee')->sort()->values()->all())->toBe([2026, 2027]);
});

test('un objectif pluriannuel est listé sous chacune de ses années', function () {
    $admin = userWithRole('dbcgoq');
    $exercices = exercicesDe(2026, 2030);
    Objectif::factory()->pourExercices($exercices->all())->create([
        'code' => 'PS_LISTE',
        'libelle' => 'Plan stratégique quinquennal',
        'statut' => 'actif',
    ]);

    foreach ([2026, 2028, 2030] as $annee) {
        $exerciceId = $exercices->firstWhere('annee', $annee)->id;

        $this->actingAs($admin)->get(route('objectifs.index', ['exercice_id' => $exerciceId]))
            ->assertOk()
            ->assertSee('PS_LISTE');
    }
});

test('les activités d\'un objectif pluriannuel restent comptées dans leur seul exercice', function () {
    $exercices = exercicesDe(2026, 2030);
    $objectif = Objectif::factory()->pourExercices($exercices->all())->create(['code' => 'PS_ACT']);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();
    $departement = Departement::factory()->create();

    $e2026 = $exercices->firstWhere('annee', 2026);
    $e2027 = $exercices->firstWhere('annee', 2027);

    Activite::factory()->count(2)->pourExtrant($extrant)->pourDepartement($departement)
        ->pourExercice($e2026)->create();
    Activite::factory()->count(3)->pourExtrant($extrant)->pourDepartement($departement)
        ->pourExercice($e2027)->create();

    expect(Activite::forExercice($e2026->id)->count())->toBe(2);
    expect(Activite::forExercice($e2027->id)->count())->toBe(3);
    // Sans le rattachement porté par l'activité, les 5 seraient comptées des deux côtés.
    expect(Activite::forExercice($exercices->firstWhere('annee', 2030)->id)->count())->toBe(0);
});

test('une activité créée hérite de l\'exercice actif, pas de celui de l\'objectif', function () {
    $exercices = exercicesDe(2026, 2030);
    $actif = $exercices->firstWhere('annee', 2028);
    $actif->update(['statut' => 'actif']);

    $objectif = Objectif::factory()->pourExercices($exercices->all())->create(['code' => 'PS_CREA']);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();
    $departement = Departement::factory()->create();

    $chef = userWithRole('responsable-programme', ['departement_id' => $departement->id]);

    $this->actingAs($chef)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $departement->id,
        'nom_activite' => 'Activité pluriannuelle',
        'indicateur_objectivement_verifiable' => 'IND',
        'moyen_verification' => 'Rapport',
        'cout' => 1000000,
        'trimestre_1' => 'on',
    ])->assertRedirect(route('activites.index'));

    $activite = Activite::where('nom_activite', 'Activité pluriannuelle')->firstOrFail();

    expect($activite->exercice_id)->toBe($actif->id);
    expect($activite->exercice()->annee)->toBe(2028);
});
