<?php

use App\Models\Activite;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('le seeder PTA 2025 importe la hiérarchie réelle depuis le CSV', function () {
    // PtaImportSeeder a besoin d'un compte pour « saisi_par ».
    User::factory()->create();

    $this->seed(\Database\Seeders\Pta2025Seeder::class);

    $exercice = Exercice::where('annee', 2025)->first();
    expect($exercice)->not->toBeNull();

    expect(Objectif::where('exercice_id', $exercice->id)->count())->toBeGreaterThan(0);
    expect(Resultat::count())->toBeGreaterThan(0);
    expect(Extrant::count())->toBeGreaterThan(0);
    expect(Activite::count())->toBeGreaterThan(0);

    // L'encodage est réparé : pas de mojibake résiduel dans les libellés importés.
    $libelles = Resultat::pluck('libelle')->implode(' ').' '.Extrant::pluck('libelle')->implode(' ');
    expect($libelles)->not->toContain('Ã©');
});

test('le seeder PTA 2026 importe des activités avec un coût', function () {
    User::factory()->create();

    $this->seed(\Database\Seeders\Pta2026Seeder::class);

    expect(Exercice::where('annee', 2026)->exists())->toBeTrue();
    expect(Activite::where('cout', '>', 0)->count())->toBeGreaterThan(0);
});

test('un fichier PTA manquant n\'interrompt pas le seeding (2024)', function () {
    User::factory()->create();

    // pta_2024.csv n'est pas (encore) présent : le seeder doit se contenter d'avertir.
    $this->seed(\Database\Seeders\Pta2024Seeder::class);

    expect(true)->toBeTrue();
});
