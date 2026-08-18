<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_etapes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->unsignedInteger('ordre')->default(1);
            $table->string('type_etape', 40);
            $table->string('bareme', 40)->default('national');
            $table->string('localite', 150);
            $table->date('date_depart');
            $table->date('date_retour');
            $table->unsignedInteger('nombre_jours')->default(1);
            $table->unsignedInteger('nombre_nuitees')->default(0);
            $table->boolean('premiere_nuitee_payee')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_etapes');
    }
};
