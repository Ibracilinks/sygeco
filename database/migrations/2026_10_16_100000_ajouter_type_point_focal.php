<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nouveau niveau rattaché à la Direction Générale : le Point Focal. Il formule
     * ses propres activités et n'a pas d'enfants.
     */
    public function up(): void
    {
        $this->redefinirType(['direction', 'departement', 'service', 'agence_comptable', 'bureau_regional', 'point_focal']);
    }

    public function down(): void
    {
        DB::table('departements')->where('type', 'point_focal')->update(['type' => 'departement']);

        $this->redefinirType(['direction', 'departement', 'service', 'agence_comptable', 'bureau_regional']);
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
