<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La valeur d'un indicateur n'est pas toujours chiffrée : « Rapport produit »,
     * « 3 sur 5 », « Oui »… La colonne décimale rejetait ces libellés.
     * Les valeurs déjà saisies sont converties telles quelles par le SGBD.
     */
    public function up(): void
    {
        foreach (['activites', 'activite_evaluations'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('valeur_indicateur', 255)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Retour au décimal : les libellés non numériques deviennent NULL, sans quoi
        // la conversion échouerait sur la première valeur textuelle.
        foreach (['activites', 'activite_evaluations'] as $table) {
            DB::table($table)
                ->whereNotNull('valeur_indicateur')
                ->whereRaw("valeur_indicateur NOT REGEXP '^-?[0-9]+(\\\\.[0-9]+)?$'")
                ->update(['valeur_indicateur' => null]);

            Schema::table($table, function (Blueprint $t) {
                $t->decimal('valeur_indicateur', 15, 2)->nullable()->change();
            });
        }
    }
};
