<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Annonce manuelle de l'ouverture de la saisie (bouton de la fiche exercice), distincte
 * de `ouverture_notifiee_le` qui marque l'envoi automatique des identifiants.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercices', function (Blueprint $table) {
            $table->timestamp('ouverture_annoncee_le')->nullable()->after('ouverture_notifiee_le');
            $table->unsignedInteger('ouverture_annoncee_destinataires')->nullable()->after('ouverture_annoncee_le');
        });
    }

    public function down(): void
    {
        Schema::table('exercices', function (Blueprint $table) {
            $table->dropColumn(['ouverture_annoncee_le', 'ouverture_annoncee_destinataires']);
        });
    }
};
