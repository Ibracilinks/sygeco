<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Le responsable de programme voit tout le cadre logique de l'exercice
| (objectifs, résultats, extrants), même sans activité de son entité ;
| les activités affichées restent celles de son périmètre.
*/

test('le responsable de programme voit tout le cadre logique, mais seulement les activités de son périmètre', function () {
    $exercice = Exercice::factory()->actif()->create();
    $direction = Departement::factory()->departement()->create();
    $service = Departement::factory()->service()->enfantDe($direction)->create();
    $ailleurs = Departement::factory()->departement()->create();
    $responsable = userWithRole('responsable-programme', ['departement_id' => $direction->id]);

    $objectif = Objectif::factory()->pourExercice($exercice)->create(['code' => 'OG_TOUS', 'statut' => 'actif']);
    $resultat = Resultat::factory()->forObjectif($objectif)->create(['code' => 'RS_TOUS', 'is_active' => true]);
    $extrant = Extrant::factory()->forResultat($resultat)->create(['code' => 'EXT_TOUS', 'is_active' => true]);

    // Aucune activité de son entité : il voit tout de même le cadre logique.
    Activite::factory()->pourExtrant($extrant)->pourDepartement($ailleurs)->brouillon()->create(['nom_activite' => 'ACT_AILLEURS']);
    Activite::factory()->pourExtrant($extrant)->pourDepartement($service)->brouillon()->create(['nom_activite' => 'ACT_SERVICE']);

    $this->actingAs($responsable)->withSession([ActiveExercice::SESSION_KEY => $exercice->id]);

    $this->get(route('objectifs.index'))->assertOk()->assertSee('OG_TOUS');
    $this->get(route('resultats.index'))->assertOk()->assertSee('RS_TOUS');
    $this->get(route('extrants.index'))->assertOk()->assertSee('EXT_TOUS');

    // Les fiches ne comptent que l'activité de son service, pas celle d'une autre entité.
    foreach ([route('objectifs.show', $objectif), route('resultats.show', $resultat)] as $page) {
        $this->get($page)->assertOk()->assertViewHas('stats', fn ($stats) => $stats['nb_activites'] === 1);
    }

    $this->get(route('extrants.show', $extrant))->assertOk()
        ->assertSee('ACT_SERVICE')->assertDontSee('ACT_AILLEURS');
});
