<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('departement_id')
                ->nullable()
                ->after('email')
                ->constrained('departements')
                ->nullOnDelete();

            $table->string('poste')->nullable()->after('departement_id');
            $table->string('telephone')->nullable()->after('poste');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departement_id']);
            $table->dropColumn(['departement_id', 'poste', 'telephone']);
        });
    }
};
