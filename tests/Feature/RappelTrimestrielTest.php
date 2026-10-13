<?php

use App\Models\Activite;
use App\Models\ActiviteEvaluation;
use App\Models\Departement;
use App\Models\Exercice;
use App\Models\User;
use App\Notifications\RappelTrimestrielActivitesNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Rappel de fin de trimestre
|--------------------------------------------------------------------------
| Les 5, 12, 19 et 26 du dernier mois du trimestre, chaque compte d'une structure
| reçoit (mail + in-app) la liste de ses activités validées du trimestre qui ne
| sont pas encore évaluées « Réalisé ».
*/

function activiteDuT4(Exercice $exercice, Departement $departement, array $attributs = []): Activite
{
    return Activite::factory()->valide()->pourExercice($exercice)->pourDepartement($departement)->create(array_merge([
        'trimestre_1' => 'non', 'trimestre_2' => 'non', 'trimestre_3' => 'non', 'trimestre_4' => 'oui',
    ], $attributs));
}

test('les comptes de la structure porteuse reçoivent la liste de leurs activités non réalisées', function () {
    Notification::fake();
    $exercice = Exercice::factory()->actif()->create(['annee' => 2026]);
    $structure = Departement::factory()->create();
    $autre = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($structure)->create();
    $collegue = User::factory()->dansDepartement($structure)->create();
    $horsStructure = User::factory()->dansDepartement($autre)->create();

    activiteDuT4($exercice, $structure, ['nom_activite' => 'Atelier régional']);
    activiteDuT4($exercice, $structure, ['nom_activite' => 'Rapport de synthèse']);

    $this->artisan('activites:rappel-trimestriel', ['--date' => '2026-12-12'])->assertSuccessful();

    foreach ([$chef, $collegue] as $destinataire) {
        Notification::assertSentTo($destinataire, RappelTrimestrielActivitesNotification::class,
            fn ($n, $canaux) => $n->trimestre === 4 && $n->rang === 2
                && $n->activites === ['Atelier régional', 'Rapport de synthèse']
                && $canaux === ['mail', 'database']);
    }
    Notification::assertNotSentTo($horsStructure, RappelTrimestrielActivitesNotification::class);
});

test('sont exclues les activités réalisées, non validées ou d\'un autre trimestre', function () {
    Notification::fake();
    $exercice = Exercice::factory()->actif()->create(['annee' => 2026]);
    $structure = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($structure)->create();

    $realisee = activiteDuT4($exercice, $structure);
    ActiviteEvaluation::create(['activite_id' => $realisee->id, 'periode' => ActiviteEvaluation::PERIODE_FIN_ANNEE, 'statut_execution' => 'realise']);
    activiteDuT4($exercice, $structure, ['statut' => 'brouillon']);
    activiteDuT4($exercice, $structure, ['trimestre_3' => 'oui', 'trimestre_4' => 'non']);

    $this->artisan('activites:rappel-trimestriel', ['--date' => '2026-12-05'])->assertSuccessful();

    Notification::assertNotSentTo($chef, RappelTrimestrielActivitesNotification::class);
});

test('une activité seulement « en cours » déclenche encore le rappel', function () {
    Notification::fake();
    $exercice = Exercice::factory()->actif()->create(['annee' => 2026]);
    $structure = Departement::factory()->create();
    $chef = User::factory()->dansDepartement($structure)->create();

    $enCours = activiteDuT4($exercice, $structure);
    ActiviteEvaluation::create(['activite_id' => $enCours->id, 'periode' => ActiviteEvaluation::PERIODE_FIN_ANNEE, 'statut_execution' => 'en_cours']);

    $this->artisan('activites:rappel-trimestriel', ['--date' => '2026-12-26'])->assertSuccessful();

    Notification::assertSentTo($chef, RappelTrimestrielActivitesNotification::class, fn ($n) => $n->rang === 4);
});

test('rien ne part si l\'exercice actif est d\'une autre année', function () {
    Notification::fake();
    $exercice = Exercice::factory()->actif()->create(['annee' => 2025]);
    $structure = Departement::factory()->create();
    User::factory()->dansDepartement($structure)->create();
    activiteDuT4($exercice, $structure);

    $this->artisan('activites:rappel-trimestriel', ['--date' => '2026-12-05'])->assertSuccessful();

    Notification::assertNothingSent();
});

test('la notification in-app pointe vers les activités validées du trimestre', function () {
    $exercice = Exercice::factory()->actif()->create(['annee' => 2026]);
    $chef = User::factory()->create();

    $donnees = (new RappelTrimestrielActivitesNotification($exercice, 1, 3, ['A', 'B']))->toArray($chef);

    expect($donnees['message'])->toContain('rappel 3/4')->toContain('2 activité(s) du T1')
        ->and($donnees['url'])->toBe(route('activites.index', ['trimestre' => 1, 'statut' => 'valide']));
});

test('le rappel est planifié les 5, 12, 19 et 26 de mars, juin, septembre et décembre', function () {
    $evenement = collect(app(Schedule::class)->events())
        ->first(fn ($e) => str_contains($e->command ?? '', 'activites:rappel-trimestriel'));

    expect($evenement)->not->toBeNull()
        ->and($evenement->expression)->toBe('10 8 5,12,19,26 3,6,9,12 *');
});
