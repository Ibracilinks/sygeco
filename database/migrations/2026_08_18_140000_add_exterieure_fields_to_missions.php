<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->string('destination', 150)->nullable()->after('objet');
            $table->string('zone_code', 40)->nullable()->after('destination');
            $table->string('zone_label', 150)->nullable()->after('zone_code');
            $table->decimal('zone_taux', 5, 2)->default(0)->after('zone_label');
            $table->decimal('montant_majoration', 15, 2)->default(0)->after('montant_indemnites');
            $table->decimal('montant_autres_frais', 15, 2)->default(0)->after('montant_majoration');
            $table->decimal('montant_billets', 15, 2)->default(0)->after('montant_autres_frais');
            $table->unsignedInteger('frais_participation_nombre')->default(0)->after('montant_billets');
            $table->decimal('frais_participation_unitaire', 15, 2)->default(0)->after('frais_participation_nombre');
            $table->decimal('frais_participation_total', 15, 2)->default(0)->after('frais_participation_unitaire');
            $table->unsignedInteger('frais_visa_nombre')->default(0)->after('frais_participation_total');
            $table->decimal('frais_visa_unitaire', 15, 2)->default(0)->after('frais_visa_nombre');
            $table->decimal('frais_visa_total', 15, 2)->default(0)->after('frais_visa_unitaire');
            $table->unsignedInteger('billets_affaire_nombre')->default(0)->after('frais_visa_total');
            $table->decimal('billets_affaire_unitaire', 15, 2)->default(0)->after('billets_affaire_nombre');
            $table->decimal('billets_affaire_total', 15, 2)->default(0)->after('billets_affaire_unitaire');
            $table->unsignedInteger('billets_economique_nombre')->default(0)->after('billets_affaire_total');
            $table->decimal('billets_economique_unitaire', 15, 2)->default(0)->after('billets_economique_nombre');
            $table->decimal('billets_economique_total', 15, 2)->default(0)->after('billets_economique_unitaire');
        });

        Schema::table('mission_participants', function (Blueprint $table) {
            $table->string('categorie', 40)->nullable()->after('nom_complet');
            $table->decimal('montant_par_jour', 15, 2)->default(0)->after('categorie');
            $table->decimal('montant_par_nuitee', 15, 2)->default(0)->after('montant_par_jour');
            $table->unsignedInteger('nombre_nuitees')->default(0)->after('montant_par_nuitee');
            $table->decimal('sous_total', 15, 2)->default(0)->after('nombre_nuitees');
            $table->decimal('majoration_taux', 5, 2)->default(0)->after('sous_total');
            $table->decimal('majoration_montant', 15, 2)->default(0)->after('majoration_taux');
            $table->decimal('total_general', 15, 2)->default(0)->after('majoration_montant');
        });
    }

    public function down(): void
    {
        Schema::table('mission_participants', function (Blueprint $table) {
            $table->dropColumn([
                'categorie',
                'montant_par_jour',
                'montant_par_nuitee',
                'nombre_nuitees',
                'sous_total',
                'majoration_taux',
                'majoration_montant',
                'total_general',
            ]);
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn([
                'destination',
                'zone_code',
                'zone_label',
                'zone_taux',
                'montant_majoration',
                'montant_autres_frais',
                'montant_billets',
                'frais_participation_nombre',
                'frais_participation_unitaire',
                'frais_participation_total',
                'frais_visa_nombre',
                'frais_visa_unitaire',
                'frais_visa_total',
                'billets_affaire_nombre',
                'billets_affaire_unitaire',
                'billets_affaire_total',
                'billets_economique_nombre',
                'billets_economique_unitaire',
                'billets_economique_total',
            ]);
        });
    }
};
