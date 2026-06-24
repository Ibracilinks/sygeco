<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ouverture de la saisie + relances par paliers (J-15, J-10, J-7, J-5, J-3, J-2, J-1, J) à tous les comptes.
Schedule::command('activites:notifier-saisie')->dailyAt('08:00');

// Ouverture des fenêtres de suivi (mi-parcours et évaluation) aux chefs de département.
Schedule::command('activites:notifier-suivi')->dailyAt('08:05');
