<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoActoTramiteSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nombre_tipo_acto_tramite' => 'Inscripción de Propiedad'],
            ['nombre_tipo_acto_tramite' => 'Cancelación de Hipoteca'],
            ['nombre_tipo_acto_tramite' => 'Constitución de Sociedad'],
            ['nombre_tipo_acto_tramite' => 'Traspaso de Vehículo'],
            ['nombre_tipo_acto_tramite' => 'Cesión de Derechos'],
            ['nombre_tipo_acto_tramite' => 'Levantamiento de Embargo'],
            ['nombre_tipo_acto_tramite' => 'Aprobación de Licencia de Construcción'],
            ['nombre_tipo_acto_tramite' => 'Modificación de Escritura'],
            ['nombre_tipo_acto_tramite' => 'Registro de Testamento'],
            ['nombre_tipo_acto_tramite' => 'Formalización de Contrato de Arrendamiento'],
            ['nombre_tipo_acto_tramite' => 'Registro de Póliza'],
            ['nombre_tipo_acto_tramite' => 'Registro de Patrimonio Autónomo'],
            ['nombre_tipo_acto_tramite' => 'Cancelación de Gravamen'],
            ['nombre_tipo_acto_tramite' => 'Registro de Constitución de Garantía'],
            ['nombre_tipo_acto_tramite' => 'Anotación Preventiva'],
            ['nombre_tipo_acto_tramite' => 'Rectificación Catastral'],
            ['nombre_tipo_acto_tramite' => 'Cambio de Uso de Suelo'],
            ['nombre_tipo_acto_tramite' => 'Registro de Contrato de Leasing'],
            ['nombre_tipo_acto_tramite' => 'Registro de División de Predio'],
            ['nombre_tipo_acto_tramite' => 'Aprobación de Subdivisión'],
            ['nombre_tipo_acto_tramite' => 'Autorización de Construcción'],
            ['nombre_tipo_acto_tramite' => 'Licencia de Urbanismo'],
            ['nombre_tipo_acto_tramite' => 'Registro de Resolución Judicial'],
            ['nombre_tipo_acto_tramite' => 'Registro de Recurso de Apelación'],
            ['nombre_tipo_acto_tramite' => 'Certificación de Estado de Cuenta'],
            ['nombre_tipo_acto_tramite' => 'Registro de Cesión Fiduciaria'],
            ['nombre_tipo_acto_tramite' => 'Inscripción de Títulos'],
            ['nombre_tipo_acto_tramite' => 'Revocatoria de Acto Administrativo'],
            ['nombre_tipo_acto_tramite' => 'Inscripción de Declaratoria de Bien de Interés Cultural'],
            ['nombre_tipo_acto_tramite' => 'Certificación de Disponibilidad Presupuestal'],
            ['nombre_tipo_acto_tramite' => 'Registro de Fianza Bancaria'],
            ['nombre_tipo_acto_tramite' => 'Registro de Escritura de Donación'],
            ['nombre_tipo_acto_tramite' => 'Anulación de Documento Registrado'],
            ['nombre_tipo_acto_tramite' => 'Registro de Dación en Pago'],
            ['nombre_tipo_acto_tramite' => 'Registro de Permuta'],
            ['nombre_tipo_acto_tramite' => 'Registro de Contrato de Comodato'],
            ['nombre_tipo_acto_tramite' => 'Inscripción de Embargo Preventivo'],
            ['nombre_tipo_acto_tramite' => 'Registro de Mandato'],
            ['nombre_tipo_acto_tramite' => 'Registro de Interdicción'],
            ['nombre_tipo_acto_tramite' => 'Registro de Sentencia de Usucapión'],
            ['nombre_tipo_acto_tramite' => 'Autorización de Venta Judicial'],
            ['nombre_tipo_acto_tramite' => 'Inscripción de Propiedad Horizontal'],
            ['nombre_tipo_acto_tramite' => 'Registro de Acuerdo de Reestructuración'],
            ['nombre_tipo_acto_tramite' => 'Registro de Contrato de Mutuo'],
            ['nombre_tipo_acto_tramite' => 'Certificación de Pago Predial'],
            ['nombre_tipo_acto_tramite' => 'Autorización de Avaluador'],
            ['nombre_tipo_acto_tramite' => 'Registro de Garantía Hipotecaria'],
            ['nombre_tipo_acto_tramite' => 'Registro de Constitución de Servidumbre'],
            ['nombre_tipo_acto_tramite' => 'Registro de Extinción de Servidumbre'],
            ['nombre_tipo_acto_tramite' => 'Registro de Contrato de Prenda'],
            ['nombre_tipo_acto_tramite' => 'Registro de Cancelación de Prenda'],
            ['nombre_tipo_acto_tramite' => 'Registro de Contrato de Anticresis'],
            ['nombre_tipo_acto_tramite' => 'Registro de Cancelación de Anticresis'],
            ['nombre_tipo_acto_tramite' => 'Registro de Escritura de Adjudicación'],
            ['nombre_tipo_acto_tramite' => 'Registro de Permiso de Ocupación'],
            ['nombre_tipo_acto_tramite' => 'Registro de Declaratoria de Propiedad Horizontal'],
            ['nombre_tipo_acto_tramite' => 'Inscripción de Sucesión'],
            ['nombre_tipo_acto_tramite' => 'Inscripción de Liquidación de Sociedad'],
            ['nombre_tipo_acto_tramite' => 'Registro de Reorganización Empresarial'],
            ['nombre_tipo_acto_tramite' => 'Autorización de Cancelación de Hipoteca'],
            ['nombre_tipo_acto_tramite' => 'Aprobación de Proyecto de Parcelación'],
        ];

        DB::table('tipo_acto_tramite')->insert($data);
    }
}
