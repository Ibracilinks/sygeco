<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            // Montant du budget effectivement consommé lors de l'exécution
            // (comparaison ultérieure avec le coût planifié « cout »).
            $table->decimal('montant_utilise', 15, 2)->nullable()->after('execution_commentaire');
            // Valeur réalisée de l'indicateur objectivement vérifiable de l'activité.
            $table->decimal('valeur_indicateur', 15, 2)->nullable()->after('montant_utilise');
        });
    }

    public function down(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->dropColumn(['montant_utilise', 'valeur_indicateur']);
        });
    }
};
