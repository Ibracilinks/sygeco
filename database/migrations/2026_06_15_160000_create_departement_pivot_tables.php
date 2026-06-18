<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departement_objectif', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departement_id')->constrained('departements')->cascadeOnDelete();
            $table->foreignId('objectif_id')->constrained('objectifs')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['departement_id', 'objectif_id']);
        });

        Schema::create('departement_resultat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departement_id')->constrained('departements')->cascadeOnDelete();
            $table->foreignId('resultat_id')->constrained('resultats')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['departement_id', 'resultat_id']);
        });

        Schema::create('departement_extrant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departement_id')->constrained('departements')->cascadeOnDelete();
            $table->foreignId('extrant_id')->constrained('extrants')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['departement_id', 'extrant_id']);
        });

        Schema::create('activite_departement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->foreignId('departement_id')->constrained('departements')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['activite_id', 'departement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activite_departement');
        Schema::dropIfExists('departement_extrant');
        Schema::dropIfExists('departement_resultat');
        Schema::dropIfExists('departement_objectif');
    }
};
