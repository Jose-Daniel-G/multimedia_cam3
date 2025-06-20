<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoCausaDevolucionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $causas = [
            'Documentación incompleta',
            'Nombre inconsistente con identificación',
            'Falta de firma en el formulario',
            'Datos fiscales incorrectos',
            'Número de cédula inválido',
            'Registro duplicado en sistema',
            'Error en la fecha de emisión',
            'Pago no conciliado',
            'Formato de archivo inválido',
            'Expiración de plazo de respuesta',
            'Falta de soporte legal adjunto',
            'Nombre del ciudadano ilegible',
            'Información desactualizada',
            'Incompatibilidad con tipo de trámite',
            'Solicitud enviada al organismo incorrecto',
            'Error en la dirección de notificación',
            'Cédula de identidad no válida',
            'Firma electrónica no verificada',
            'Archivo adjunto corrupto',
            'Causa de devolución no especificada'
        ];

        foreach ($causas as $nombre) {
            DB::table('tipo_causa_devolucion')->insert([
                'nombre_causa_devolucion' => $nombre,
            ]);
        }
    }
}
