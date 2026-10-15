<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Exports Excel du cadre logique
|--------------------------------------------------------------------------
| Vrais classeurs .xlsx : en-tête figé, montants numériques, totaux.
*/

/**
 * Exercice actif avec deux activités validées sous un même extrant.
 */
function cadreLogiqueExporte(): Exercice
{
    $exercice = Exercice::factory()->actif()->create(['annee' => 2026]);
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['libelle' => 'Objectif exporté', 'description' => null]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create(['code' => 'RS.I', 'libelle' => 'Résultat exporté']);
    $extrant = Extrant::factory()->forResultat($resultat)->create(['code' => 'Extrant 1.1', 'libelle' => 'Extrant exporté']);

    $structure = Departement::factory()->departement()->create(['code' => 'DBCGOQ', 'nom' => 'Direction du Budget et du Contrôle de Gestion']);

    foreach (['Atelier de lancement' => 1_500_000, 'Mission de supervision' => 2_500_000] as $nom => $cout) {
        Activite::factory()->pourExtrant($extrant)->pourExercice($exercice)->pourDepartement($structure)->valide()->tousTrimestres()
            ->create(['nom_activite' => $nom, 'cout' => $cout, 'pour_memoire' => false]);
    }

    return $exercice;
}

function feuilleExportee(TestResponse $response): Worksheet
{
    $response->assertOk()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $fichier = tempnam(sys_get_temp_dir(), 'xlsx');
    file_put_contents($fichier, $response->streamedContent());

    return IOFactory::load($fichier)->getActiveSheet();
}

/**
 * Valeurs de la feuille, cellule par cellule, pour y chercher un libellé ou un montant.
 *
 * @return array<int, mixed>
 */
function valeursFeuille(Worksheet $feuille): array
{
    // Les montants relus sont des nombres : on les ramène à des flottants pour comparer.
    return collect($feuille->toArray(null, false, false))->flatten()
        ->filter(fn ($v) => $v !== null && $v !== '')
        ->map(fn ($v) => is_int($v) ? (float) $v : $v)
        ->values()->all();
}

test('le PTA d\'un exercice est un classeur xlsx avec montants et totaux', function () {
    $exercice = cadreLogiqueExporte();

    $feuille = feuilleExportee($this->actingAs(userWithRole('dbcgoq'))->get(route('exercices.export', $exercice)));
    $valeurs = valeursFeuille($feuille);

    expect($valeurs)->toContain('PLAN DE TRAVAIL ANNUEL — EXERCICE 2026')
        ->toContain('OBJECTIF STRATÉGIQUE : Objectif exporté')
        ->toContain('RS.I Résultat exporté')
        ->toContain('Atelier de lancement')
        ->toContain('Sous-total RS.I')
        // Les responsables apparaissent par leur code, pas par leur nom complet.
        ->toContain('DBCGOQ')
        ->not->toContain('Direction du Budget et du Contrôle de Gestion')
        ->toContain(4_000_000.0)
        // Montants numériques, au format milliers.
        ->and($feuille->getStyle('J9')->getNumberFormat()->getFormatCode())->toBe('#,##0')
        ->and($feuille->getFreezePane())->toBe('A6');
});

test('la programmation s\'exporte avec les filtres de l\'écran', function () {
    $exercice = cadreLogiqueExporte();
    $admin = userWithRole('dbcgoq');

    $this->actingAs($admin)->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.index'))->assertSee('Exporter (Cadre logique)');

    $feuille = feuilleExportee($this->actingAs($admin)->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('activites.export', ['search' => 'Atelier'])));
    $valeurs = valeursFeuille($feuille);

    expect($valeurs)->toContain('Atelier de lancement')
        ->not->toContain('Mission de supervision')
        ->toContain(1_500_000.0);
});

test('l\'arbitrage et l\'évaluation s\'exportent en xlsx', function () {
    $exercice = cadreLogiqueExporte();
    $admin = userWithRole('dbcgoq');

    $arbitrage = valeursFeuille(feuilleExportee($this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])->get(route('validations.exporter'))));
    $evaluation = valeursFeuille(feuilleExportee($this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])->get(route('evaluations.export', 'mi-parcours'))));

    expect($arbitrage)->toContain('Mission de supervision')->toContain(4_000_000.0)
        ->and($evaluation)->toContain('Mission de supervision')->toContain('OBSERVATIONS');
});
