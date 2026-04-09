<?php

use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\AnalyseBudgetaireController;
use App\Http\Controllers\ExtrantController;
use App\Http\Controllers\ObjectifStrategiqueController;
use App\Http\Controllers\ResultatStrategiqueController;
use App\Http\Controllers\StructureController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::resource('structures', StructureController::class);

    Route::resource('objectifs', ObjectifStrategiqueController::class);
    Route::resource('resultats', ResultatStrategiqueController::class);
    Route::resource('extrants', ExtrantController::class);
    // Gestion Terrain - Activités
    Route::prefix('activites')->name('activites.')->group(function () {
        Route::get('/', [ActiviteController::class, 'index'])->name('index');
        Route::get('/dashboard', [ActiviteController::class, 'dashboard'])->name('dashboard');
        Route::get('/create', [ActiviteController::class, 'create'])->name('create');
        Route::post('/', [ActiviteController::class, 'store'])->name('store');
        Route::get('/export', [ActiviteController::class, 'export'])->name('export');
        Route::get('/{activite}', [ActiviteController::class, 'show'])->name('show');
        Route::get('/{activite}/edit', [ActiviteController::class, 'edit'])->name('edit');
        Route::put('/{activite}', [ActiviteController::class, 'update'])->name('update');
        Route::delete('/{activite}', [ActiviteController::class, 'destroy'])->name('destroy');
        Route::post('/{activite}/toggle', [ActiviteController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{activite}/duplicate', [ActiviteController::class, 'duplicate'])->name('duplicate');
    });

    // Analyse budgétaire
    Route::prefix('analyse-budgetaire')->name('analyse-budgetaire.')->group(function () {
        Route::get('/', [AnalyseBudgetaireController::class, 'dashboard'])->name('dashboard');
        Route::get('/comparaison', [AnalyseBudgetaireController::class, 'comparaison'])->name('comparaison');
        Route::get('/export', [AnalyseBudgetaireController::class, 'export'])->name('export');
        Route::get('/objectif/{objectif}', [AnalyseBudgetaireController::class, 'parObjectif'])->name('par-objectif');
        Route::get('/resultat/{resultat}', [AnalyseBudgetaireController::class, 'parResultat'])->name('par-resultat');
        Route::get('/extrant/{extrant}', [AnalyseBudgetaireController::class, 'parExtrant'])->name('par-extrant');
    });

    // Route::resource('activites', ActiviteController::class);
});

require __DIR__ . '/settings.php';
