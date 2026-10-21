<?php

use App\Models\Departement;
use App\Models\Mission;
use App\Models\User;
use App\Support\AnalyseMissions;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Analyse des missions : toutes les missions, statistiques et doublons potentiels,
| pour la DBCGOQ, le contrôle de gestion et la cellule planification.
*/

/**
 * Mission minimale avec ses participants.
 *
 * @param  array<int, string>  $participants
 */
function missionAnalysee(string $reference, string $depart, string $retour, array $participants, array $attributs = []): Mission
{
    $mission = Mission::query()->forceCreate($attributs + [
        'reference' => $reference,
        'type' => Mission::TYPE_REGION,
        'objet' => 'Mission '.$reference,
        'date_document' => $depart,
        'date_depart' => $depart,
        'date_retour' => $retour,
        'nombre_jours' => 3,
        'montant_total' => 100000,
        'statut' => 'finalise',
        'cree_par' => User::factory()->create()->id,
    ]);

    foreach (array_values($participants) as $ordre => $nom) {
        $mission->participants()->create(['ordre' => $ordre + 1, 'nom_complet' => $nom]);
    }

    return $mission->load(['participants', 'departements', 'departement']);
}

test('une même personne sur deux missions simultanées est un doublon potentiel fort', function () {
    $a = missionAnalysee('A-1', '2026-03-02', '2026-03-05', ['Moussa  Traoré', 'Awa Diallo']);
    $b = missionAnalysee('B-1', '2026-03-04', '2026-03-08', ['MOUSSA TRAORE']);
    // Mêmes personnes, mais périodes disjointes : pas de doublon.
    $c = missionAnalysee('C-1', '2026-04-01', '2026-04-03', ['Moussa Traoré']);

    $doublons = (new AnalyseMissions(collect([$a, $b, $c])))->doublons();

    expect($doublons)->toHaveCount(1)
        ->and($doublons[0]['niveau'])->toBe('fort')
        ->and(collect($doublons[0]['missions'])->pluck('reference')->all())->toBe(['A-1', 'B-1'])
        ->and($doublons[0]['detail'])->toBe('Moussa  Traoré');
});

test('même objet, même type et même date de départ : saisie en double', function () {
    $a = missionAnalysee('A-2', '2026-05-04', '2026-05-06', ['Awa Diallo'], ['objet' => 'Supervision de Kayes']);
    $b = missionAnalysee('B-2', '2026-05-04', '2026-05-06', ['Fatoumata Koné'], ['objet' => 'supervision de kayes']);

    $doublons = (new AnalyseMissions(collect([$a, $b])))->doublons();

    expect($doublons)->toHaveCount(1)
        ->and($doublons[0]['motif'])->toBe('Même objet, même type, même date de départ');
});

test('les statistiques comptent missions, budget, participants et jours-personnes', function () {
    $a = missionAnalysee('A-3', '2026-01-10', '2026-01-12', ['Awa Diallo', 'Moussa Traoré']);
    $b = missionAnalysee('B-3', '2026-02-10', '2026-02-12', ['Fatoumata Koné'], ['type' => Mission::TYPE_MEME_VILLE, 'statut' => 'brouillon']);

    $stats = (new AnalyseMissions(collect([$a, $b])))->statistiques();

    expect($stats['nombre'])->toBe(2)
        ->and($stats['budget'])->toBe(200000.0)
        ->and($stats['participants'])->toBe(3)
        ->and($stats['jours_personnes'])->toBe(3 * 2 + 3 * 1)
        ->and($stats['par_type'][Mission::TYPE_REGION]['nombre'])->toBe(1)
        ->and($stats['par_mois'][1])->toBe(1)
        ->and($stats['brouillons'])->toBe(1);
});

test('la page est ouverte à la DBCGOQ, au contrôle de gestion et à la cellule planification, fiches comprises', function (string $role) {
    $user = userWithRole($role, ['departement_id' => Departement::factory()->create()->id]);
    $mission = missionAnalysee('ANA-1', now()->year.'-03-02', now()->year.'-03-05', ['Awa Diallo']);

    $this->actingAs($user)->get(route('missions.analyse'))->assertOk()->assertSee('ANA-1');
    $this->actingAs($user)->get(route('dashboard'))->assertSee(route('missions.analyse'));
    // La fiche s'ouvre en lecture seule depuis l'analyse.
    $this->actingAs($user)->get(route('missions.show', $mission))->assertOk();
})->with(['dbcgoq', 'service-controle-gestion', 'agent-planification']);

test('la page est refusée aux autres profils', function (string $role) {
    $this->actingAs(userWithRole($role))->get(route('missions.analyse'))->assertForbidden();
})->with(['responsable-programme', 'chef-service', 'suivi-evaluation', 'service-budget']);

test('le contrôle de gestion ne voit ni l\'arbitrage ni le journal dans son menu', function () {
    $user = userWithRole('service-controle-gestion', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route('dashboard'))->assertOk()
        ->assertSee(route('budget.analysis'))
        ->assertDontSee(route('validations.index'))
        ->assertDontSee(route('journal.index'));
});
