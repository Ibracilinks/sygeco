<?php

use App\Models\Activite;
use App\Models\ActiviteEvaluation;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Services\DashboardDataService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test("le camembert d'exécution répartit les activités validées et isole les non évaluées", function () {
    $exercice = Exercice::factory()->actif()->create();
    $departement = Departement::factory()->create();

    $objectif = Objectif::factory()->create(['exercice_id' => $exercice->id, 'annee' => $exercice->annee]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    $creer = fn (string $statut) => Activite::factory()
        ->pourExtrant($extrant)
        ->pourDepartement($departement)
        ->create(['statut' => $statut]);

    $evaluer = function (Activite $activite, string $execution) {
        $activite->evaluations()->create([
            'periode' => ActiviteEvaluation::PERIODE_FIN_ANNEE,
            'statut_execution' => $execution,
        ]);
    };

    $evaluer($creer('valide'), 'realise');
    $evaluer($creer('valide'), 'realise');
    $evaluer($creer('valide'), 'en_cours');
    $evaluer($creer('valide'), 'non_realise');
    $creer('valide');            // validée mais non évaluée
    $creer('en_attente');        // hors périmètre : non validée

    $repartition = (new DashboardDataService($exercice->annee))->getExecutionActivitesValidees();

    expect($repartition)->toBe([
        'Réalisée' => 2,
        'En cours de réalisation' => 1,
        'Non réalisée' => 1,
        'Non évaluée' => 1,
    ]);
});
