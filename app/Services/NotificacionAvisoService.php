<?php
namespace App\Services;

use App\Models\NotificacionAviso;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class NotificacionAvisoService
{
    public function __construct(
        protected int $tipoPlantillaId,
        protected int $organismoId,
        protected int $estadoAuditoriaId,
        protected string $rutaArchivoExel,
        protected ?int $fk_idusuario = null
    ) {}

    public function procesarFila(array $row, string $extension, string $publi_notificacion)
    {
        if ($this->tipoPlantillaId == 1 || $this->tipoPlantillaId == 2) {
            return $this->coactivoPersuasivo($row, $extension, $publi_notificacion);
        }

        return $this->liquidacion($row, $extension, $publi_notificacion);
    }

    public function coactivoPersuasivo(array $row, string $extension, string $publi_notificacion)
    {
        $fechas = $this->conversionDateExcelMonth($row['fecha_publicacion'], $row['fecha_desfijacion'], 1);
        if (!$fechas) return;

        try {
            NotificacionAviso::create([
                'publi_notificacion'       => $publi_notificacion,
                'fk_idorganismo'           => $this->organismoId,
                'fk_idusuario'             => $this->fk_idusuario ?? Auth::id(),
                'fk_idtp_imp'              => $row['tipo_impuesto'],
                'fk_tipo_plantilla'        => $this->tipoPlantillaId,
                'ruta_archivos'            => $this->rutaArchivoExel,
                'nombre_ciudadano'         => $row['nombre_ciudadano'],
                'cedula_identificacion'    => $row['cedula_identificacion'],
                'fecha_publicacion'        => $fechas['fecha_publicacion']->format('Y-m-d'),
                'fecha_desfijacion'        => $fechas['fecha_desfijacion']->format('Y-m-d'),
                'fk_tipo_acto_tramite'     => $row['tipo_acto_tramite'] ?? null,
                'fk_estado_publicacion'    => $row['estado_publicacion'] ?? null,
                'fk_tipo_causa_devolucion' => $row['tipo_causa_devolucion'] ?? null,
                'json_plantilla'           => json_encode(['data' => $row[7] ?? []]),
                'id_estado_auditoria'      => $this->estadoAuditoriaId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al guardar Notificación Coactiva: ' . $e->getMessage());
        }
    }

    public function liquidacion(array $row, string $extension, string $publi_notificacion)
    {
        $fechas = $this->conversionDateExcelDay($row['fecha_publicacion'], $row['fecha_desfijacion'], 5);
        if (!$fechas) return;

        try {
            NotificacionAviso::create([
                'publi_notificacion'       => $publi_notificacion,
                'fk_idorganismo'           => $this->organismoId,
                'fk_idusuario'             => $this->fk_idusuario ?? Auth::id(),
                'fk_idtp_imp'              => 1,
                'fk_tipo_plantilla'        => $this->tipoPlantillaId,
                'ruta_archivos'            => $this->rutaArchivoExel,
                'nombre_ciudadano'         => $row['nombre_ciudadano'],
                'cedula_identificacion'    => $row['cedula_identificacion'],
                'fecha_publicacion'        => $fechas['fecha_publicacion']->format('Y-m-d'),
                'fecha_desfijacion'        => $fechas['fecha_desfijacion']->format('Y-m-d'),
                'id_predio'                => $row['id_predio'] ?? null,
                'objeto_contrato'          => $row['objeto_contrato'] ?? null,
                'num_predial'              => $row['num_predial'] ?? null,
                'fk_publi_noti'            => $row['publi_noti'] ?? null,
                'json_plantilla'           => json_encode(['data' => $row[7] ?? []]),
                'id_estado_auditoria'      => $this->estadoAuditoriaId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al guardar Liquidación: ' . $e->getMessage());
        }
    }

    private function conversionDateExcelDay($fecha_publicacion, $fecha_desfijacion, $dias)
    {
        try {
            $f1 = $this->parseFechaExcel($fecha_publicacion);
            $f2 = $this->parseFechaExcel($fecha_desfijacion);
            // Log::debug("Comparando fechas: publicación={$f1->toDateString()} / desfijación={$f2->toDateString()}");
            return $f2->equalTo($f1->copy()->addDays($dias))
                ? ['fecha_publicacion' => $f1, 'fecha_desfijacion' => $f2]
                : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function conversionDateExcelMonth($fecha_publicacion, $fecha_desfijacion, $meses)
    {
        try {
            $f1 = $this->parseFechaExcel($fecha_publicacion);
            $f2 = $this->parseFechaExcel($fecha_desfijacion);
            // Log::debug("Comparando fechas: publicación={$f1->toDateString()} / desfijación={$f2->toDateString()}");

            return $f2->equalTo($f1->copy()->addMonths($meses))
                ? ['fecha_publicacion' => $f1, 'fecha_desfijacion' => $f2]
                : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseFechaExcel($fecha)
    {
        try {
            return is_numeric($fecha)
                ? Carbon::instance(Date::excelToDateTimeObject($fecha))
                : Carbon::parse($fecha);
        } catch (\Exception $e) {
            return null;
        }
    }
}
