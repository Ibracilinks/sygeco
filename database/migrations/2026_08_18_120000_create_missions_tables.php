<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 80)->unique();
            $table->string('type', 40)->default('meme_ville');
            $table->foreignId('departement_id')->nullable()->constrained('departements')->nullOnDelete();
            $table->text('objet');
            $table->date('date_document');
            $table->date('date_depart');
            $table->date('date_retour');
            $table->unsignedInteger('nombre_personnes')->default(0);
            $table->unsignedInteger('nombre_jours')->default(1);
            $table->unsignedInteger('tickets_carburant_par_jour')->default(1);
            $table->unsignedInteger('nombre_tickets_carburant')->default(0);
            $table->decimal('montant_par_jour', 15, 2)->default(0);
            $table->decimal('montant_ticket_carburant', 15, 2)->default(0);
            $table->decimal('montant_indemnites', 15, 2)->default(0);
            $table->decimal('montant_carburant', 15, 2)->default(0);
            $table->decimal('montant_total', 15, 2)->default(0);
            $table->string('lieu_signature', 100)->default('Bamako');
            $table->enum('statut', ['brouillon', 'finalise'])->default('brouillon');
            $table->foreignId('cree_par')->constrained('users');
            $table->foreignId('maj_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mission_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->unsignedInteger('ordre')->default(1);
            $table->string('nom_complet');
            $table->timestamps();
        });

        Schema::create('mission_signataires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->unsignedInteger('ordre')->default(1);
            $table->string('libelle', 150)->nullable();
            $table->string('nom');
            $table->string('fonction');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_signataires');
        Schema::dropIfExists('mission_participants');
        Schema::dropIfExists('missions');
    }
};
