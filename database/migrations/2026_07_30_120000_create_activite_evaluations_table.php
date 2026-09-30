<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une évaluation par activité et par période (mi-parcours / fin d'année) :
     * la saisie de fin d'année n'écrase plus celle de mi-parcours.
     */
    public function up(): void
    {
        Schema::create('activite_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->enum('periode', ['mi_parcours', 'fin_annee']);
            $table->enum('statut_execution', ['non_realise', 'en_cours', 'realise'])->default('non_realise');
            $table->decimal('montant_utilise', 15, 2)->nullable();
            $table->decimal('valeur_indicateur', 15, 2)->nullable();
            $table->text('observation')->nullable();
            $table->foreignId('maj_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('maj_le')->nullable();
            $table->timestamps();

            $table->unique(['activite_id', 'periode']);
            $table->index(['periode', 'statut_execution']);
        });

        $this->reprendreSaisiesExistantes();
    }

    public function down(): void
    {
        Schema::dropIfExists('activite_evaluations');
    }

    /**
     * Reprise des évaluations déjà saisies sur la table activites : elles sont
     * rattachées à la fenêtre de l'exercice dans laquelle la mise à jour a eu lieu,
     * et au mi-parcours par défaut.
     */
    private function reprendreSaisiesExistantes(): void
    {
        $activites = DB::table('activites')
            ->leftJoin('extrants', 'activites.extrant_id', '=', 'extrants.id')
            ->leftJoin('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
            ->select(
                'activites.id',
                'activites.statut_execution',
                'activites.montant_utilise',
                'activites.valeur_indicateur',
                'activites.execution_commentaire',
                'activites.execution_maj_le',
                'activites.execution_maj_par',
                DB::raw('COALESCE(activites.exercice_id, objectifs.exercice_id) as exercice_id')
            )
            ->where(function ($query) {
                $query->whereNotNull('activites.execution_maj_le')
                    ->orWhereNotNull('activites.montant_utilise')
                    ->orWhereNotNull('activites.valeur_indicateur')
                    ->orWhereNotNull('activites.execution_commentaire')
                    ->orWhere('activites.statut_execution', '!=', 'non_realise');
            })
            ->get();

        if ($activites->isEmpty()) {
            return;
        }

        $exercices = DB::table('exercices')
            ->select('id', 'date_debut_evaluation')
            ->get()
            ->keyBy('id');

        $lignes = $activites->map(function ($activite) use ($exercices) {
            $debutEvaluation = $exercices[$activite->exercice_id]->date_debut_evaluation ?? null;

            $periode = ($debutEvaluation && $activite->execution_maj_le
                && $activite->execution_maj_le >= $debutEvaluation)
                    ? 'fin_annee'
                    : 'mi_parcours';

            return [
                'activite_id' => $activite->id,
                'periode' => $periode,
                'statut_execution' => $activite->statut_execution ?: 'non_realise',
                'montant_utilise' => $activite->montant_utilise,
                'valeur_indicateur' => $activite->valeur_indicateur,
                'observation' => $activite->execution_commentaire,
                'maj_par' => $activite->execution_maj_par,
                'maj_le' => $activite->execution_maj_le,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        foreach (array_chunk($lignes, 500) as $lot) {
            DB::table('activite_evaluations')->insert($lot);
        }
    }
};
