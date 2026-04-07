<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extrant_id')->constrained('extrants')->onDelete('cascade');
            $table->string('code', 50)->unique();
            $table->text('libelle');
            $table->text('description')->nullable();
            $table->decimal('budget_previsionnel_global', 15, 2)->nullable();
            $table->date('date_debut_prevue')->nullable();
            $table->date('date_fin_prevue')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('extrant_id');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};
