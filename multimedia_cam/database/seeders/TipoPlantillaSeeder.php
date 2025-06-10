<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoPlantillaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_plantilla')->insert([
            ['nombre_plantilla' => 'plantilla cobro coactivo masivo'],
            ['nombre_plantilla' => 'plantilla de cobro persuasivo masivo'],
            ['nombre_plantilla' => 'plantilla liquidaciones'],
        ]);
    }
}
