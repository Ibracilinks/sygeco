<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercice_relances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_id')->constrained('exercices')->cascadeOnDelete();
            // Palier de relance : nombre de jours restant avant la date limite (0 = jour J)
            $table->unsignedTinyInteger('palier');
            $table->unsignedInteger('destinataires')->default(0);
            $table->timestamp('envoye_le')->nullable();
            $table->timestamps();

            $table->unique(['exercice_id', 'palier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercice_relances');
    }
};
