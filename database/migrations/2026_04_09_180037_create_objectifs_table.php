<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objectifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_id')->nullable()->constrained('exercices')->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->string('libelle', 500);
            $table->text('description')->nullable();
            $table->integer('annee');
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->integer('ordre')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('annee');
            $table->index('statut');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objectifs');
    }
};
