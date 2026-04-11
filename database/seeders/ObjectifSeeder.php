<?php

namespace Database\Seeders;

use App\Models\Objectif;
use Illuminate\Database\Seeder;

class ObjectifSeeder extends Seeder
{
    public function run(): void
    {
        Objectif::factory()
            ->count(50)
            ->actif()
            ->anneeEnCours()
            ->create();

        Objectif::factory()
            ->count(30)
            ->actif()
            ->anneeProchaine()
            ->create();

        Objectif::factory()
            ->count(20)
            ->inactif()
            ->create();

        Objectif::factory()
            ->count(50)
            ->actif()
            ->pourAnnee(2024)
            ->create();

        Objectif::factory()
            ->count(50)
            ->actif()
            ->pourAnnee(2023)
            ->create();

        Objectif::factory()
            ->count(50)
            ->actif()
            ->pourAnnee(2022)
            ->create();

        Objectif::factory()
            ->count(50)
            ->actif()
            ->pourAnnee(2022)
            ->create();

        Objectif::factory()
            ->count(50)
            ->actif()
            ->pourAnnee(2021)
            ->create();
    }
}
