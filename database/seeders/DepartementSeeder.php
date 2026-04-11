<?php

namespace Database\Seeders;

use App\Models\Departement as Department;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $departements = [
            ['code' => 'DIR_FIN', 'nom' => 'Direction des Finances', 'ordre' => 1],
            ['code' => 'DIR_RH', 'nom' => 'Direction des Ressources Humaines', 'ordre' => 2],
            ['code' => 'DIR_SI', 'nom' => 'Direction des Systèmes d\'Information', 'ordre' => 3],
            ['code' => 'DIR_LOG', 'nom' => 'Direction Logistique', 'ordre' => 4],
            ['code' => 'DIR_AUDIT', 'nom' => 'Direction Audit Interne', 'ordre' => 5],
            ['code' => 'DIR_JUR', 'nom' => 'Direction Juridique', 'ordre' => 6],
            ['code' => 'DIR_QUAL', 'nom' => 'Direction Qualité', 'ordre' => 7],
        ];

        foreach ($departements as $dept) {
            Department::create($dept);
        }
    }
}
