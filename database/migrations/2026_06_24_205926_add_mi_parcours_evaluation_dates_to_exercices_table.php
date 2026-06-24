<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercices', function (Blueprint $table) {
            // Fenêtre de suivi à mi-parcours (renseignement de l'exécution des activités).
            $table->date('date_debut_mi_parcours')->nullable()->after('ouverture_notifiee_le');
            $table->date('date_fin_mi_parcours')->nullable()->after('date_debut_mi_parcours');
            $table->timestamp('mi_parcours_notifiee_le')->nullable()->after('date_fin_mi_parcours');

            // Fenêtre d'évaluation de fin d'exercice (bilan d'exécution des activités).
            $table->date('date_debut_evaluation')->nullable()->after('mi_parcours_notifiee_le');
            $table->date('date_fin_evaluation')->nullable()->after('date_debut_evaluation');
            $table->timestamp('evaluation_notifiee_le')->nullable()->after('date_fin_evaluation');
        });
    }

    public function down(): void
    {
        Schema::table('exercices', function (Blueprint $table) {
            $table->dropColumn([
                'date_debut_mi_parcours',
                'date_fin_mi_parcours',
                'mi_parcours_notifiee_le',
                'date_debut_evaluation',
                'date_fin_evaluation',
                'evaluation_notifiee_le',
            ]);
        });
    }
};
