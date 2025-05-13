<?php

namespace App\Jobs;

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
        // Obtener la extensión del archivo
        $extension = strtolower(pathinfo($this->rutaArchivoExel, PATHINFO_EXTENSION));
        
        // Asegurarse de que $fk_idusuario es un entero
        $fk_idusuario = is_numeric($this->fk_idusuario) ? (int) $this->fk_idusuario : null;
        
        try {
            Log::info('Job ImportarNotificaciones iniciado', [
                'tipoPlantillaId' => $this->tipoPlantillaId,
                'organismoId' => $this->organismoId,
                'estadoAuditoriaId' => $this->estadoAuditoriaId,
                'rutaArchivoExel' => $this->rutaArchivoExel,
                'fk_idusuario' => $this->fk_idusuario,
                'publi_notificacion' => $this->publi_notificacion
            ]);
    
            $service = new NotificacionAvisoService(
                $this->tipoPlantillaId,
                $this->organismoId,
                $this->estadoAuditoriaId,
                $this->rutaArchivoExel,
                $fk_idusuario
            );
    
            Log::info('Servicio NotificacionAvisoService creado con éxito');
    
            foreach ($this->datos as $index => $row) {
                // Comprobar si la fila está vacía
                if (empty($row) || !is_array($row) || count(array_filter($row)) === 0) {
                    // Log para filas vacías
                    Log::info('Fila vacía detectada en índice real ' . $index);
                    continue; // Saltar filas vacías
                }
                // Log::info('Procesando fila', ['index' => $index,'row' => $row,'extension' => $extension,'publi_notificacion' => $this->publi_notificacion]);
                // Procesar fila con el servicio
                $service->procesarFila($row, $extension, $this->publi_notificacion);
            }
        } catch (\Exception $e) {
            Log::error('Error en el Job ImportarNotificaciones:  '. $e->getMessage());
        }
    }
    
    
}
