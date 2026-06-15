<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->string('action');
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire')->nullable();
            $table->string('ancien_statut')->nullable();
            $table->string('nouveau_statut')->nullable();
            $table->timestamps();

            $table->index('activite_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_historiques');
    }
};
