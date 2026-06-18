<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->enum('statut_execution', ['non_realise', 'en_cours', 'realise'])
                ->default('non_realise')
                ->after('statut');
            $table->text('execution_commentaire')->nullable()->after('statut_execution');
            $table->timestamp('execution_maj_le')->nullable()->after('execution_commentaire');
            $table->foreignId('execution_maj_par')->nullable()->after('execution_maj_le')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->dropForeign(['execution_maj_par']);
            $table->dropColumn(['statut_execution', 'execution_commentaire', 'execution_maj_le', 'execution_maj_par']);
        });
    }
};
