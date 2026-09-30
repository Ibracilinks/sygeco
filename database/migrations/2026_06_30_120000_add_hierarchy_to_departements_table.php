<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departements', function (Blueprint $table) {
            $table->enum('type', ['direction', 'departement', 'service'])
                ->default('departement')
                ->after('nom');
            $table->foreignId('parent_id')
                ->nullable()
                ->after('type')
                ->constrained('departements')
                ->nullOnDelete();

            $table->index('type');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('departements', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'parent_id']);
        });
    }
};
