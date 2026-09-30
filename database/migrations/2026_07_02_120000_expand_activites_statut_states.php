<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Élargit la machine à états des activités :
 *  - « soumis » devient « en_attente » (en attente de validation) ;
 *  - le rejet devient un vrai statut « rejete » (auparavant un brouillon portant un motif_refus).
 *
 * La colonne passe d'un enum figé à un varchar : les valeurs autorisées sont contrôlées
 * côté application (modèle + validation), ce qui rend l'ajout futur d'états trivial et portable
 * sur tous les SGBD.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->string('statut', 20)->default('brouillon')->change();
        });

        // Les activités soumises passent « en attente de validation ».
        DB::table('activites')->where('statut', 'soumis')->update(['statut' => 'en_attente']);

        // Les brouillons issus d'un refus deviennent de vraies activités rejetées.
        DB::table('activites')
            ->where('statut', 'brouillon')
            ->whereNotNull('motif_refus')
            ->update(['statut' => 'rejete']);
    }

    public function down(): void
    {
        // Retour aux valeurs historiques avant de restaurer l'enum.
        DB::table('activites')->where('statut', 'en_attente')->update(['statut' => 'soumis']);
        DB::table('activites')->where('statut', 'rejete')->update(['statut' => 'brouillon']);

        Schema::table('activites', function (Blueprint $table) {
            $table->enum('statut', ['brouillon', 'soumis', 'valide'])->default('brouillon')->change();
        });
    }
};
