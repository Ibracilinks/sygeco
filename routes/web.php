<?php

use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\BudgetAnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ExerciceController;
use App\Http\Controllers\ExtrantController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\MissionBaremeController;
use App\Http\Controllers\MissionController;
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

    // Manuel d'utilisation (documentation utilisateur), découpé en chapitres.
    Route::view('documentation', 'pages.documentation.index')->name('documentation');
    Route::prefix('documentation')->name('documentation.')->group(function () {
        Route::view('directions-centrales', 'pages.documentation.directions-centrales')->name('directions-centrales');
        Route::view('utilisateurs', 'pages.documentation.utilisateurs')->name('utilisateurs');
        Route::view('exercices', 'pages.documentation.exercices')->name('exercices');
        Route::view('planification', 'pages.documentation.planification')->name('planification');
        Route::view('activites', 'pages.documentation.activites')->name('activites');
        Route::view('validation', 'pages.documentation.validation')->name('validation');
        Route::view('suivi', 'pages.documentation.suivi')->name('suivi');
        Route::view('missions', 'pages.documentation.missions')->name('missions');
    });

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

        // Ressource complète en un seul enregistrement : Laravel place la route `create`
        // (GET exercices/create) AVANT `show` (GET exercices/{exercice}), évitant que
        // « /exercices/create » soit capturé par le joker {exercice} (bug 404 précédent).
        Route::resource('exercices', ExerciceController::class);
        Route::post('exercices/{exercice}/activate', [ExerciceController::class, 'activate'])->name('exercices.activate');
        Route::get('exercices/{exercice}/export', [ExerciceController::class, 'export'])->name('exercices.export');

        Route::post('objectifs/{objectif}/toggle-statut', [ObjectifController::class, 'toggleStatut'])->name('objectifs.toggle-statut');
        Route::resource('objectifs', ObjectifController::class)->except(['index', 'show']);

        Route::resource('resultats', ResultatController::class)->except(['index', 'show']);
        Route::post('resultats/{resultat}/toggle-status', [ResultatController::class, 'toggleStatus'])->name('resultats.toggle-status');

        Route::resource('extrants', ExtrantController::class)->except(['index', 'show']);
        Route::post('extrants/{extrant}/toggle-status', [ExtrantController::class, 'toggleStatus'])->name('extrants.toggle-status');

        Route::resource('departements', DepartementController::class)->except(['index', 'show']);
        Route::resource('users', UserController::class)->except(['index', 'show']);
    });

    Route::middleware(['role:superadmin|dbcgoq|responsable-programme'])->group(function () {
        Route::prefix('validations')->name('validations.')->group(function () {
            Route::get('/', [ValidationController::class, 'index'])->name('index');
            Route::get('/entite/{departement}', [ValidationController::class, 'entite'])->name('entite');
            Route::get('/{activite}', [ValidationController::class, 'show'])->name('show');
            Route::post('/valider-plusieurs', [ValidationController::class, 'validerPlusieurs'])->name('valider-plusieurs');
            Route::post('/{activite}/valider', [ValidationController::class, 'valider'])->name('valider');
            Route::post('/{activite}/refuser', [ValidationController::class, 'refuser'])->name('refuser');
            // Arbitrage budgétaire
            Route::post('/{activite}/arbitrer-modifier', [ValidationController::class, 'arbitrerModifier'])->name('arbitrer-modifier');
            Route::post('/{activite}/arbitrer-supprimer', [ValidationController::class, 'arbitrerSupprimer'])->name('arbitrer-supprimer');
            Route::post('/arbitrer-fusionner', [ValidationController::class, 'arbitrerFusionner'])->name('arbitrer-fusionner');
            Route::get('/export/excel', [ValidationController::class, 'exporter'])->name('exporter');
        });
    });

    // Lecture du cadre logique : les cellules planification et suivi & évaluation
    // en ont besoin pour rattacher / lire les activités, sans droit d'écriture.
    Route::middleware(['role:superadmin|dbcgoq|responsable-programme|agent-planification|suivi-evaluation|service-controle-gestion'])->group(function () {
        Route::resource('objectifs', ObjectifController::class)->only(['index', 'show']);
        Route::resource('resultats', ResultatController::class)->only(['index', 'show']);
        Route::resource('extrants', ExtrantController::class)->only(['index', 'show']);
    });

    Route::middleware(['role:superadmin|dbcgoq'])->group(function () {
        Route::resource('departements', DepartementController::class)->only(['index', 'show']);
        Route::resource('users', UserController::class)->only(['index', 'show']);
    });

    // L'évaluation est fermée à la cellule planification (agent-planification).
    Route::middleware(['role:superadmin|dbcgoq|responsable-programme|chef-service|suivi-evaluation|service-controle-gestion'])->prefix('evaluations')->name('evaluations.')->group(function () {
        Route::get('/{periode}', [EvaluationController::class, 'index'])->name('index')->where('periode', 'mi-parcours|fin-annee');
        Route::get('/{periode}/export', [EvaluationController::class, 'exporter'])->name('export')->where('periode', 'mi-parcours|fin-annee');
        Route::post('/{activite}/{periode}', [EvaluationController::class, 'enregistrer'])->name('enregistrer')->where('periode', 'mi-parcours|fin-annee');
    });

    Route::middleware(['role:superadmin|dbcgoq|responsable-programme|chef-service|agent-planification|suivi-evaluation|service-controle-gestion'])->prefix('activites')->name('activites.')->group(function () {
        Route::get('/', [ActiviteController::class, 'index'])->name('index');
        Route::get('/create', [ActiviteController::class, 'create'])->name('create');
        Route::post('/', [ActiviteController::class, 'store'])->name('store');
        Route::get('/export', [ActiviteController::class, 'export'])->name('export');
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
        Route::post('/{activite}/pieces-jointes', [ActiviteController::class, 'storePieceJointe'])->name('pieces-jointes.store');
        Route::get('/{activite}/pieces-jointes/{pieceJointe}', [ActiviteController::class, 'downloadPieceJointe'])->name('pieces-jointes.download');
    });

    // Le chargé des missions (service-budget) n'a accès qu'à ce module.
    Route::middleware(['role:superadmin|dbcgoq|responsable-programme|service-budget'])->group(function () {
        Route::post('missions/{mission}/finaliser', [MissionController::class, 'finaliser'])->name('missions.finaliser');
        Route::resource('missions', MissionController::class);
    });

    // Barèmes des missions : montants révisables, liste des catégories/zones figée.
    Route::middleware(['role:superadmin|dbcgoq'])->group(function () {
        Route::get('baremes-missions', [MissionBaremeController::class, 'index'])->name('mission-baremes.index');
        Route::put('baremes-missions', [MissionBaremeController::class, 'update'])->name('mission-baremes.update');
    });
});

require __DIR__.'/settings.php';
