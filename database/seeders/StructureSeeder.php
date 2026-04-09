<?php

namespace Database\Seeders;

use App\Models\Structure;
use Illuminate\Database\Seeder;

class StructureSeeder extends Seeder
{
    public function run(): void
    {
        $direction = Structure::factory()->direction()->create();

        $departement = Structure::factory()
            ->departement($direction->id)
            ->create();

        $regions = ['BAMAKO', 'KAYES', 'SIKASSO', 'SEGOU', 'KOUTIALA'];

        foreach ($regions as $region) {
            Structure::factory()
                ->bureauRegional($region, $departement->id)
                ->create();
        }
    }
}
