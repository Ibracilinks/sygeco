<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extrants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objectif_id')->constrained('objectifs')->onDelete('cascade');
            $table->string('code', 20)->unique();
            $table->string('libelle', 500);
            $table->text('description')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('objectif_id');
            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extrants');
    }
};
