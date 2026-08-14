<?php

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\User;
use App\Models\ValidationHistorique;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Machine à états (brouillon -> soumis -> valide / refus)
|--------------------------------------------------------------------------
*/

test('un brouillon peut être soumis et passe au statut en attente', function () {
    $activite = Activite::factory()->brouillon()->create();

    expect($activite->peutEtreSoumis())->toBeTrue();
    expect($activite->soumettre())->toBeTrue();

    expect($activite->fresh())
        ->statut->toBe('en_attente')
        ->date_soumission->not->toBeNull();
});

test('soumettre journalise une entrée d\'historique', function () {
    $activite = Activite::factory()->brouillon()->create();

    $activite->soumettre();

    expect(ValidationHistorique::where('activite_id', $activite->id)
        ->where('action', 'soumission')->count())->toBe(1);
});

test('une activité déjà soumise ne peut pas être soumise à nouveau', function () {
    $activite = Activite::factory()->soumis()->create();

    expect($activite->peutEtreSoumis())->toBeFalse();
    expect($activite->soumettre())->toBeFalse();
});

test('une activité soumise peut être validée par un utilisateur authentifié', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activite = Activite::factory()->soumis()->create();

    expect($activite->peutEtreValide())->toBeTrue();
    expect($activite->valider('OK pour moi'))->toBeTrue();

    expect($activite->fresh())
        ->statut->toBe('valide')
        ->valide_par->toBe($user->id)
        ->date_validation->not->toBeNull();
});

test('un brouillon ne peut pas être validé directement', function () {
    $activite = Activite::factory()->brouillon()->create();

    expect($activite->peutEtreValide())->toBeFalse();
    expect($activite->valider())->toBeFalse();
});

test('refuser une activité soumise la passe au statut rejeté avec un motif', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activite = Activite::factory()->soumis()->create();

    expect($activite->refuser('Budget non justifié et incohérent'))->toBeTrue();

    expect($activite->fresh())
        ->statut->toBe('rejete')
        ->motif_refus->toBe('Budget non justifié et incohérent')
        ->refuse_par->toBe($user->id)
        ->refuse_le->not->toBeNull();
});

test('refuser un brouillon est impossible', function () {
    $activite = Activite::factory()->brouillon()->create();

    expect($activite->refuser('motif suffisamment long'))->toBeFalse();
});

test('soumettre après un refus efface le motif de refus', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activite = Activite::factory()->soumis()->create();
    $activite->refuser('Motif de refus initial assez long');
    $activite->refresh();

    expect($activite->motif_refus)->not->toBeNull();

    $activite->soumettre();

    expect($activite->fresh())
        ->statut->toBe('en_attente')
        ->motif_refus->toBeNull()
        ->refuse_par->toBeNull();
});

test('peutEtreModifie est vrai seulement pour les brouillons', function () {
    expect(Activite::factory()->brouillon()->create()->peutEtreModifie())->toBeTrue();
    expect(Activite::factory()->soumis()->create()->peutEtreModifie())->toBeFalse();
    expect(Activite::factory()->valide()->create()->peutEtreModifie())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Arbitrage budgétaire
|--------------------------------------------------------------------------
*/

test('arbitrerModification met à jour l\'activité et journalise l\'action', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activite = Activite::factory()->soumis()->avecCout(1000000)->create();

    $activite->arbitrerModification(['cout' => 750000], 'Coût ajusté après arbitrage');

    expect((float) $activite->fresh()->cout)->toBe(750000.0);
    expect(ValidationHistorique::where('activite_id', $activite->id)
        ->where('action', 'arbitrage_modification')->count())->toBe(1);
});

test('arbitrerSuppression archive (soft delete) l\'activité', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activite = Activite::factory()->soumis()->create();

    $activite->arbitrerSuppression('Doublon');

    expect(Activite::find($activite->id))->toBeNull();
    expect(Activite::withTrashed()->find($activite->id))->not->toBeNull();
    expect(ValidationHistorique::where('activite_id', $activite->id)
        ->where('action', 'arbitrage_suppression')->count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Accesseurs
|--------------------------------------------------------------------------
*/

test('getTrimestresSelectionnes liste les trimestres marqués oui', function () {
    $activite = Activite::factory()->create([
        'trimestre_1' => 'oui',
        'trimestre_2' => 'non',
        'trimestre_3' => 'oui',
        'trimestre_4' => 'non',
    ]);

    expect($activite->trimestres_selectionnes)->toBe('T1, T3');
});

test('getCoutFormate formate le coût avec séparateur et FCFA', function () {
    $activite = Activite::factory()->avecCout(1234567)->create();

    expect($activite->cout_formate)->toBe('1 234 567 FCFA');
});

test('getStatutLabel reflète les statuts de la machine à états', function () {
    expect(Activite::factory()->rejete()->create()->statut_label)->toContain('Rejeté');
    expect(Activite::factory()->brouillon()->create()->statut_label)->toContain('Brouillon');
    expect(Activite::factory()->enAttente()->create()->statut_label)->toContain('En attente');
    expect(Activite::factory()->valide()->create()->statut_label)->toContain('Validé');
});

test('getStatutColor renvoie une couleur par statut', function () {
    expect(Activite::factory()->rejete()->create()->statut_color)->toBe('red');
    expect(Activite::factory()->brouillon()->create()->statut_color)->toBe('gray');
    expect(Activite::factory()->enAttente()->create()->statut_color)->toBe('yellow');
    expect(Activite::factory()->valide()->create()->statut_color)->toBe('green');
});

test('statut d\'exécution : libellé et couleur', function () {
    expect(Activite::factory()->create(['statut_execution' => 'realise'])->statut_execution_label)->toBe('Réalisé');
    expect(Activite::factory()->create(['statut_execution' => 'realise'])->statut_execution_couleur)->toBe('emerald');
    expect(Activite::factory()->create(['statut_execution' => 'en_cours'])->statut_execution_couleur)->toBe('amber');
    // Valeur par défaut (non_realise) -> libellé et couleur de repli
    expect(Activite::factory()->create()->statut_execution_label)->toBe('Non réalisé');
    expect(Activite::factory()->create()->statut_execution_couleur)->toBe('slate');
});

/*
|--------------------------------------------------------------------------
| Scopes & relations
|--------------------------------------------------------------------------
*/

test('les scopes par statut filtrent correctement', function () {
    Activite::factory()->brouillon()->count(2)->create();
    Activite::factory()->soumis()->count(3)->create();
    Activite::factory()->valide()->count(1)->create();

    expect(Activite::brouillon()->count())->toBe(2);
    expect(Activite::soumis()->count())->toBe(3);
    expect(Activite::valide()->count())->toBe(1);
});

test('le scope pourTrimestre filtre les activités d\'un trimestre', function () {
    Activite::factory()->create(['trimestre_1' => 'oui', 'trimestre_2' => 'non', 'trimestre_3' => 'non', 'trimestre_4' => 'non']);
    Activite::factory()->create(['trimestre_1' => 'non', 'trimestre_2' => 'oui', 'trimestre_3' => 'non', 'trimestre_4' => 'non']);

    expect(Activite::pourTrimestre(1)->count())->toBe(1);
});

test('le scope byDepartement filtre par département', function () {
    $dep = Departement::factory()->create();
    $autre = Departement::factory()->create();
    Activite::factory()->pourDepartement($dep)->count(2)->create();
    Activite::factory()->pourDepartement($autre)->count(3)->create();

    expect(Activite::byDepartement($dep->id)->count())->toBe(2);
});

test('le scope forExercice filtre sur l\'exercice porté par l\'activité', function () {
    // Deux exercices distincts et explicites pour éviter toute collision d'année aléatoire.
    $objectifA = Objectif::factory()->pourAnnee(2024)->create();
    $extrantA = Extrant::factory()->create(['objectif_id' => $objectifA->id]);
    $exerciceId = $objectifA->exercices()->value('exercices.id');

    $objectifB = Objectif::factory()->pourAnnee(2025)->create();
    $extrantB = Extrant::factory()->create(['objectif_id' => $objectifB->id]);

    Activite::factory()->pourExtrant($extrantA)->count(2)->create();
    Activite::factory()->pourExtrant($extrantB)->count(1)->create();

    expect(Activite::forExercice($exerciceId)->count())->toBe(2);
    expect(Activite::forExercice(null)->count())->toBe(3);
});

test('relations de l\'activité', function () {
    $activite = Activite::factory()->create();

    expect($activite->extrant)->toBeInstanceOf(Extrant::class);
    expect($activite->departement)->toBeInstanceOf(Departement::class);
    expect($activite->saisiePar)->toBeInstanceOf(User::class);
});
