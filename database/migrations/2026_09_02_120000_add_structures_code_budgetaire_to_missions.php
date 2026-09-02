<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une mission est demandée par plusieurs services (table pivot, `departement_id`
     * restant la structure principale), porte un code budgétaire et, à l'intérieur
     * du pays, un point de départ.
     */
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->string('code_budgetaire', 60)->nullable()->after('objet');
            $table->string('point_depart', 150)->nullable()->after('code_budgetaire');
        });

        Schema::create('departement_mission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departement_id')->constrained('departements')->cascadeOnDelete();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['departement_id', 'mission_id']);
        });

        // Les missions déjà saisies gardent leur structure demandeuse d'origine.
        foreach (
            DB::table('missions')->whereNotNull('departement_id')->get(['id', 'departement_id']) as $mission
        ) {
            DB::table('departement_mission')->insert([
                'departement_id' => $mission->departement_id,
                'mission_id' => $mission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('departement_mission');

        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['code_budgetaire', 'point_depart']);
        });
    }
};
