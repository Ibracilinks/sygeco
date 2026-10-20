<?php

use App\Models\Activite;
use App\Models\Departement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Reprise de l'existant : les activités en attente saisies par un service sont
| rattachées à l'entité qui le chapeaute (hors DG), comme à la soumission.
*/

test('la commande rattache les activités en attente des services à leur entité', function () {
    $dg = Departement::factory()->direction()->create();
    $dc = Departement::factory()->departement()->enfantDe($dg)->create(['nom' => 'DBCGOQ']);
    $service = Departement::factory()->service()->enfantDe($dc)->create(['nom' => 'Service Qualité']);
    $agence = Departement::factory()->agenceComptable()->enfantDe($dg)->create(['nom' => 'Agence Comptable']);
    $serviceAgence = Departement::factory()->service()->enfantDe($agence)->create(['nom' => 'Service Recettes']);
    $serviceRattache = Departement::factory()->service()->enfantDe($dg)->create();

    $aRattacher = Activite::factory()->enAttente()->pourDepartement($service)->create();
    $aRattacherAgence = Activite::factory()->enAttente()->pourDepartement($serviceAgence)->create();
    // Ne bougent pas : brouillon, validée, service rattaché à la DG, entité de premier niveau.
    $brouillon = Activite::factory()->brouillon()->pourDepartement($service)->create();
    $validee = Activite::factory()->valide()->pourDepartement($service)->create();
    $rattacheDg = Activite::factory()->enAttente()->pourDepartement($serviceRattache)->create();
    $direction = Activite::factory()->enAttente()->pourDepartement($dc)->create();

    // Simulation : rien ne change.
    $this->artisan('activites:reattribuer-entite', ['--dry-run' => true])
        ->expectsOutputToContain('Simulation : 2 activité(s)')
        ->assertSuccessful();
    expect($aRattacher->fresh()->departement_id)->toBe($service->id);

    $this->artisan('activites:reattribuer-entite')
        ->expectsConfirmation('Rattacher ces 2 activité(s) ?', 'yes')
        ->assertSuccessful();

    expect($aRattacher->fresh()->departement_id)->toBe($dc->id)
        ->and($aRattacher->fresh()->departements->pluck('id')->all())->toContain($service->id)
        ->and($aRattacherAgence->fresh()->departement_id)->toBe($agence->id)
        ->and($aRattacher->validationHistoriques()->where('action', 'reattribution')->value('commentaire'))
        ->toBe('Structure : Service Qualité → DBCGOQ (reprise des activités en attente)');

    foreach ([$brouillon, $validee, $rattacheDg, $direction] as $inchangee) {
        expect($inchangee->fresh()->departement_id)->toBe($inchangee->departement_id);
    }

    // Relancée, elle n'a plus rien à faire.
    $this->artisan('activites:reattribuer-entite')
        ->expectsOutputToContain('Aucune activité en attente à rattacher')
        ->assertSuccessful();
});
