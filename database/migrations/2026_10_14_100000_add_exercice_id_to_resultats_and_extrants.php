<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les résultats et les extrants appartiennent au couple « exercice + objectif »,
 * et non plus au seul objectif : rattacher un objectif (pluriannuel) à un nouvel
 * exercice ne doit pas y faire apparaître les résultats et extrants d'un autre.
 *
 * Reprise de l'existant, résultat par résultat :
 *  1. l'exercice des activités portées par ses extrants (le plus ancien) ;
 *  2. sinon l'exercice de l'objectif dont l'année est celle de création du résultat ;
 *  3. sinon le plus ancien exercice couvert par l'objectif.
 * Les extrants héritent de l'exercice de leur résultat ; un extrant sans résultat
 * suit la même règle à partir de ses propres activités et de son objectif.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['resultats', 'extrants'] as $table) {
            if (! Schema::hasColumn($table, 'exercice_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('exercice_id')->nullable()->after('id')->constrained('exercices')->nullOnDelete();
                });
            }
        }

        $exercicesParObjectif = DB::table('exercice_objectif')
            ->join('exercices', 'exercices.id', '=', 'exercice_objectif.exercice_id')
            ->orderBy('exercices.annee')
            ->get(['exercice_objectif.objectif_id', 'exercices.id', 'exercices.annee'])
            ->groupBy('objectif_id');

        $exerciceParDefaut = function (int $objectifId, ?string $creeLe) use ($exercicesParObjectif): ?int {
            $exercices = $exercicesParObjectif->get($objectifId);

            if ($exercices === null || $exercices->isEmpty()) {
                return null;
            }

            $annee = $creeLe ? (int) substr($creeLe, 0, 4) : null;

            return (int) ($exercices->firstWhere('annee', $annee)?->id ?? $exercices->first()->id);
        };

        DB::table('resultats')->whereNull('exercice_id')->orderBy('id')
            ->each(function ($resultat) use ($exerciceParDefaut) {
                $exerciceId = DB::table('activites')
                    ->join('extrants', 'extrants.id', '=', 'activites.extrant_id')
                    ->join('exercices', 'exercices.id', '=', 'activites.exercice_id')
                    ->where('extrants.resultat_id', $resultat->id)
                    ->orderBy('exercices.annee')
                    ->value('exercices.id');

                $exerciceId ??= $exerciceParDefaut((int) $resultat->objectif_id, $resultat->created_at);

                if ($exerciceId !== null) {
                    DB::table('resultats')->where('id', $resultat->id)->update(['exercice_id' => $exerciceId]);
                    DB::table('extrants')->where('resultat_id', $resultat->id)->update(['exercice_id' => $exerciceId]);
                }
            });

        DB::table('extrants')->whereNull('exercice_id')->orderBy('id')
            ->each(function ($extrant) use ($exerciceParDefaut) {
                $exerciceId = DB::table('activites')
                    ->join('exercices', 'exercices.id', '=', 'activites.exercice_id')
                    ->where('activites.extrant_id', $extrant->id)
                    ->orderBy('exercices.annee')
                    ->value('exercices.id');

                $exerciceId ??= $exerciceParDefaut((int) $extrant->objectif_id, $extrant->created_at);

                if ($exerciceId !== null) {
                    DB::table('extrants')->where('id', $extrant->id)->update(['exercice_id' => $exerciceId]);
                }
            });

        // Un même code de résultat se retrouve sous un objectif dans chaque exercice.
        $this->remplacerUnicite(['objectif_id', 'code'], ['exercice_id', 'objectif_id', 'code']);
    }

    /**
     * @param  array<int, string>  $ancienne
     * @param  array<int, string>  $nouvelle
     */
    private function remplacerUnicite(array $ancienne, array $nouvelle): void
    {
        $existe = fn (array $colonnes) => collect(Schema::getIndexes('resultats'))
            ->contains(fn (array $i) => $i['unique'] && $i['columns'] === $colonnes);

        Schema::table('resultats', function (Blueprint $table) use ($ancienne, $nouvelle, $existe) {
            if ($existe($ancienne)) {
                $table->dropUnique($ancienne);
            }
            if (! $existe($nouvelle)) {
                $table->unique($nouvelle);
            }
        });
    }

    public function down(): void
    {
        $this->remplacerUnicite(['exercice_id', 'objectif_id', 'code'], ['objectif_id', 'code']);

        foreach (['extrants', 'resultats'] as $table) {
            if (Schema::hasColumn($table, 'exercice_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropConstrainedForeignId('exercice_id');
                });
            }
        }
    }
};
