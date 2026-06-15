<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les codes (OG, RS.I, Extrant 1.1, …) doivent être uniques au sein de leur parent,
     * pas globalement, afin que chaque exercice puisse réutiliser la même nomenclature.
     */
    public function up(): void
    {
        Schema::table('objectifs', function (Blueprint $table) {
            $table->dropUnique('objectifs_code_unique');
            $table->unique(['exercice_id', 'code']);
        });

        Schema::table('resultats', function (Blueprint $table) {
            $table->dropUnique('resultats_code_unique');
            $table->unique(['objectif_id', 'code']);
        });

        Schema::table('extrants', function (Blueprint $table) {
            $table->dropUnique('extrants_code_unique');
            $table->unique(['resultat_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('objectifs', function (Blueprint $table) {
            $table->dropUnique(['exercice_id', 'code']);
            $table->unique('code', 'objectifs_code_unique');
        });

        Schema::table('resultats', function (Blueprint $table) {
            $table->dropUnique(['objectif_id', 'code']);
            $table->unique('code', 'resultats_code_unique');
        });

        Schema::table('extrants', function (Blueprint $table) {
            $table->dropUnique(['resultat_id', 'code']);
            $table->unique('code', 'extrants_code_unique');
        });
    }
};
