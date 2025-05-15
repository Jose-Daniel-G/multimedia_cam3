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
            // Estado "E": En Proceso
            EventoAuditoria::where('id_publi_noti', $this->publi_notificacion)
                ->update([
                    'estado_auditoria' => 'E',
                    'datos_adicionales' => json_encode(['progreso' => 0])
                ]);

            $total = count($this->datos);
            $procesados = 0;

            $extension = strtolower(pathinfo($this->rutaArchivoExel, PATHINFO_EXTENSION));

            $service = new NotificacionAvisoService(
                $this->tipoPlantillaId,
                $this->organismoId,
                $this->estadoAuditoriaId,
                $this->rutaArchivoExel,
                $this->fk_idusuario
            );

            foreach ($this->datos as $index => $row) {
                if (empty($row) || !is_array($row) || count(array_filter($row)) === 0) {
                    Log::info("Fila vacía detectada en índice {$index}");
                    continue;
                }

                $service->procesarFila($row, $extension, $this->publi_notificacion);

                $procesados++;
                $porcentaje = intval(($procesados / $total) * 100);

                EventoAuditoria::where('id_publi_noti', $this->publi_notificacion)
                    ->update([
                        'datos_adicionales' => json_encode(['progreso' => $porcentaje])
                    ]);
            }

            // Estado "P": Publicado (todo salió bien)
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
}
