<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objectif_id')->constrained('objectifs')->onDelete('cascade');
            $table->string('code', 50)->unique();
            $table->string('libelle', 500);
            $table->text('description')->nullable();
            $table->enum('type', ['performance', 'gestion', 'qualite', 'efficacite'])->default('performance');
            $table->string('unite', 50)->nullable(); // %, FCFA, Nombre, Jours, Taux
            $table->string('formule_calcul')->nullable();
            $table->decimal('cible', 15, 2)->nullable();
            $table->decimal('seuil_alerte', 15, 2)->nullable();
            $table->enum('periodicite', ['mensuel', 'trimestriel', 'semestriel', 'annuel'])->default('trimestriel');
            $table->enum('sens', ['hausse', 'baisse'])->default('hausse');
            $table->string('source_donnee', 200)->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('objectif_id');
            $table->index('code');
            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicateurs');
    }
};
