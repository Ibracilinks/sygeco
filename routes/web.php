<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\ExtrantController;
use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\IndicateurController;
use App\Http\Controllers\ObjectifController;
use App\Http\Controllers\ResultatController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Départements
    Route::resource('departements', DepartementController::class);

    // Utilisateurs
    Route::resource('users', UserController::class);

    Route::resource('structures', StructureController::class);

    Route::post('objectifs/{objectif}/toggle-statut', [ObjectifController::class, 'toggleStatut'])->name('objectifs.toggle-statut');
    Route::resource('objectifs', ObjectifController::class);

    Route::resource('resultats', ResultatController::class);

    Route::resource('extrants', ExtrantController::class);
    Route::post('extrants/{extrant}/toggle-status', [ExtrantController::class, 'toggleStatus'])->name('extrants.toggle-status');

    // Gestion Terrain - Activités
    Route::prefix('activites')->name('activites.')->group(function () {
        Route::get('/', [ActiviteController::class, 'index'])->name('index');
        Route::get('/create', [ActiviteController::class, 'create'])->name('create');
        Route::post('/', [ActiviteController::class, 'store'])->name('store');
        Route::get('/export', [ActiviteController::class, 'export'])->name('export');
        Route::get('/{activite}', [ActiviteController::class, 'show'])->name('show');
        Route::get('/{activite}/edit', [ActiviteController::class, 'edit'])->name('edit');
        Route::put('/{activite}', [ActiviteController::class, 'update'])->name('update');
        Route::delete('/{activite}', [ActiviteController::class, 'destroy'])->name('destroy');
        Route::post('/{activite}/toggle', [ActiviteController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{activite}/duplicate', [ActiviteController::class, 'duplicate'])->name('duplicate');
        Route::post('/{activite}/soumettre', [ActiviteController::class, 'soumettre'])->name('soumettre');
        Route::post('/{activite}/valider', [ActiviteController::class, 'valider'])->name('valider');
    });

    Route::resource('indicateurs', IndicateurController::class);
    Route::post('indicateurs/{indicateur}/toggle-status', [IndicateurController::class, 'toggleStatus'])->name('indicateurs.toggle-status');
    Route::get('indicateurs/{indicateur}/saisie-valeurs', [IndicateurController::class, 'saisieValeurs'])->name('indicateurs.saisie-valeurs');
    Route::post('indicateurs/{indicateur}/store-valeurs', [IndicateurController::class, 'storeValeurs'])->name('indicateurs.store-valeurs');
});

require __DIR__ . '/settings.php';
