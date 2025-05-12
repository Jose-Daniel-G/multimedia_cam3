<?php
namespace App\Services;

use App\Models\NotificacionAviso;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        $validator = Validator::make($row, [
            'nombre_ciudadano'       => 'required|string|max:255',
            'cedula_identificacion'  => 'required|string|max:50',
            'fecha_publicacion'      => 'required',
            'fecha_desfijacion'      => 'required',
            'tipo_impuesto'          => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            // Detener ejecución y generar un mensaje de alerta
            $errors = $validator->errors()->all();
            Log::warning('Fila inválida en Coactivo/Persuasivo: ' . json_encode($errors));
            throw new ValidationException($validator); // Lanza excepción si los datos no son válidos
        }
    
        $meses = 1;
        $fechas = $this->conversionDateExcelMonth($row['fecha_publicacion'], $row['fecha_desfijacion'], $meses);
        // if (!$fechas) {
        //     Log::warning("Diferencia de fechas inválida (se espera ".$meses." mes) en fila: " . json_encode($row));
        //     throw new \Exception('Las fechas no son válidas.'); // Detiene el proceso si las fechas no son correctas
        // }
    
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
            throw $e; // Lanza la excepción para manejar el error en otro lugar si es necesario
        }
    }
    
    public function liquidacion(array $row, string $extension, string $publi_notificacion)
    {
        $validator = Validator::make($row, [
            'nombre_ciudadano'       => 'required|string|max:255',
            'cedula_identificacion'  => 'required|string|max:50',
            'fecha_publicacion'      => 'required',
            'fecha_desfijacion'      => 'required',
        ]);
    
        if ($validator->fails()) {
            // Detener ejecución y generar un mensaje de alerta
            $errors = $validator->errors()->all();
            Log::warning('Fila inválida en Liquidación: ' . json_encode($errors));
            throw new ValidationException($validator); // Lanza excepción si los datos no son válidos
        }
    
        $dias = 5;
        $fechas = $this->conversionDateExcelDay($row['fecha_publicacion'], $row['fecha_desfijacion'], $dias);
        Log::info('Fechas convertidas: ' . json_encode($fechas));
        // if (!$fechas) {
        //     Log::warning("Diferencia de fechas inválida (se espera ".$dias." días) en fila: " . json_encode($row));
        //     throw new \Exception('Las fechas no son válidas.'); // Detiene el proceso si las fechas no son correctas
        // }
    
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
            throw $e; // Lanza la excepción para manejar el error en otro lugar si es necesario
        }
    }
    

    private function conversionDateExcelDay($fechaPublicacion, $fechaDesfijacion, $diasEsperados = 5)
    {
        try {
            $fechaPublicacion = $this->parseFechaExcel($fechaPublicacion);
            $fechaDesfijacion = $this->parseFechaExcel($fechaDesfijacion);
    
            if ($fechaDesfijacion->diffInDays($fechaPublicacion) !== $diasEsperados) {
                Log::warning("La diferencia entre {$fechaPublicacion->toDateString()} y {$fechaDesfijacion->toDateString()} no es de {$diasEsperados} días.");
                return null;
            }
    
            return [
                'fecha_publicacion' => $fechaPublicacion,
                'fecha_desfijacion' => $fechaDesfijacion,
            ];
        } catch (\Exception $e) {
            Log::error("Error al convertir fechas: " . $e->getMessage());
            return null;
        }
    }
    private function conversionDateExcelMonth($fecha_publicacion, $fecha_desfijacion, $mesesEsperados = 1)
    {
        try {
            $fechaPublicacion = $this->parseFechaExcel($fecha_publicacion);
            $fechaDesfijacion = $this->parseFechaExcel($fecha_desfijacion);
    
            $fechaEsperada = $fechaPublicacion->copy()->addMonths($mesesEsperados);
            
            if (!$fechaDesfijacion->isSameDay($fechaEsperada)) {
                Log::warning("La fecha de desfijación esperada es {$fechaEsperada->toDateString()}, pero se recibió {$fechaDesfijacion->toDateString()}.");
                return null;
            }
    
            return [
                'fecha_publicacion' => $fechaPublicacion,
                'fecha_desfijacion' => $fechaDesfijacion,
            ];
        } catch (\Exception $e) {
            Log::error("Error al convertir fechas: " . $e->getMessage());
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
