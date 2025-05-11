<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoImpuestoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_impuesto')->insert([
            ['id_tp_imp' => 1, 'txt_tp_imp' => 'Impuesto Predial'],
            ['id_tp_imp' => 2, 'txt_tp_imp' => 'Impuesto de Vehículos'],
            ['id_tp_imp' => 3, 'txt_tp_imp' => 'Impuesto sobre la Renta'],
            ['id_tp_imp' => 4, 'txt_tp_imp' => 'Impuesto al Valor Agregado (IVA)'],
            ['id_tp_imp' => 5, 'txt_tp_imp' => 'Impuesto de Industria y Comercio'],
            ['id_tp_imp' => 6, 'txt_tp_imp' => 'Impuesto de Timbre'],
            ['id_tp_imp' => 7, 'txt_tp_imp' => 'Impuesto a las Bebidas Azucaradas'],
            ['id_tp_imp' => 8, 'txt_tp_imp' => 'Impuesto al Consumo'],
            ['id_tp_imp' => 9, 'txt_tp_imp' => 'Impuesto de Alumbrado Público'],
            ['id_tp_imp' => 10, 'txt_tp_imp' => 'Impuesto Ambiental'],
            ['id_tp_imp' => 11, 'txt_tp_imp' => 'Impuesto de Espectáculos Públicos'],
            ['id_tp_imp' => 12, 'txt_tp_imp' => 'Impuesto de Registro'],
            ['id_tp_imp' => 13, 'txt_tp_imp' => 'Impuesto de Loterías'],
            ['id_tp_imp' => 14, 'txt_tp_imp' => 'Impuesto de Ganancias Ocasionales'],
            ['id_tp_imp' => 15, 'txt_tp_imp' => 'Impuesto al Carbono'],
            ['id_tp_imp' => 16, 'txt_tp_imp' => 'Impuesto de Semaforización'],
            ['id_tp_imp' => 17, 'txt_tp_imp' => 'Otro Impuesto'],
        ]);
        
    }
}
