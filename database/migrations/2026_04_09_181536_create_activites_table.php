<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extrant_id')->constrained('extrants')->onDelete('cascade');
            $table->foreignId('departement_id')->constrained('departements')->onDelete('cascade');
            $table->text('nom_activite');
            $table->text('indicateur_objectivement_verifiable');
            $table->text('moyen_verification');
            $table->decimal('cout', 15, 2);
            $table->enum('trimestre_1', ['oui', 'non'])->default('non');
            $table->enum('trimestre_2', ['oui', 'non'])->default('non');
            $table->enum('trimestre_3', ['oui', 'non'])->default('non');
            $table->enum('trimestre_4', ['oui', 'non'])->default('non');
            $table->enum('statut', ['brouillon', 'soumis', 'valide'])->default('brouillon');
            $table->foreignId('saisi_par')->constrained('users');
            $table->timestamp('date_saisie');
            $table->text('commentaires')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};
