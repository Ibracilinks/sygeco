<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Deux nouveaux niveaux rattachés directement à la Direction Générale, au même
     * titre que les Directions Centrales : l'Agence Comptable et les Bureaux
     * Régionaux. Ils formulent leurs propres activités et n'ont pas d'enfants.
     */
    public function up(): void
    {
        $this->redefinirType(['direction', 'departement', 'service', 'agence_comptable', 'bureau_regional']);
    }

    public function down(): void
    {
        DB::table('departements')
            ->whereIn('type', ['agence_comptable', 'bureau_regional'])
            ->update(['type' => 'departement']);

        $this->redefinirType(['direction', 'departement', 'service']);
    }

    /**
     * @param  array<int, string>  $valeurs
     */
    private function redefinirType(array $valeurs): void
    {
        // PostgreSQL : l'enum est un varchar + contrainte CHECK, que ->change()
        // ne sait pas redéfinir. On remplace directement la contrainte.
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            $liste = implode(', ', array_map(fn (string $v) => "'{$v}'", $valeurs));

            DB::statement('alter table departements drop constraint if exists departements_type_check');
            DB::statement("alter table departements add constraint departements_type_check check (type in ({$liste}))");

            return;
        }

        Schema::table('departements', function (Blueprint $table) use ($valeurs) {
            $table->enum('type', $valeurs)->default('departement')->change();
        });
    }
};
