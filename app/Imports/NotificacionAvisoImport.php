<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use App\Services\NotificacionAvisoService;
use Maatwebsite\Excel\Concerns\ToCollection;

class NotificacionAvisoImport implements
    ToCollection,
    WithHeadingRow,
    WithBatchInserts,
    WithChunkReading,
    ShouldQueue
{
    use Importable;

    public $errores = [];
    private $servicio;
    private $tipoPlantillaId;
    private $organismoId;
    private $rutaArchivoExel;
    private $estadoAuditoriaId;
    private $fk_idusuario;
    private $extension;

    /**
     * Establece la configuración para el servicio NotificacionAvisoService
     * 
     * @param int $tipoPlantillaId
     * @param int $organismoId
     * @param string $rutaArchivoExel
     * @param int $estadoAuditoriaId
     * @param int $fk_idusuario
     * @param string $extension
     */
    public function setConfiguracion($tipoPlantillaId, $organismoId, $rutaArchivoExel, $estadoAuditoriaId, $fk_idusuario, $extension)
    {
        // $this->publi_notificacion = $publi_notificacion;
        // $this->username = $username;

        $this->servicio = new NotificacionAvisoService(
            $tipoPlantillaId,
            $organismoId,
            $rutaArchivoExel,
            $estadoAuditoriaId,
            $fk_idusuario
        );
        $this->extension = $extension;
    }

    /**
     * Procesa cada fila del archivo y la pasa al servicio.
     * 
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $row = $row->toArray(); // Convierte la fila en un arreglo
                $this->servicio->procesarFila($row, $this->extension, 'publicar-notificacion');
            } catch (\Exception $e) {
                $this->errores[] = [
                    'fila' => $row,
                    'error' => $e->getMessage()
                ];
                Log::error('Error procesando fila: ' . $e->getMessage());
            }
        }

        // Si hay errores, podrías hacer algo con ellos después de procesar todas las filas
        if (!empty($this->errores)) {
            Log::error('Errores en el procesamiento de las filas', $this->errores);
        }
    }

    /**
     * Define el tamaño del lote para la importación masiva.
     * 
     * @return int
     */
    public function batchSize(): int
    {
        return 1000; // Define el tamaño del lote para mejorar el rendimiento
    }

    /**
     * Define el tamaño de la lectura en bloques.
     * 
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000; // Evita cargar demasiados registros en memoria
    }
}
