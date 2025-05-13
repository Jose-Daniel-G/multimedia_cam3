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
use Illuminate\Support\Facades\Validator;

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
        $organismo = $this->organismo;
        $excelFiles = $this->files_plantilla();
        $excelCount = count($excelFiles);
        return view('admin.import.index', compact('organismo', 'excelFiles', 'excelCount'));
    }
    public function create()
    {
        $organismo = $this->organismo;
        $excelFiles = $this->files_plantilla();
        $excelCount = count($excelFiles);
        return view('admin.import.create', compact('organismo', 'excelFiles', 'excelCount'));
    }

    public function store(Request $request)
    {
        $archivoExcel = $request->input('file'); // ← Aquí lo recibes  $request->file('file');
        $organismo = $this->organismo;
        $folder = Str::snake($organismo->depe_nomb); //SIN USO
        $rutaCarpetaUsuario = $this->baseDir . '/users/' . $this->username;
        $contenido = scandir($rutaCarpetaUsuario);
        $validacion = $this->esArchivoValido($contenido, $rutaCarpetaUsuario);
        if (empty($validacion)) {
            return redirect()->back()->with('error', 'Error: No se encontró ningún archivo CSV, XLSX o XLS en la carpeta.');
        }
        $fileExcelNamefolder = pathinfo($archivoExcel, PATHINFO_FILENAME);
        $rutaCarpetaOrigen = $rutaCarpetaUsuario . '/' . $fileExcelNamefolder;
        // CAPETA ORIGEN DE PDFS
        if (!is_dir($rutaCarpetaOrigen)) {
            return response()->json(['error' => 'Error: No existe la carpeta de origen.']);
        }
        $rutaArchivoExcel = $rutaCarpetaUsuario . '/' . $archivoExcel;
        $contenido_pdf = scandir($rutaCarpetaOrigen);                                                      // Escanear contenido de la carpeta
        $archivosPdf = $this->esPDFValido($contenido_pdf, $rutaCarpetaOrigen);                             // valida que los PDF esten registrados en csv, xlsx, xls
        $extension = strtolower(pathinfo($archivoExcel, PATHINFO_EXTENSION));                              //Obtener extension xsls/csv
        $resultado = $this->proccessFile($rutaArchivoExcel, $archivoExcel, $archivosPdf);
        $id_plantilla = $resultado['id_plantilla'];                                                        //ID PLANTILLA
        $id_plantilla = Str::snake($id_plantilla);                                                    //NOMBRE PLANTILLA
        $destino = $this->baseDir . "/pdfs/" . $folder . "/" . $id_plantilla . "/" . $this->username;      //CARPETA DESTINO PDFS 

        if (isset($resultado['error'])) {
            return response()->json(['error', $resultado['error']]);
        }

        if (!empty($resultado['pdfNoEncontrados'])) {
            return response()->json(['error', 'Error: Los siguientes PDFs no están en el CSV: ' . implode(", ", $resultado['pdfNoEncontrados'])]);
        }

        if (!empty($resultado['pdfsFaltantes'])) {
            return response()->json(['error', 'Error: Faltan los siguientes PDFs: ' . implode(", ", $resultado['pdfsFaltantes'])]);
        }


        try {
            DB::beginTransaction();

            $ultimo = DB::table('notificaciones_avisos')->max('publi_notificacion');
            $publi_notificacion = $ultimo ? $ultimo + 1 : 1;

            $id_plantilla = $resultado['id_plantilla'];
            // $organismo = auth()->user()->organismo;

            EventoAuditoria::create([
                'id_publi_noti' => $publi_notificacion,
                'idusuario' => auth()->id(),
                'id_plantilla' => $id_plantilla,
                'cont_registros' => 0,
                'estado_auditoria' => 'E',
                'datos_adicionales' => json_encode([
                    'organismo_id' => $organismo->id,
                    'archivo_cargado' => true,
                    'tipo_plantilla' => $id_plantilla
                ]),
                'fecha_auditoria' => now(),
            ]);

            $headers = $resultado['headers'];
            $rows = $resultado['rows'];

            $data = array_map(function ($row) use ($headers) {
                return array_combine($headers, $row);
            }, $rows);

            $ultimo = DB::table('notificaciones_avisos')->max('publi_notificacion');
            $publi_notificacion = $ultimo ? $ultimo + 1 : 1;

            $errores = [];
            
            foreach ($data as $index => $row) {
                $rules = [
                    'nombre_ciudadano' => ['required', 'string', 'max:255'],
                    'cedula_identificacion' => ['required', 'regex:/^[0-9]+$/'],
                    'fecha_publicacion' => ['required'],
                    'fecha_desfijacion' => ['required'],
                ];
            
                if (in_array($id_plantilla, [1, 2])) {
                    $row['tipo_impuesto'] = trim($row['tipo_impuesto']);
                    $rules = array_merge($rules, [
                        'tipo_impuesto' => ['required', 'regex:/^[0-9]+$/', 'integer'],
                        'tipo_acto_tramite' => ['required', 'regex:/^[0-9]+$/', 'integer'],
                        'tipo_causa_devolucion' => ['required', 'regex:/^[0-9]+$/', 'integer'],
                        'tipo_estado_publicacion' => ['required', 'regex:/^[0-9]+$/', 'integer'],
                    ]);
                } else {
                    $rules = array_merge($rules, [
                        'liquidacion' => ['nullable', 'regex:/^[a-zA-Z0-9]+$/'],
                        'objeto_contrato' => ['required', 'regex:/^[0-9_]+$/'],
                        'id_predio' => ['required', 'regex:/^[0-9]+$/'],
                    ]);
                }
            
                $validator = Validator::make($row, $rules);
            
                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $mensaje) {
                        if (!isset($errores[$mensaje])) {
                            $errores[$mensaje] = 0;
                        }
                        $errores[$mensaje]++;
                    }
                }
            }
            
            if (!empty($errores)) {
                return response()->json([
                    'title' => 'Errores encontrados',
                    'errors' => $errores
                ], 422);  // 422 es un código HTTP para "Unprocessable Entity" (Entidad no procesable)
            }

            ImportarNotificaciones::dispatch(
                $data,
                $publi_notificacion,
                $id_plantilla,
                $organismo->id,
                1,
                $rutaArchivoExcel,
                auth()->id(),
                auth()->user()->username,
                $extension
            );

            DB::commit();
            return response()->json(['success' => 'Archivo en proceso de importación.', 'info' => $id_plantilla]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al iniciar la importación: ' . $e->getMessage()], 500);
        }
    }

    public function proccessFile($rutaArchivoSheet, $archivoExcel, $archivosPdf)
    {
        $sheet_file = Excel::toArray([], $rutaArchivoSheet)[0] ?? [];

        // Log::debug("sheet_file: " . json_encode($sheet_file));
        if (empty($sheet_file) || !isset($sheet_file[0][1])) {
            return ['error' => '❌ El archivo está vacío o mal formado.'];
        }
        log::debug("sheet_file: " . json_encode($sheet_file[0][1]));
        $id_plantilla = substr($sheet_file[0][1], 0, 1);
        $columnaNombre = in_array($id_plantilla, [1, 2]) ? 'NO_ACT_TRA' : 'objeto_contrato';
        Log::debug("file: $rutaArchivoSheet");
        Log::debug("Plantilla detectada: $id_plantilla");

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
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
    function files_plantilla()
    {
        $files = Storage::disk('public')->files("users/{$this->username}");
        // Obtener solo archivos Excel (xlsx, xls, csv)
        $excelRawFiles = collect($files)
            ->filter(fn($file) => in_array(pathinfo($file, PATHINFO_EXTENSION), ['xlsx', 'xls', 'csv']))
            ->map(fn($file) => basename($file))
            ->toArray();

        $rutaCarpetaUsuario = $this->baseDir . '/users/' . $this->username;
        $excelFiles = [];

        foreach ($excelRawFiles as $fileExcel) {
            $rutaArchivoExcel = $rutaCarpetaUsuario . '/' . $fileExcel;

            if (!file_exists($rutaArchivoExcel)) continue;

            $dataRaw = Excel::toArray([], $rutaArchivoExcel)[0] ?? [];

            if (isset($dataRaw[0][1])) {
                $id_plantilla = substr($dataRaw[0][1], 0, 1);
                $n_registros = $dataRaw[1][1];
                $n_pdfs = $dataRaw[2][1];

                $excelFiles[] = [
                    'file' => $fileExcel,
                    'n_registros' => $n_registros,
                    'n_pdfs' => $n_pdfs,
                    'id_plantilla' => $id_plantilla
                ];
            }
        }

        return $excelFiles;
    }

    public function show()
    {
        //
    }
    private function conversionDateExcelDay($fechaPublicacion, $fechaDesfijacion, $diasEsperados = 5)
    {
        try {
            $fechaPublicacion = $this->parseFechaExcel($fechaPublicacion);
            $fechaDesfijacion = $this->parseFechaExcel($fechaDesfijacion);

            if ($fechaDesfijacion->diffInDays($fechaPublicacion) !== $diasEsperados) {
                Log::warning("La diferencia entre {$fechaPublicacion->toDateString()} y {$fechaDesfijacion->toDateString()} no es de {$diasEsperados} días.");
                return ["La diferencia entre fechas no es de {$diasEsperados} días."];
            }

            return []; // Sin errores
        } catch (\Exception $e) {
            Log::error("Error al convertir fechas: " . $e->getMessage());
            return ["Error al procesar fechas: " . $e->getMessage()];
        }
    }

    private function conversionDateExcelMonth($fecha_publicacion, $fecha_desfijacion, $mesesEsperados = 1)
    {
        try {
            $fechaPublicacion = $this->parseFechaExcel($fecha_publicacion);
            $fechaDesfijacion = $this->parseFechaExcel($fecha_desfijacion);

            // Calcular la fecha esperada sumando el número de meses
            $fechaEsperada = $fechaPublicacion->copy()->addMonths($mesesEsperados);

            // Registrar las fechas para depuración
            Log::info("Fecha de publicación: {$fechaPublicacion->toDateString()}");
            Log::info("Fecha de desfijación: {$fechaDesfijacion->toDateString()}");
            Log::info("Fecha esperada de desfijación: {$fechaEsperada->toDateString()}");

            // Comparar la fecha de desfijación, permitiendo un pequeño margen de diferencia
            // Esto podría ser por ejemplo un rango de +- 3 días
            $fechaMinimaDesfijacion = $fechaEsperada->copy()->subDays(3);  // 3 días antes
            $fechaMaximaDesfijacion = $fechaEsperada->copy()->addDays(3);   // 3 días después

            if ($fechaDesfijacion->lt($fechaMinimaDesfijacion) || $fechaDesfijacion->gt($fechaMaximaDesfijacion)) {
                Log::warning("La fecha de desfijación debería estar entre {$fechaMinimaDesfijacion->toDateString()} y {$fechaMaximaDesfijacion->toDateString()}, pero se recibió {$fechaDesfijacion->toDateString()}.");
                return null;
            }

            return [];
            // return [
            //     'fecha_publicacion' => $fechaPublicacion->toDateString(),
            //     'fecha_desfijacion' => $fechaDesfijacion->toDateString(),
            // ];
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

    function edit(){
        $organismo = $this->organismo;
        $excelFiles = $this->files_plantilla();
        $excelCount = count($excelFiles);
        return view('admin.import.edit', compact('organismo', 'excelFiles', 'excelCount'));
    }


    public function update(Request $request, NotificacionAviso $file)
    {
        //
    }


    public function destroy(NotificacionAviso $file)
    {
        //
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
