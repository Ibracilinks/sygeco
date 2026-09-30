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
        Schema::table('departements', function (Blueprint $table) use ($valeurs) {
            $table->enum('type', $valeurs)->default('departement')->change();
        });
    }
};
