<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicateur_valeurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicateur_id')->constrained('indicateurs')->onDelete('cascade');
            $table->foreignId('departement_id')->constrained('departements')->onDelete('cascade');
            $table->string('periode', 20); // T1_2025, T2_2025, etc.
            $table->decimal('valeur_realisee', 15, 2);
            $table->decimal('ecart', 15, 2)->nullable();
            $table->decimal('taux_realisation', 8, 2)->nullable();
            $table->text('commentaire')->nullable();
            $table->foreignId('saisi_par')->constrained('users');
            $table->timestamp('date_saisie');
            $table->enum('statut', ['brouillon', 'valide'])->default('brouillon');
            $table->timestamps();

            $table->unique(['indicateur_id', 'departement_id', 'periode'], 'unique_indicateur_periode');
            $table->index('periode');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicateur_valeurs');
    }
};
