<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * « Pour mémoire » (PM) : l'activité est bien programmée, mais son coût est
     * déjà porté par une autre activité. Elle ne pèse donc rien au budget et
     * s'affiche « PM » au lieu d'un montant.
     */
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->boolean('pour_memoire')->default(false)->after('cout');
        });
    }

    public function down(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->dropColumn('pour_memoire');
        });
    }
};
