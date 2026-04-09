<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultat_strategiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objectif_strategique_id')->constrained('objectif_strategiques')->onDelete('cascade');
            $table->string('code', 20)->unique();
            $table->text('libelle');
            $table->text('description')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('objectif_strategique_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultat_strategiques');
    }
};
