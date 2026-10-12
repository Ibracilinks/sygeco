<?php

use App\Models\Departement;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Date limite de saisie dépassée
|--------------------------------------------------------------------------
| Passé la date limite de saisie de l'exercice actif, seuls le dbcgoq et le
| service contrôle de gestion peuvent encore programmer une activité.
*/

function exerciceSaisieCloturee(): Exercice
{
    return Exercice::factory()->create([
        'annee' => 2026,
        'statut' => 'actif',
        'date_debut' => now()->startOfYear(),
        'date_fin' => now()->endOfYear(),
        'date_ouverture_saisie' => now()->subDays(30),
        'date_limite_saisie' => now()->subDay(),
    ]);
}

test('après la date limite, le responsable de programme ne voit plus le bouton ni le formulaire', function () {
    exerciceSaisieCloturee();
    $responsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($responsable)->get(route('activites.index'))
        ->assertOk()
        ->assertDontSee(route('activites.create'))
        ->assertSee('Saisie close depuis le');

    $this->actingAs($responsable)->get(route('activites.create'))->assertForbidden();
});

test('après la date limite, l\'enregistrement d\'une activité est refusé', function () {
    $exercice = exerciceSaisieCloturee();
    $departement = Departement::factory()->create();
    $agent = userWithRole('agent-planification', ['departement_id' => $departement->id]);

    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => $exercice->annee]);
    $extrant = Extrant::factory()->forResultat(Resultat::factory()->forObjectif($objectif)->create())->create();

    $this->actingAs($agent)->post(route('activites.store'), [
        'extrant_id' => $extrant->id,
        'departement_id' => $departement->id,
        'nom_activite' => 'Activité hors délai',
        'indicateur_objectivement_verifiable' => 'Indicateur',
        'moyen_verification' => 'Rapport',
        'cout' => 1000,
    ])->assertForbidden();
});

test('après la date limite, le dbcgoq et le service contrôle de gestion gardent l\'accès', function (string $role) {
    exerciceSaisieCloturee();
    $user = userWithRole($role, ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($user)->get(route('activites.index'))->assertSee(route('activites.create'));
    $this->actingAs($user)->get(route('activites.create'))->assertOk();
})->with(['dbcgoq', 'service-controle-gestion']);

test('le jour de la date limite, la saisie reste ouverte', function () {
    exerciceSaisieCloturee()->update(['date_limite_saisie' => now()->startOfDay()]);
    $responsable = userWithRole('responsable-programme', ['departement_id' => Departement::factory()->create()->id]);

    $this->actingAs($responsable)->get(route('activites.create'))->assertOk();
});
