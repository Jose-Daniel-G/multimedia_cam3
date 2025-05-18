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

    protected $datos;
    protected $tipoPlantillaId;
    protected $organismoId;
    protected $estadoAuditoriaId;
    protected $rutaArchivoExel;
    protected $fk_idusuario;
    protected $publi_notificacion;

    public function __construct($datos, $publi_notificacion, $tipoPlantillaId, $organismoId, $estadoAuditoriaId, $rutaArchivoExel, $fk_idusuario)
    {
        $this->datos = $datos;
        $this->tipoPlantillaId = (int) $tipoPlantillaId;
        $this->organismoId = (int) $organismoId;
        $this->estadoAuditoriaId = (int) $estadoAuditoriaId;
        $this->rutaArchivoExel = $rutaArchivoExel;
        $this->fk_idusuario = (int) $fk_idusuario;
        $this->publi_notificacion = (int) $publi_notificacion;
    }

    public function handle()
    {
        try {
            $evento = EventoAuditoria::where('id_publi_noti', $this->publi_notificacion)->first();

            if ($evento) {
                $datosAdicionales = json_decode($evento->datos_adicionales ?? '{}', true);
                $datosAdicionales['progreso'] =  json_encode(['progreso' => 0]);

                $evento->update([
                    'datos_adicionales' => json_encode($datosAdicionales),
                ]);
            }

            $total = count($this->datos);
            $procesados = 0;

            $service = new NotificacionAvisoService(
                $this->tipoPlantillaId,
                $this->organismoId,
                $this->estadoAuditoriaId,
                $this->rutaArchivoExel,
                $this->fk_idusuario
            );
            $extension = strtolower(pathinfo($this->rutaArchivoExel, PATHINFO_EXTENSION));


            foreach ($this->datos as $index => $row) {
                if (empty($row) || !is_array($row) || count(array_filter($row)) === 0) {
                    Log::info("Fila vacía detectada en índice {$index}");
                    continue;
                }

                $service->procesarFila($row, $extension, $this->publi_notificacion);


                $procesados++;
                $porcentaje = intval(($procesados / $total) * 100);

                $this->actualizarProgresoEvento($this->publi_notificacion, $porcentaje);
            }

            // Al finalizar, podrías actualizar el estado a 'P' (Publicado), si no hubo errores
            EventoAuditoria::where('id_publi_noti', $this->publi_notificacion)
                ->update(['estado_auditoria' => 'P']);
        } catch (\Exception $e) {
            Log::error("Error en ImportarNotificaciones: {$e->getMessage()}");

            // Estado "F": Fallido
            EventoAuditoria::where('id_publi_noti', $this->publi_notificacion)
                ->update([
                    'estado_auditoria' => 'F',
                    'datos_adicionales' => json_encode([
                        'progreso' => 0,
                        'error' => $e->getMessage()
                    ])
                ]);
        }
    }
    private function actualizarProgresoEvento($publiNotiId, $porcentaje)
    {
        Log::info("Actualizando progreso del evento con ID {$publiNotiId} a {$porcentaje}%");
        // $evento = EventoAuditoria::where('id_publi_noti', $publiNotiId)->first();

        // if ($evento) {
        //     $datosAdicionales = json_decode($evento->datos_adicionales ?? '{}', true);
        //     $datosAdicionales['progreso'] = $porcentaje;

        //     $evento->update([
        //         'datos_adicionales' => json_encode($datosAdicionales),
        //     ]);
        // }
    }
}
