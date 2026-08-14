<?php

use App\Models\Activite;
use App\Models\Exercice;
use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('les activités coûteuses des alertes budgétaires sont cliquables et affichent le nom complet', function () {
    $exercice = Exercice::factory()->actif()->create(['annee' => 2026]);
    $objectif = Objectif::factory()->pourExercice($exercice)->create(['annee' => 2026]);
    $resultat = Resultat::factory()->forObjectif($objectif)->create();
    $extrant = Extrant::factory()->forResultat($resultat)->create();

    $nomComplet = "Acquisition et déploiement d'une plateforme intégrée de gestion documentaire à l'échelle nationale";
    $activite = Activite::factory()->pourExtrant($extrant)->create([
        'nom_activite' => $nomComplet,
        'cout' => 8_000_000, // > 5M -> alerte « activité coûteuse »
    ]);

    $admin = userWithRole('dbcgoq');

    $response = $this->actingAs($admin)->get(route('budget.analysis', ['annee' => 2026]));

    $response->assertOk();
    // Lien cliquable vers la fiche de l'activité...
    $response->assertSee(route('activites.show', $activite->id), false);
    // ...et nom complet disponible au survol (attribut title).
    $response->assertSee('title="'.e($nomComplet).'"', false);
});
