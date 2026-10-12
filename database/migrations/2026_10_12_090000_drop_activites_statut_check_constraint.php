<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sur PostgreSQL, l'enum d'origine de `activites.statut` est un varchar doublé d'une
 * contrainte CHECK (brouillon, soumis, valide). Le passage en string par ->change()
 * (2026_07_02_120000) ne la supprime pas : « en_attente » et « rejete » étaient donc
 * refusés à la soumission. Les valeurs sont contrôlées côté application.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE activites DROP CONSTRAINT IF EXISTS activites_statut_check');
    }

    public function down(): void
    {
        // Pas de retour : rétablir l'ancienne contrainte refuserait les statuts actuels.
    }
};
