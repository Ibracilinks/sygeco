<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 200);
            $table->text('description')->nullable();
            $table->string('responsable_nom', 100)->nullable();
            $table->string('responsable_email', 100)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('ordre')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departements');
    }
};
