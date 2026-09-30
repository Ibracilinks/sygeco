<?php

use App\Models\Mission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les barèmes des missions (catégories de missionnaires et zones de majoration)
     * étaient figés dans des constantes PHP : toute révision réglementaire imposait
     * un déploiement. Ils passent en base pour être révisables depuis l'application.
     * Les valeurs initiales reprennent exactement les constantes du modèle.
     */
    public function up(): void
    {
        Schema::create('mission_baremes', function (Blueprint $table) {
            $table->id();
            $table->string('groupe', 40);
            $table->string('code', 40);
            $table->string('libelle', 150);
            $table->string('description', 500)->nullable();
            $table->decimal('frais_mission', 15, 2)->default(0);
            $table->decimal('indemnites', 15, 2)->default(0);
            $table->decimal('taux', 6, 2)->default(0);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();

            $table->unique(['groupe', 'code']);
            $table->index('groupe');
        });

        $lignes = [];
        $ordre = 0;

        foreach (Mission::CATEGORIES_EXTERIEURES as $code => $categorie) {
            $lignes[] = [
                'groupe' => 'categorie_exterieure',
                'code' => $code,
                'libelle' => $categorie['label'],
                'description' => $categorie['description'] ?? null,
                'frais_mission' => $categorie['frais_mission'] ?? 0,
                'indemnites' => $categorie['indemnites'] ?? 0,
                'taux' => 0,
                'ordre' => ++$ordre,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $ordre = 0;

        foreach (Mission::CATEGORIES_NATIONALES as $code => $categorie) {
            $lignes[] = [
                'groupe' => 'categorie_nationale',
                'code' => $code,
                'libelle' => $categorie['label'],
                'description' => $categorie['description'] ?? null,
                'frais_mission' => $categorie['frais_mission'] ?? 0,
                'indemnites' => $categorie['indemnites'] ?? 0,
                'taux' => 0,
                'ordre' => ++$ordre,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $ordre = 0;

        foreach (Mission::ZONES_EXTERIEURES as $code => $zone) {
            $lignes[] = [
                'groupe' => 'zone',
                'code' => $code,
                'libelle' => $zone['label'],
                'description' => null,
                'frais_mission' => 0,
                'indemnites' => 0,
                'taux' => $zone['taux'] ?? 0,
                'ordre' => ++$ordre,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('mission_baremes')->insert($lignes);
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_baremes');
    }
};
