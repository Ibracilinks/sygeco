<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un objectif peut couvrir plusieurs exercices (ex. « plan stratégique 2026-2030 ») :
 * le lien objectif → exercice devient une table pivot.
 *
 * Conséquence : l'exercice d'une activité ne peut plus se déduire de son objectif
 * (il serait ambigu sur 5 ans). `activites.exercice_id`, jusqu'ici réservé aux
 * activités non programmées, devient la source de vérité pour toutes les activités
 * et est renseigné rétroactivement depuis l'objectif d'origine.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('objectifs', 'exercice_id')) {
            return; // Déjà migré.
        }

        Schema::create('exercice_objectif', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_id')->constrained('exercices')->cascadeOnDelete();
            $table->foreignId('objectif_id')->constrained('objectifs')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exercice_id', 'objectif_id']);
        });

        // 1. Reprise du rattachement simple existant vers le pivot.
        $maintenant = now();
        $liens = DB::table('objectifs')
            ->whereNotNull('exercice_id')
            ->get(['id', 'exercice_id'])
            ->map(fn ($o) => [
                'objectif_id' => $o->id,
                'exercice_id' => $o->exercice_id,
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ])
            ->all();

        foreach (array_chunk($liens, 500) as $lot) {
            DB::table('exercice_objectif')->insert($lot);
        }

        // 2. Chaque activité programmée hérite de l'exercice de son objectif d'origine,
        //    avant que ce rattachement ne disparaisse.
        DB::table('activites')
            ->whereNull('activites.exercice_id')
            ->whereNotNull('activites.extrant_id')
            ->update([
                'exercice_id' => DB::raw(
                    '(select objectifs.exercice_id
                        from extrants
                        join objectifs on objectifs.id = extrants.objectif_id
                       where extrants.id = activites.extrant_id)'
                ),
            ]);

        // 3. Le rattachement simple n'a plus de sens : le pivot fait foi.
        //    `objectifs.annee` est conservé comme année de départ (tri / libellé).
        //    La contrainte peut être absente selon l'historique de la base : on la
        //    cherche avant de la supprimer, sinon MySQL rejette l'ALTER.
        $this->supprimerContrainteExerciceId();

        // L'unicité « code par exercice » n'a plus de support : un objectif
        // pluriannuel n'a plus d'exercice unique. On retombe sur un simple index
        // (l'unicité globale du code reste portée par la validation applicative ;
        // elle n'est pas remise en base car des codes hérités sont dupliqués
        // d'un exercice à l'autre — ex. « OG » recréé chaque année).
        $this->supprimerIndex('objectifs', 'objectifs_exercice_id_code_unique');

        Schema::table('objectifs', function (Blueprint $table) {
            $table->dropColumn('exercice_id');
        });
    }

    /**
     * Supprime la clé étrangère `objectifs.exercice_id` si elle existe.
     *
     * MySQL rejette l'ALTER quand la contrainte est absente (bases dont l'historique
     * l'a perdue) : on la recherche avant. SQLite reconstruit la table et exige au
     * contraire que la contrainte soit déclarée supprimée avant la colonne.
     */
    private function supprimerContrainteExerciceId(): void
    {
        $connexion = Schema::getConnection();

        if (! in_array($connexion->getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('objectifs', function (Blueprint $table) {
                $table->dropForeign(['exercice_id']);
            });

            return;
        }

        $contrainte = $connexion->selectOne(
            'select constraint_name from information_schema.key_column_usage
              where table_schema = database()
                and table_name = ?
                and column_name = ?
                and referenced_table_name is not null',
            ['objectifs', 'exercice_id']
        );

        if ($contrainte !== null) {
            $nom = $contrainte->constraint_name ?? $contrainte->CONSTRAINT_NAME;
            $connexion->statement("alter table `objectifs` drop foreign key `{$nom}`");
        }
    }

    private function supprimerIndex(string $table, string $index): void
    {
        $existe = collect(Schema::getIndexes($table))
            ->contains(fn (array $i) => $i['name'] === $index);

        if (! $existe) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($index) {
            $blueprint->dropIndex($index);
        });
    }

    public function down(): void
    {
        Schema::table('objectifs', function (Blueprint $table) {
            $table->foreignId('exercice_id')->nullable()->after('id')->constrained('exercices')->nullOnDelete();
        });

        // Restaure le rattachement simple sur l'exercice le plus ancien du pivot.
        DB::table('objectifs')->update([
            'exercice_id' => DB::raw(
                '(select min(exercice_objectif.exercice_id)
                    from exercice_objectif
                   where exercice_objectif.objectif_id = objectifs.id)'
            ),
        ]);

        Schema::table('objectifs', function (Blueprint $table) {
            $table->unique(['exercice_id', 'code'], 'objectifs_exercice_id_code_unique');
        });

        Schema::dropIfExists('exercice_objectif');
    }
};
