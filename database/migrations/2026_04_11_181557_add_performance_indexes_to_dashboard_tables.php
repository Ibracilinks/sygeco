<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            // Index for statut queries
            $table->index('statut');

            // Composite index for created_at month/year queries
            $table->index(['created_at', 'extrant_id']);

            // Index for cout queries (for ordering and distribution)
            $table->index('cout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->dropIndex(['statut']);
            $table->dropIndex(['created_at', 'extrant_id']);
            $table->dropIndex(['cout']);
        });
    }
};
