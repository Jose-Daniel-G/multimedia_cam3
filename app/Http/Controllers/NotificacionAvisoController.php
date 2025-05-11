<?php

namespace App\Http\Controllers;

use App\Models\NotificacionAviso;
use App\Imports\NotificacionAvisoImport;
use App\Jobs\ImportarNotificaciones;
use App\Models\EventoAuditoria;
use App\Models\Organismo;
use App\Models\TipoPlantilla;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class NotificacionAvisoController extends Controller
{
    protected $usuario;
    protected $organismo;
    protected $username;
    protected $baseDir;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->usuario = Auth::user();
            $this->organismo = Organismo::find($this->usuario->organismo_id);
            $this->username = strtok($this->usuario->email, '@'); // forma más rápida
            $this->baseDir = storage_path('app/public');
            return $next($request);
        });
    }

    public function index()
    {
        $files = Storage::disk('public')->files("users/{$this->username}");
        $tipo_plantilla = TipoPlantilla::all();
        $organismo = $this->organismo;
        // Obtener solo archivos Excel
        // $this->getFiles($files);
        $sheet = 0;
        $excelFiles = collect($files)->filter(function ($file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            return in_array($extension, ['csv', 'xlsx', 'xls']);
        })->map(fn($file) => basename($file))->values()->toArray();
        $pdfFiles = collect($files)
            ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'pdf')
            ->map(fn($file) => basename($file))
            ->toArray();
        if($pdfFiles){
            $sheet = 1;
        }
        $pdfCount = count($pdfFiles);
        

        return view('admin.import.index', compact('organismo', 'tipo_plantilla', 'sheet', 'excelFiles', 'pdfCount','pdfFiles'));
    }

    public function store(Request $request)
    {
        $id_tipo_plantilla = 3;
        $organismo = $this->organismo;
        $area = TipoPlantilla::find($id_tipo_plantilla);
        $folder = Str::snake($organismo->depe_nomb);
        $area_snake = Str::snake($area->nombre_plantilla);
        $rutaCarpetaUsuario = $this->baseDir . '/users/' . $this->username;
        $destino = $this->baseDir . "/pdfs/" . $folder . "/" . $area_snake . "/" . $this->username;
    
        $columnaNombre = in_array($id_tipo_plantilla, [1, 2]) ? 'NO_ACT_TRA' : 'objeto_contrato';
        $contenido = scandir($rutaCarpetaUsuario);
        $archivoExcel = $this->esArchivoValido($contenido, $rutaCarpetaUsuario);
    
        if (empty($archivoExcel)) {
            return redirect()->back()->with('error', 'Error: No se encontró ningún archivo CSV, XLSX o XLS en la carpeta.');
        }
    
        if (count($archivoExcel) > 1) {
            return redirect()->back()->with('error', 'Error: Solo debe haber un archivo CSV, XLSX o XLS en la carpeta.');
        }
    
        $archivosPdf = $this->esPDFValido($contenido, $rutaCarpetaUsuario);
    
        $fileExcel = reset($archivoExcel);
        $extension = strtolower(pathinfo($fileExcel, PATHINFO_EXTENSION));
        $rutaArchivoExcel = $rutaCarpetaUsuario . '/' . $fileExcel;
        $resultado = $this->pdfsFaltantes($rutaArchivoExcel, $fileExcel, $archivosPdf, $columnaNombre);
    
        if (isset($resultado['error'])) {
            return redirect()->back()->with('error', $resultado['error']);
        }
    
        if (!empty($resultado['pdfNoEncontrados'])) {
            return redirect()->back()->with('error', 'Error: Los siguientes PDFs no están en el CSV: ' . implode(", ", $resultado['pdfNoEncontrados']));
        }
    
        if (!empty($resultado['pdfsFaltantes'])) {
            return redirect()->back()->with('error', 'Error: Faltan los siguientes PDFs: ' . implode(", ", $resultado['pdfsFaltantes']));
        }
    
        try {
            DB::beginTransaction();
    
            $ultimo = DB::table('notificaciones_avisos')->max('publi_notificacion');
            $publi_notificacion = $ultimo ? $ultimo + 1 : 1;
            Log::debug("id_plantilla:".json_encode($resultado['id_plantilla']));
            $evento = EventoAuditoria::create([
                'id_publi_noti' => $publi_notificacion,
                'idusuario' => auth()->id(),
                'id_plantilla' => $resultado['id_plantilla'],
                'cont_registros' => 0, // se actualizará luego si se desea
                'estado_auditoria' => 'E',
                'datos_adicionales' => json_encode([
                    'organismo_id' => auth()->user()->organismo_id,
                    'archivo_cargado' => true,
                    'tipo_plantilla' => $resultado['id_plantilla']
                ]),
                'fecha_auditoria' => now(),
            ]);

            // Leer datos desde el Excel
            $dataRaw = Excel::toArray([], $rutaArchivoExcel)[0];

            // Obtener encabezados y quitar la primera fila
            $headers = array_map('trim', $dataRaw[3]);
            // $rows = array_slice($dataRaw, 4);
            $rows = array_filter(array_slice($dataRaw, 4), fn($fila) 
            => is_array($fila) && count(array_filter($fila)) > 0); //Validar que las filas no estén vacías antes de procesarlas.

            // Convertir cada fila en array asociativo
            $data = array_map(function ($row) use ($headers) {
                return array_combine($headers, $row);
            }, $rows);
            log::debug("Data procesada: ".json_encode($data));
            $data = array_map(function ($row) {
                // Convertir campos numéricos que vienen como string a enteros
                if (isset($row['id_predio'])) {
                    $row['id_predio'] = (int)ltrim($row['id_predio'], '0');
                }
            
                if (isset($row['cedula_identificacion'])) {
                    $row['cedula_identificacion'] = (int)ltrim($row['cedula_identificacion'], '0');
                }
            
                if (isset($row['liquidacion'])) {
                    $row['liquidacion'] = (int)ltrim($row['liquidacion'], '0');
                }
            
                // Convertir fechas
                if (isset($row['fecha_publicacion']) && is_numeric($row['fecha_publicacion'])) {
                    try {
                        $row['fecha_publicacion'] = \Carbon\Carbon::instance(
                            Date::excelToDateTimeObject($row['fecha_publicacion'])
                        )->format('n/j/Y');
                    } catch (\Exception $e) {
                        $row['fecha_publicacion'] = null;
                    }
                }
            
                if (isset($row['fecha_desfijacion']) && is_numeric($row['fecha_desfijacion'])) {
                    try {
                        $row['fecha_desfijacion'] = \Carbon\Carbon::instance(
                            Date::excelToDateTimeObject($row['fecha_desfijacion'])
                        )->format('n/j/Y');
                    } catch (\Exception $e) {
                        $row['fecha_desfijacion'] = null;
                    }
                }
            
                return $row;
            }, $data);
            

            // dd($data);
            // $publi_notificacion
            // Lanzar Job de importación masiva
            ImportarNotificaciones::dispatch(
                $data,
                $id_tipo_plantilla,
                $organismo->id,
                1, // estado_auditoria_id
                $rutaArchivoExcel,
                $this->usuario->id,
                $this->username,
                $extension,
                1
            );
    
            // Crear carpeta destino si no existe
            // if (!is_dir($destino)) {
            //     mkdir($destino, 0777, true);
            // }
    
            // // Mover PDFs
            // foreach ($archivosPdf as $pdf) {
            //     rename("{$rutaCarpetaUsuario}/{$pdf}", "{$destino}/{$pdf}");
            // }
    
            // // Mover Excel/CSV
            // if (file_exists($rutaArchivoExcel)) {
            //     rename($rutaArchivoExcel, "{$destino}/{$fileExcel}");
            // }
    
            DB::commit();
            return back()->with(['success' => 'Archivo en proceso de importación.', 'info' => $id_tipo_plantilla]);
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al iniciar la importación: ' . $e->getMessage());
        }
    }
    

    public function show()
    {
        //
    }


    public function edit(NotificacionAviso $file)
    {
        //
    }


    public function update(Request $request, NotificacionAviso $file)
    {
        //
    }


    public function destroy(NotificacionAviso $file)
    {
        //
    }
    function getFiles($files)
    {
        $excelFiles = collect($files)->filter(function ($file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            return in_array($extension, ['csv', 'xlsx', 'xls']);
        })->map(fn($file) => basename($file))->values()->toArray();

        $path = storage_path("app/public/{$excelFiles}");
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension === 'csv') {
            $csv = Reader::createFromPath($path, 'r');
            $csv->setHeaderOffset(0);
            $records = iterator_to_array($csv->getRecords());
        } else {
            $spreadsheet = IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $headers = array_shift($rows);
            $records = array_map(fn($row) => array_combine($headers, $row), $rows);
        }
        $records = array_filter($records, function ($record) {
            // Filtra los registros que no tienen datos nulos en todos sus campos
            return !empty(array_filter($record, function ($value) {
                return !is_null($value) && $value !== '';
            }));
        });
        $csvCount = count($records);

        // dd(['records'=>$records, 'csvCount'=>$csvCount]);
        try {
            $pdfFiles = collect($files)
                ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'pdf')
                ->map(fn($file) => basename($file))
                ->toArray();
            $pdfCount = count($pdfFiles);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al leer la cantidad de archivos PDF.');
        }
        $sheet = 1;
        // dd(['dataFile'=>$dataFile,'csvCount'=>$csvCount,'pdfFiles'=>$pdfFiles,'records'=>$records]);

        return view('admin.import.index', compact('organismo', 'csvCount', 'pdfFiles', 'pdfCount', 'tipo_plantilla', 'sheet'));
    }
    function esArchivoValido($contenido, $rutaCarpetaUsuario)
    {
        $archivosValidos = array_filter($contenido, function ($archivo) use ($rutaCarpetaUsuario) {
            $extensionesPermitidas = ['csv', 'xlsx', 'xls'];
            $extension = pathinfo($archivo, PATHINFO_EXTENSION);
            return is_file($rutaCarpetaUsuario . '/' . $archivo) && in_array(strtolower($extension), $extensionesPermitidas);
        });

        return $archivosValidos;
    }
    function esPDFValido($contenido, $rutaCarpetaUsuario)
    {
        // dd(['archivos'=>$contenido, 'rutaCarpetaUsuario'=>$rutaCarpetaUsuario]);
        $archivosPdf = array_map(
            fn($pdf) => strtolower(trim($pdf)),
            array_filter($contenido, function ($archivo) use ($rutaCarpetaUsuario) {
                return is_file($rutaCarpetaUsuario . '/' . $archivo) && strtolower(pathinfo($archivo, PATHINFO_EXTENSION)) === 'pdf';
            })
        );

        return $archivosPdf;
    }

    public function pdfsFaltantes($rutaArchivoSheet, $archivoExcel, $archivosPdf)
    {
        $extension = strtolower(pathinfo($archivoExcel, PATHINFO_EXTENSION));
        $sheet_file = Excel::toArray([], $rutaArchivoSheet)[0] ?? [];

        if (empty($sheet_file) || !isset($sheet_file[0][1])) {
            return ['error' => '❌ El archivo está vacío o mal formado.'];
        }
        $id_plantilla = substr($sheet_file[0][1], 0, 1);
        $columnaNombre = in_array($id_plantilla, [1, 2]) ? 'NO_ACT_TRA' : 'objeto_contrato';
        // Log::debug("Plantilla detectada: $id_plantilla");

        if (!isset($sheet_file[3])) {
            return ['error' => '❌ No se encontró la fila de headers (fila 4) en la hoja.'];
        }
        $headers = array_map('trim', $sheet_file[3]);
        // $datos = array_slice($sheet_file, 4);
        $rows = array_filter(array_slice($sheet_file, 4), fn($fila) 
               => is_array($fila) && count(array_filter($fila)) > 0); //Validar que las filas no estén vacías antes de procesarlas.
            //    Log::debug("Datos procesados: ".json_encode($rows));

        $indiceColumna = array_search($columnaNombre, $headers);

        $nombresValidos = array_values(array_filter(
            array_map(function ($fila) use ($indiceColumna) {
                return isset($fila[$indiceColumna]) ? strtolower(trim($fila[$indiceColumna])) : '';
            }, $rows),
            fn($h) => $h !== ''
        ));
        

        $archivosPdf = array_map(function ($archivo) {
            return preg_replace('/(\.pdf)+$/i', '', $archivo);
        }, $archivosPdf);

        $pdfsFaltantes = array_filter($nombresValidos, fn($nombre) => !in_array($nombre, $archivosPdf));
        $pdfNoEncontrados = array_filter($archivosPdf, fn($pdf) => !in_array($pdf, $nombresValidos));

        return [
            'id_plantilla' => $id_plantilla,
            'pdfsFaltantes' => $pdfsFaltantes,
            'pdfNoEncontrados' => $pdfNoEncontrados,
            'rows' => $rows,
        ];
    }
    function normalizarFilasExcel($encabezados, $data)
    {
        if (empty($data)) {
            return redirect()->back()->with('error', 'Error: El archivo no contiene datos válidos.');
        } //EL CSV ESTA VACIO

        // Normalizar encabezados: quitar espacios y convertir a minúsculas
        $encabezados = array_map(fn($h) => mb_strtolower(trim($h), 'UTF-8'), $encabezados);
        // 1. Eliminar columnas completamente vacías (sin título y sin datos)
        $columnas_no_vacias = array_filter($encabezados, function ($titulo, $i) use ($data) {
            return !empty($titulo) || array_filter(array_column($data, $i));
        }, ARRAY_FILTER_USE_BOTH);

        // 2. Eliminar filas totalmente vacías
        $data_filtrada = array_filter($data, function ($fila) {
            return array_filter($fila); // Retorna true si al menos una celda tiene contenido
        });

        // 3. Procesar cada fila
        $result = array_map(function ($fila) use ($columnas_no_vacias) {
            $fila_filtrada = array_intersect_key($fila, $columnas_no_vacias);
            $valores = array_values($fila_filtrada);
            $claves = array_values($columnas_no_vacias);

            // HORA_REG
            $posHora = array_search('hora_reg', $claves);
            if ($posHora !== false && isset($valores[$posHora]) && is_numeric($valores[$posHora])) {
                $segundos = round($valores[$posHora] * 86400);
                $valores[$posHora] = gmdate('H:i:s', $segundos);
            }

            // Fechas a convertir
            $fechas = ['fec_act_tra', 'fec_reg', 'fecha_publicacion', 'fecha_desfijacion'];
            foreach ($fechas as $fecha) {
                $idx = array_search($fecha, $claves);
                if ($idx !== false && isset($valores[$idx]) && is_numeric($valores[$idx])) {
                    $valores[$idx] = Carbon::createFromDate(1900, 1, 1)->addDays($valores[$idx] - 2)->format('Y-m-d');
                }
            }

            return array_combine($claves, $valores);
        }, $data_filtrada);

        return $result;
    }
}
