<?php

namespace Database\Seeders;

class Pta2024Seeder extends PtaImportSeeder
{
    protected function annee(): int
    {
        return 2024;
    }

    protected function fichier(): string
    {
        return 'pta_2024.csv';
    }
}
