<?php

namespace App\Jobs;

use App\Models\EventoAuditoria;
use App\Services\NotificacionAvisoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportarNotificaciones implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $id_plantilla;
    protected $organismo_id;
    protected $estadoAuditoriaId;
    protected $rutaArchivoExel;
    protected $fk_idusuario;
    protected $publi_notificacion;
    protected $fileExcelNamefolder;
    protected $rutaCarpetaOrigen;
    protected $destino;
    protected $archivoExcel;
    protected $rutaCarpetaUsuario;

    public function __construct($data,$publi_notificacion,$id_plantilla,$organismo_id,$estadoAuditoriaId,
                                $rutaArchivoExel,$fk_idusuario,$fileExcelNamefolder,$rutaCarpetaOrigen,
                                $destino,$archivoExcel,$rutaCarpetaUsuario) {

        $this->data = $data;
        $this->id_plantilla = (int) $id_plantilla;
        $this->organismo_id = (int) $organismo_id;
        $this->estadoAuditoriaId = (int) $estadoAuditoriaId;
        $this->rutaArchivoExel = $rutaArchivoExel;
        $this->fk_idusuario = (int) $fk_idusuario;
        $this->publi_notificacion = (int) $publi_notificacion;
        $this->fileExcelNamefolder = $fileExcelNamefolder;
        $this->rutaCarpetaOrigen = $rutaCarpetaOrigen;
        $this->destino = $destino;
        $this->archivoExcel = $archivoExcel;
        $this->rutaCarpetaUsuario = $rutaCarpetaUsuario;
    }

    public function handle()
    {
        try {
            $evento = EventoAuditoria::where('id_publi_noti', $this->publi_notificacion)->first();

            if ($evento) {
                $dataAdicionales = json_decode($evento->data_adicionales ?? '{}', true);
                $dataAdicionales['progreso'] =  json_encode(['progreso' => 0]);

                $evento->update([
                    'data_adicionales' => json_encode($dataAdicionales),
                ]);
            }

            $total = count($this->data);
            $procesados = 0;

            $service = new NotificacionAvisoService(
                $this->id_plantilla,
                $this->organismo_id,
                $this->estadoAuditoriaId,
                $this->rutaArchivoExel,
                $this->fk_idusuario
            );
            $extension = strtolower(pathinfo($this->rutaArchivoExel, PATHINFO_EXTENSION));


            foreach ($this->data as $index => $row) {
                if (empty($row) || !is_array($row) || count(array_filter($row)) === 0) {
                    Log::info("Fila vacía detectada en índice {$index}");
                    continue;
                }

                $service->procesarFila($row, $extension, $this->publi_notificacion);


                $procesados++;
                $porcentaje = intval(($procesados / $total) * 100);

                $this->actualizarProgresoEvento($this->publi_notificacion, $porcentaje);
            }
            
            if (!is_dir($this->destino)) {
                mkdir($this->destino, 0755, true);
            }
            Log::debug("Ruta de destino: $this->destino");
            rename($this->rutaCarpetaOrigen, "{$this->destino}/{$this->fileExcelNamefolder}");

            // Mover el archivo Excel o CSV a la carpeta de destino
            if (isset($archivoExcel) && file_exists("{$this->rutaCarpetaUsuario}/{$archivoExcel}")) {
                rename("{$this->rutaCarpetaUsuario}/{$archivoExcel}", "{$this->destino}/{$archivoExcel}");
            }

            // Al finalizar, podrías actualizar el estado a 'P' (Publicado), si no hubo errores
            EventoAuditoria::where('id_publi_noti', $this->publi_notificacion)->update(['estado_auditoria' => 'P']);

        } catch (\Exception $e) {
            Log::error("Error en ImportarNotificaciones: {$e->getMessage()}");

            // Estado "F": Fallido
            EventoAuditoria::where('id_publi_noti', $this->publi_notificacion)
                ->update([
                    'estado_auditoria' => 'F',
                    'data_adicionales' => json_encode([
                        'progreso' => 0,
                        'error' => $e->getMessage()
                    ])
                ]);
        }
    }
    private function actualizarProgresoEvento($publiNotiId, $porcentaje)
    {
        Log::info("Actualizando progreso del evento con ID {$publiNotiId} a {$porcentaje}%");
        $evento = EventoAuditoria::where('id_publi_noti', $publiNotiId)->first();

        if ($evento) {
            $dataAdicionales = json_decode($evento->data_adicionales ?? '{}', true);
            $dataAdicionales['progreso'] = $porcentaje;

            $evento->update([
                'data_adicionales' => json_encode($dataAdicionales),
            ]);
        }
    }
}
