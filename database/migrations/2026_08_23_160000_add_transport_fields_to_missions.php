<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Section « II- CARBURANT » et « III- PEAGES » du budget des missions à
     * l'intérieur du pays : carburant du trajet et en ville, location de véhicule,
     * billet d'avion et péages. Ces postes n'existaient pas encore en base.
     */
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->unsignedInteger('nombre_vehicules')->default(1)->after('nombre_tickets_carburant');
            $table->decimal('distance_totale_km', 10, 2)->default(0)->after('nombre_vehicules');
            $table->decimal('consommation_aux_cent', 6, 2)->default(20)->after('distance_totale_km');
            $table->decimal('litres_par_jour_ville', 6, 2)->default(5)->after('consommation_aux_cent');
            $table->decimal('prix_litre_carburant', 10, 2)->default(866)->after('litres_par_jour_ville');
            $table->unsignedInteger('location_vehicule_jours')->default(0)->after('prix_litre_carburant');
            $table->decimal('location_vehicule_tarif', 15, 2)->default(0)->after('location_vehicule_jours');
            $table->decimal('montant_carburant_trajet', 15, 2)->default(0)->after('location_vehicule_tarif');
            $table->decimal('montant_carburant_ville', 15, 2)->default(0)->after('montant_carburant_trajet');
            $table->decimal('montant_location_vehicule', 15, 2)->default(0)->after('montant_carburant_ville');
            $table->decimal('montant_peages', 15, 2)->default(0)->after('montant_location_vehicule');
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_vehicules',
                'distance_totale_km',
                'consommation_aux_cent',
                'litres_par_jour_ville',
                'prix_litre_carburant',
                'location_vehicule_jours',
                'location_vehicule_tarif',
                'montant_carburant_trajet',
                'montant_carburant_ville',
                'montant_location_vehicule',
                'montant_peages',
            ]);
        });
    }
};
