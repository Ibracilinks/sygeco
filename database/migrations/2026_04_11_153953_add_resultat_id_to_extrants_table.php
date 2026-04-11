<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extrants', function (Blueprint $table) {
            $table->foreignId('resultat_id')->nullable()->after('objectif_id')->constrained('resultats')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('extrants', function (Blueprint $table) {
            $table->dropForeign(['resultat_id']);
            $table->dropColumn('resultat_id');
        });
    }
};
