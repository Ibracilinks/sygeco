<?php

use App\Http\Controllers\BudgetAnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\ExerciceController;
use App\Http\Controllers\ExtrantController;
use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ObjectifController;
use App\Http\Controllers\ResultatController;
use App\Http\Controllers\SapAnalyticsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard parallèle « SAP Cloud Analytics » (présentation autonome, mêmes données)
    Route::get('sap-analytics', [SapAnalyticsController::class, 'index'])->name('sap.analytics');

    // Notifications in-app (accessible à tout utilisateur connecté)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/lire-tout', [NotificationController::class, 'readAll'])->name('read-all');
        Route::get('/{notification}/lire', [NotificationController::class, 'read'])->name('read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['role:superadmin|dbcgoq'])->group(function () {
        Route::get('budget-analysis', [BudgetAnalysisController::class, 'index'])->name('budget.analysis');
        Route::get('journal', [JournalController::class, 'index'])->name('journal.index');

        Route::resource('exercices', ExerciceController::class)->only(['index', 'show']);
        Route::post('exercices/{exercice}/activate', [ExerciceController::class, 'activate'])->name('exercices.activate');
        Route::get('exercices/{exercice}/export', [ExerciceController::class, 'export'])->name('exercices.export');
        Route::resource('exercices', ExerciceController::class)->except(['index', 'show']);

        Route::post('objectifs/{objectif}/toggle-statut', [ObjectifController::class, 'toggleStatut'])->name('objectifs.toggle-statut');
        Route::resource('objectifs', ObjectifController::class)->except(['index', 'show']);

        Route::resource('resultats', ResultatController::class)->except(['index', 'show']);
        Route::post('resultats/{resultat}/toggle-status', [ResultatController::class, 'toggleStatus'])->name('resultats.toggle-status');

        Route::resource('extrants', ExtrantController::class)->except(['index', 'show']);
        Route::post('extrants/{extrant}/toggle-status', [ExtrantController::class, 'toggleStatus'])->name('extrants.toggle-status');

        Route::resource('departements', DepartementController::class)->except(['index', 'show']);
        Route::resource('users', UserController::class)->except(['index', 'show']);
    });

    Route::middleware(['role:superadmin|dbcgoq|chef'])->group(function () {
        Route::prefix('validations')->name('validations.')->group(function () {
            Route::get('/', [ValidationController::class, 'index'])->name('index');
            Route::get('/{activite}', [ValidationController::class, 'show'])->name('show');
            Route::post('/{activite}/valider', [ValidationController::class, 'valider'])->name('valider');
            Route::post('/{activite}/refuser', [ValidationController::class, 'refuser'])->name('refuser');
            Route::post('/valider-plusieurs', [ValidationController::class, 'validerPlusieurs'])->name('valider-plusieurs');
            // Arbitrage budgétaire
            Route::post('/{activite}/arbitrer-modifier', [ValidationController::class, 'arbitrerModifier'])->name('arbitrer-modifier');
            Route::post('/{activite}/arbitrer-supprimer', [ValidationController::class, 'arbitrerSupprimer'])->name('arbitrer-supprimer');
            Route::post('/arbitrer-fusionner', [ValidationController::class, 'arbitrerFusionner'])->name('arbitrer-fusionner');
            Route::get('/export/excel', [ValidationController::class, 'exporter'])->name('exporter');
        });
    });

    Route::middleware(['role:superadmin|dbcgoq|chef'])->group(function () {
        Route::resource('objectifs', ObjectifController::class)->only(['index', 'show']);
        Route::resource('resultats', ResultatController::class)->only(['index', 'show']);
        Route::resource('extrants', ExtrantController::class)->only(['index', 'show']);
    });

    Route::middleware(['role:superadmin|dbcgoq'])->group(function () {
        Route::resource('departements', DepartementController::class)->only(['index', 'show']);
        Route::resource('users', UserController::class)->only(['index', 'show']);
    });

    Route::middleware(['role:superadmin|dbcgoq|chef|agent'])->prefix('activites')->name('activites.')->group(function () {
        Route::get('/', [ActiviteController::class, 'index'])->name('index');
        Route::get('/create', [ActiviteController::class, 'create'])->name('create');
        Route::post('/', [ActiviteController::class, 'store'])->name('store');
        Route::get('/export', [ActiviteController::class, 'export'])->name('export');
        Route::get('/suivi', [ActiviteController::class, 'suivi'])->name('suivi');
        Route::post('/non-programmee', [ActiviteController::class, 'storeNonProgrammee'])->name('non-programmee.store');
        Route::get('/{activite}', [ActiviteController::class, 'show'])->name('show');
        Route::get('/{activite}/edit', [ActiviteController::class, 'edit'])->name('edit');
        Route::put('/{activite}', [ActiviteController::class, 'update'])->name('update');
        Route::delete('/{activite}', [ActiviteController::class, 'destroy'])->name('destroy');
        Route::post('/{activite}/toggle', [ActiviteController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{activite}/duplicate', [ActiviteController::class, 'duplicate'])->name('duplicate');
        Route::post('/{activite}/soumettre', [ActiviteController::class, 'soumettre'])->name('soumettre');
        Route::post('/{activite}/valider', [ActiviteController::class, 'valider'])->name('valider');
        Route::post('/{activite}/refuser', [ActiviteController::class, 'refuser'])->name('refuser');
        Route::post('/{activite}/execution', [ActiviteController::class, 'updateExecution'])->name('execution');
        Route::post('/{activite}/pieces-jointes', [ActiviteController::class, 'storePieceJointe'])->name('pieces-jointes.store');
        Route::get('/{activite}/pieces-jointes/{pieceJointe}', [ActiviteController::class, 'downloadPieceJointe'])->name('pieces-jointes.download');
    });
});

require __DIR__ . '/settings.php';
