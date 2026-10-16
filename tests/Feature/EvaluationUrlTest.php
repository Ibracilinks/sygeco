<?php

use App\Models\Activite;
use App\Models\ActiviteEvaluation;
use App\Models\Exercice;
use App\Support\ActiveExercice;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('les liens et la saisie des évaluations conservent le protocole du navigateur', function (string $periode, string $scheme) {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $activite = Activite::factory()->pourExercice($exercice)->valide()->tousTrimestres()->create();
    $pageUrl = new Uri("{$scheme}://sygeco.test/evaluations/{$periode}");

    // Le navigateur peut être en HTTPS alors que le serveur reçoit du HTTP du proxy.
    // L'exercice est épinglé : les fabriques peuvent créer un autre exercice « actif ».
    $response = $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get("http://sygeco.test/evaluations/{$periode}?statut_execution=non_evaluee", [
            'X-Forwarded-Proto' => $scheme,
        ])
        ->assertOk()
        ->assertSee('action="/evaluations/'.$periode.'"', false)
        ->assertSee('href="/evaluations/'.$periode.'/export?statut_execution=non_evaluee"', false)
        ->assertSee('href="/activites/'.$activite->id.'"', false)
        ->assertSee('action="/activites/non-programmee"', false);

    // Lire la destination réellement fournie au bouton, encodée par Js::from.
    $html = html_entity_decode($response->getContent(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    expect(preg_match("/openEvaluationModal\(JSON\.parse\('(.*?)'\)\)/", $html, $matches))->toBe(1);
    $json = json_decode('"'.$matches[1].'"', true, 512, JSON_THROW_ON_ERROR);
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    $destination = UriResolver::resolve($pageUrl, new Uri($data['action']));

    expect($destination->getScheme())->toBe($scheme)
        ->and($destination->getAuthority())->toBe($pageUrl->getAuthority())
        ->and($destination->getPath())->toBe("/evaluations/{$activite->id}/{$periode}");

    $this->postJson((string) $destination, [
        'statut_execution' => 'realise',
        'observation' => 'Évaluation enregistrée depuis la page.',
        'valeur_indicateur' => 'Rapport produit',
    ])->assertOk()->assertJsonStructure(['message', 'ligne']);

    $this->assertDatabaseHas('activite_evaluations', [
        'activite_id' => $activite->id,
        'periode' => ActiviteEvaluation::periodeDepuisSlug($periode),
        'statut_execution' => 'realise',
        'observation' => 'Évaluation enregistrée depuis la page.',
        'valeur_indicateur' => 'Rapport produit',
    ]);
})->with(['mi-parcours', 'fin-annee'])->with(['http', 'https']);

test('l\'évaluation liste d\'abord les activités non évaluées, puis les évaluées', function () {
    $admin = userWithRole('dbcgoq');
    $exercice = Exercice::factory()->actif()->create();
    $periode = ActiviteEvaluation::PERIODE_MI_PARCOURS;

    // Créée en premier, l'activité évaluée viendrait en tête sans ce tri.
    $evaluee = Activite::factory()->pourExercice($exercice)->valide()->tousTrimestres()->create(['nom_activite' => 'ACT_EVALUEE']);
    $evaluee->evaluations()->create(['periode' => $periode, 'statut_execution' => 'realise']);
    Activite::factory()->pourExercice($exercice)->valide()->tousTrimestres()->create(['nom_activite' => 'ACT_A_EVALUER']);

    $this->actingAs($admin)
        ->withSession([ActiveExercice::SESSION_KEY => $exercice->id])
        ->get(route('evaluations.index', 'mi-parcours'))
        ->assertOk()
        ->assertSeeInOrder(['ACT_A_EVALUER', 'ACT_EVALUEE']);
});
