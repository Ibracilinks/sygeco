<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercices', function (Blueprint $table) {
            $table->date('date_ouverture_saisie')->nullable()->after('date_fin');
            $table->date('date_limite_saisie')->nullable()->after('date_ouverture_saisie');
            $table->timestamp('ouverture_notifiee_le')->nullable()->after('date_limite_saisie');
        });
    }

    public function down(): void
    {
        Schema::table('exercices', function (Blueprint $table) {
            $table->dropColumn(['date_ouverture_saisie', 'date_limite_saisie', 'ouverture_notifiee_le']);
        });
    }
};
