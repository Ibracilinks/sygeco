<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Active la saisie d'activités « non programmées » (hors PTA) dans le suivi-évaluation :
 *  - lien direct à l'exercice (une activité non programmée n'a pas forcément d'extrant) ;
 *  - indicateur `non_programmee` ;
 *  - `extrant_id` devient nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->foreignId('exercice_id')->nullable()->after('extrant_id')->constrained('exercices')->nullOnDelete();
            $table->boolean('non_programmee')->default(false)->after('exercice_id');
        });

        Schema::table('activites', function (Blueprint $table) {
            $table->foreignId('extrant_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exercice_id');
            $table->dropColumn('non_programmee');
        });

        Schema::table('activites', function (Blueprint $table) {
            $table->foreignId('extrant_id')->nullable(false)->change();
        });
    }
};
