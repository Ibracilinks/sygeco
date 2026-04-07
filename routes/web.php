<?php

use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\ExtrantController;
use App\Http\Controllers\ObjectifStrategiqueController;
use App\Http\Controllers\ResultatStrategiqueController;
use App\Http\Controllers\StructureController;
use Illuminate\Support\Facades\Route;

Route::view('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::resource('structures', StructureController::class);

    Route::resource('objectifs', ObjectifStrategiqueController::class);
    Route::resource('resultats', ResultatStrategiqueController::class);
    Route::resource('extrants', ExtrantController::class);
    Route::resource('activites', ActiviteController::class);
});

require __DIR__ . '/settings.php';
