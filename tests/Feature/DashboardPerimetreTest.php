<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Services\DashboardDataService;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Monte un extrant rattaché à l'exercice actif, seul point d'accroche exigé par
 * les jointures du tableau de bord (activites → extrants → objectifs).
 */
function extrantDeLExercice(Exercice $exercice): Extrant
{
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();

    return Extrant::factory()->forResultat($resultat)->create();
}

test('le tableau de bord ignore les activités mises à la corbeille', function () {
    $exercice = Exercice::factory()->actif()->create();
    ActiveExercice::set($exercice->id);
    $extrant = extrantDeLExercice($exercice);
    $departement = Departement::factory()->create();

    $creer = fn () => Activite::factory()->pourExtrant($extrant)->pourDepartement($departement)
        ->create(['cout' => 1_000_000]);

    $creer();
    $creer();
    $creer()->delete();

    $this->actingAs(userWithRole('superadmin'));
    $kpis = (new DashboardDataService)->getDashboardPayload()['kpis'];

    // Le KPI et le budget s'alignent sur la page Programmation, qui exclut la corbeille.
    expect($kpis['activites'])->toBe(2)
        ->and($kpis['resultats_strategiques'])->toBe(1)
        ->and((float) $kpis['budget_total'])->toBe(2_000_000.0);
});

test("le tableau de bord d'un chef se limite à son sous-arbre", function () {
    $exercice = Exercice::factory()->actif()->create();
    ActiveExercice::set($exercice->id);
    $extrant = extrantDeLExercice($exercice);

    $direction = Departement::factory()->direction()->create();
    $service = Departement::factory()->service()->enfantDe($direction)->create();
    $autreDirection = Departement::factory()->direction()->create();

    $creer = fn (Departement $d) => Activite::factory()->pourExtrant($extrant)->pourDepartement($d)
        ->create(['cout' => 1_000_000]);

    $creer($direction);
    $creer($service);
    $creer($autreDirection);

    $chef = userWithRole('responsable-programme', ['departement_id' => $direction->id]);
    $this->actingAs($chef);

    $kpis = (new DashboardDataService)->getDashboardPayload()['kpis'];

    // Son entité et son service, jamais la direction voisine.
    expect($kpis['activites'])->toBe(2)
        ->and((float) $kpis['budget_total'])->toBe(2_000_000.0);
});

test('le cache du tableau de bord ne mélange pas les périmètres', function () {
    $exercice = Exercice::factory()->actif()->create();
    ActiveExercice::set($exercice->id);
    $extrant = extrantDeLExercice($exercice);

    $direction = Departement::factory()->direction()->create();
    $autreDirection = Departement::factory()->direction()->create();

    Activite::factory()->pourExtrant($extrant)->pourDepartement($direction)->create();
    Activite::factory()->count(3)->pourExtrant($extrant)->pourDepartement($autreDirection)->create();

    // Le superadmin passe en premier : sa vue globale ne doit pas être resservie au chef.
    $this->actingAs(userWithRole('superadmin'));
    expect((new DashboardDataService)->getDashboardPayload()['kpis']['activites'])->toBe(4);

    $this->actingAs(userWithRole('responsable-programme', ['departement_id' => $direction->id]));
    expect((new DashboardDataService)->getDashboardPayload()['kpis']['activites'])->toBe(1);
});
