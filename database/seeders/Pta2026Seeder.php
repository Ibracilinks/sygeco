<?php

namespace Database\Seeders;

class Pta2026Seeder extends PtaImportSeeder
{
    protected function annee(): int
    {
        return 2026;
    }

    protected function fichier(): string
    {
        return 'pta_2026.csv';
    }
}
