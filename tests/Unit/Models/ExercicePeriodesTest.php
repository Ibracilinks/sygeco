<?php

use App\Models\Exercice;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('enPeriodeMiParcours est faux sans dates', function () {
    $exercice = Exercice::factory()->create([
        'date_debut_mi_parcours' => null,
        'date_fin_mi_parcours' => null,
    ]);

    expect($exercice->enPeriodeMiParcours())->toBeFalse();
});

test('enPeriodeMiParcours est vrai dans la fenêtre (bornes incluses)', function () {
    $exercice = Exercice::factory()->create([
        'date_debut_mi_parcours' => now()->subDays(2)->toDateString(),
        'date_fin_mi_parcours' => now()->addDays(2)->toDateString(),
    ]);

    expect($exercice->enPeriodeMiParcours())->toBeTrue();
    expect($exercice->periodeSuiviCourante())->toBe('mi_parcours');
});

test('enPeriodeMiParcours est faux hors de la fenêtre', function () {
    $exercice = Exercice::factory()->create([
        'date_debut_mi_parcours' => now()->subDays(10)->toDateString(),
        'date_fin_mi_parcours' => now()->subDays(5)->toDateString(),
    ]);

    expect($exercice->enPeriodeMiParcours())->toBeFalse();
});

test('enPeriodeEvaluation suit sa propre fenêtre', function () {
    $exercice = Exercice::factory()->create([
        'date_debut_evaluation' => now()->subDay()->toDateString(),
        'date_fin_evaluation' => now()->addDays(3)->toDateString(),
    ]);

    expect($exercice->enPeriodeEvaluation())->toBeTrue();
    expect($exercice->periodeSuiviCourante())->toBe('evaluation');
});

test('enPeriodeSuiviExecution est vrai si l\'une des fenêtres est ouverte', function () {
    $miParcours = Exercice::factory()->create([
        'date_debut_mi_parcours' => now()->subDay()->toDateString(),
        'date_fin_mi_parcours' => now()->addDay()->toDateString(),
    ]);
    $aucune = Exercice::factory()->create();

    expect($miParcours->enPeriodeSuiviExecution())->toBeTrue();
    expect($aucune->enPeriodeSuiviExecution())->toBeFalse();
    expect($aucune->periodeSuiviCourante())->toBeNull();
});

test('les nouvelles dates sont castées en Carbon', function () {
    $exercice = Exercice::factory()->create([
        'date_debut_mi_parcours' => '2025-06-01',
        'date_fin_mi_parcours' => '2025-06-30',
        'date_debut_evaluation' => '2025-12-01',
        'date_fin_evaluation' => '2025-12-31',
    ]);

    expect($exercice->date_debut_mi_parcours)->toBeInstanceOf(\Carbon\CarbonInterface::class);
    expect($exercice->date_fin_evaluation->format('Y-m-d'))->toBe('2025-12-31');
});
