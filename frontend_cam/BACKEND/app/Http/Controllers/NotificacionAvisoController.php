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
    protected $rutaCarpetaUsuario;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->usuario = Auth::user();
            $this->organismo = Organismo::find(auth::user()->organismo_id);
            $this->username = strtok($this->usuario->email, '@'); // forma más rápida
            $this->baseDir = storage_path('app/public');
            $this->rutaCarpetaUsuario = $this->baseDir . '/users/' . $this->username;

            return $next($request);
        });
    }

    public function index()
    {
        $organismo = $this->organismo;

        // Traer solo eventos publicados por el usuario autenticado
        $eventos = DB::table('evento_auditoria')
            ->where('idusuario', auth()->id())
            ->where('estado_auditoria', 'P')
            ->orderByDesc('created_at')
            ->get();

        $excelFiles = [];

        foreach ($eventos as $evento) {
            $datos = json_decode($evento->datos_adicionales ?? '{}', true);
            if (is_string($datos)) {
                $datos = json_decode($datos, true); // manejar doble codificación
            }

            $archivo = $datos['archivo'] ?? null;
            if (!$archivo) {
                continue;
            }

            $procesados = $evento->cont_registros;
            $total = $procesados; // No se guarda n_registros en los datos, así que lo igualamos
            $porcentaje = 100; // Como el estado es 'P', asumimos 100%

            $excelFiles[] = [
                'file' => $archivo,
                'n_registros' => $total,
                'n_pdfs' =>       $datos['pdfsAsociados'] ?? '-', // si no hay, se muestra como '-'
                'id_plantilla' => $evento->id_plantilla,
                'plantilla' => optional(TipoPlantilla::find($evento->id_plantilla))->nombre_plantilla,
                'procesados' => $procesados,
                'porcentaje' => $porcentaje,
                'estado' => 'Publicado',
                'fecha' => $evento->fecha_auditoria,
                'observaciones' => $datos['observaciones'] ?? '',
            ];
        }

        $excelCount = count($excelFiles);

        return view('admin.import.index', compact('organismo', 'excelFiles', 'excelCount'));
    }

    public function create()
    {
        $organismo = $this->organismo;
        $excelFiles = $this->files_plantilla();

        // Verificar si existen archivos bloqueados
        if (isset($excelFiles['abierto']) && $excelFiles['abierto']) {
            $archivosBloqueados = implode(', ', $excelFiles['archivosBloqueados']);
            return redirect()->route('admin.home')->withErrors([
                "Los siguientes archivos están abiertos: {$archivosBloqueados} por favor ciérralos para continuar."
            ]);
        }

        // Filtrar archivos que aún no están siendo procesados
        $archivosFiltrados = array_filter($excelFiles, function ($archivo) {
            $fileName = $archivo['file'];

            return !DB::table('evento_auditoria')
                ->whereJsonContains('datos_adicionales->archivo', $fileName)
                ->exists();
        });
        $excelFiles =  $archivosFiltrados;
        $excelCount = count($archivosFiltrados);

        return view('admin.import.create', compact('organismo', 'excelFiles', 'excelCount'));
    }


    public function store(Request $request)
    {
        $archivoExcel = $request->input('file'); // ← Aquí lo recibes  $request->file('file');

        $organismo = $this->organismo;
        $folder = Str::snake($organismo->depe_nomb); //SIN USO
        $contenido = scandir($this->rutaCarpetaUsuario);
        $validacion = $this->esArchivoValido($contenido, $this->rutaCarpetaUsuario);
        if (empty($validacion)) {
            return redirect()->back()->with('error', 'Error: No se encontró ningún archivo CSV, XLSX o XLS en la carpeta.');
        }
        $fileExcelNamefolder = pathinfo($archivoExcel, PATHINFO_FILENAME);
        $rutaCarpetaOrigen = $this->rutaCarpetaUsuario . '/' . $fileExcelNamefolder;
        // CAPETA ORIGEN DE PDFS
        if (!is_dir($rutaCarpetaOrigen)) {
            return response()->json(['error' => 'Error: No existe la carpeta de origen.']);
        }
        $rutaArchivoExcel = $this->rutaCarpetaUsuario . '/' . $archivoExcel;

        $contenido_pdf = scandir($rutaCarpetaOrigen);                                                      // Escanear contenido de la carpeta
        $archivosPdf = $this->esPDFValido($contenido_pdf, $rutaCarpetaOrigen);                             // valida que los PDF esten registrados en csv, xlsx, xls
        $extension = strtolower(pathinfo($archivoExcel, PATHINFO_EXTENSION));                              //Obtener extension xsls/csv
        $resultado = $this->processFile($rutaArchivoExcel, $archivosPdf);
        if (isset($resultado['error'])) {
            return response()->json(['error' => $resultado['error']]);
        }
        $id_plantilla = $resultado['id_plantilla'];                                                        //ID PLANTILLA
        $destino = $this->baseDir . "/pdfs/" . $folder . "/" . $this->username;                            //CARPETA DESTINO PDFS



        if (!empty($resultado['pdfNoEncontrados'])) {
            return response()->json(['error' => 'Error: Los siguientes PDFs no están en el CSV: ' . implode(", ", $resultado['pdfNoEncontrados'])]);
        }

        if (!empty($resultado['pdfsFaltantes'])) {
            return response()->json(['error' => 'Error: Faltan los siguientes PDFs: ' . implode(", ", $resultado['pdfsFaltantes'])]);
        }


        try {
            DB::beginTransaction();

            $ultimo = DB::table('notificaciones_avisos')->max('publi_notificacion');
            $publi_notificacion = $ultimo ? $ultimo + 1 : 1;
            $id_plantilla = $resultado['id_plantilla'];
            $pdfsAsociados = $resultado['pdfsAsociados'];
            $headers      = $resultado['headers'];
            $rows         = $resultado['rows'];

            $data = array_map(function ($row) use ($headers) {
                return array_combine($headers, $row);
            }, $rows);

            $errores = [];

            $data = $this->convertirFechasEnArray($data);

            foreach ($data as $index => $row) {
                Log::debug("Fila $index: " . json_encode($row));

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

                    $erroresFecha = $this->conversionDateExcelMonth($row['fecha_publicacion'], $row['fecha_desfijacion'], 1);
                    if (!empty($erroresFecha)) {
                        foreach ($erroresFecha as $mensaje) {
                            if (!isset($errores[$mensaje])) {
                                $errores[$mensaje] = 0;
                            }
                            $errores[$mensaje]++;
                        }
                    }
                } else {
                    $rules = array_merge($rules, [
                        'liquidacion' => ['nullable', 'regex:/^[a-zA-Z0-9]+$/'],
                        'objeto_contrato' => ['required', 'regex:/^[0-9_]+$/'],
                        'id_predio' => ['required', 'regex:/^[0-9]+$/'],
                    ]);

                    $erroresFecha = $this->conversionDateExcelDay($row['fecha_publicacion'], $row['fecha_desfijacion'], 5);
                    if (!empty($erroresFecha)) {
                        foreach ($erroresFecha as $mensaje) {
                            if (!isset($errores[$mensaje])) {
                                $errores[$mensaje] = 0;
                            }
                            $errores[$mensaje]++;
                        }
                    }
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
                return response()->json(['title' => 'Errores encontrados', 'errors' => $errores], 422);  // 422 es un código HTTP para "Unprocessable Entity" (Entidad no procesable)
            }
            EventoAuditoria::create([
                'id_publi_noti' => $publi_notificacion,
                'idusuario' => auth()->id(),
                'id_plantilla' => $id_plantilla,
                'cont_registros' => 0,
                'estado_auditoria' => 'E',
                'datos_adicionales' => json_encode([
                    'organismo_id' => $organismo->id,
                    'archivo' => $archivoExcel,
                    'archivo_cargado' => true,
                    'tipo_plantilla' => $id_plantilla,
                    'pdfsAsociados' => $pdfsAsociados
                ]),
                'fecha_auditoria' => now(),
            ]);

            ImportarNotificaciones::dispatch(
                $data,
                $publi_notificacion,
                $id_plantilla,
                $organismo->id,
                1,                      //estadoAuditoriaId
                $rutaArchivoExcel,
                auth()->id(), //auth()->user()->username,$extension
                $fileExcelNamefolder,
                $rutaCarpetaOrigen,
                $destino,
                $archivoExcel,
                $this->rutaCarpetaUsuario
            );

            DB::commit();
            return response()->json(['success' => 'Archivo en proceso de importación.', 'info' => $id_plantilla]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al iniciar la importación: ' . $e->getMessage()], 500);
        }
    }

    public function processFile($rutaArchivoExcel, $archivosPdf)
    {
        $sheet_file = Excel::toArray([], $rutaArchivoExcel)[0] ?? [];

        if (empty($sheet_file) || !isset($sheet_file[0][1])) {
            return ['error' => '❌ El archivo está vacío o mal formado.'];
        }

        $registros = $sheet_file[1][1];
        $pdfsAsociados = $sheet_file[2][1];
        log::debug("registros: " . json_encode($registros) . " pdfsAsociados:" . json_encode($pdfsAsociados));
        if ($registros != $pdfsAsociados) {
            return ['error' => '❌ El número de registros no coincide con la cantidad de archivos pdf.'];
        }



        log::debug("sheet_file: " . json_encode($sheet_file[0][1]));
        $id_plantilla = substr($sheet_file[0][1], 0, 1);
        $columnaNombre = in_array($id_plantilla, [1, 2]) ? 'NO_ACT_TRA' : 'objeto_contrato';

        // Log::debug("file: $rutaArchivoExcel");
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

        $this->extraerCodigoDesdeColumna($rows, $headers, 'tipo_acto_tramite');
        $this->extraerCodigoDesdeColumna($rows, $headers, 'tipo_impuesto');


        $archivosPdf = array_map(function ($archivo) {
            return preg_replace('/(\.pdf)+$/i', '', $archivo);
        }, $archivosPdf);

        $pdfsFaltantes = array_filter($nombresValidos, fn($nombre) => !in_array($nombre, $archivosPdf));
        $pdfNoEncontrados = array_filter($archivosPdf, fn($pdf) => !in_array($pdf, $nombresValidos));

        return [
            'id_plantilla' => $id_plantilla,
            'pdfsFaltantes' => $pdfsFaltantes,
            'pdfNoEncontrados' => $pdfNoEncontrados,
            'pdfsAsociados' => $pdfsAsociados,
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    function files_plantilla()  // Obtener archivos de plantilla
    {
        $files = Storage::disk('public')->files("users/{$this->username}");

        $excelRawFiles = collect($files)
            ->filter(fn($file) => in_array(pathinfo($file, PATHINFO_EXTENSION), ['xlsx', 'xls', 'csv']))
            ->map(fn($file) => basename($file))
            ->toArray();

        $rutaCarpetaUsuario = $this->baseDir . '/users/' . $this->username;
        $excelFiles = [];
        $archivosBloqueados = [];

        foreach ($excelRawFiles as $fileExcel) {
            $rutaArchivoExcel = $rutaCarpetaUsuario . '/' . $fileExcel;
            $abierto = $this->estaBloqueado($rutaArchivoExcel); // chequeo por archivo
            if ($abierto) {
                Log::warning("El archivo {$fileExcel} está abierto o en uso.");
                $archivosBloqueados[] = $fileExcel;
                break;
            } else {

                if (!file_exists($rutaArchivoExcel)) continue;

                $dataRaw = Excel::toArray([], $rutaArchivoExcel)[0] ?? [];

                if (isset($dataRaw[0][1])) {
                    $id_plantilla = substr($dataRaw[0][1], 0, 1);
                    $plantilla = TipoPlantilla::find($id_plantilla);
                    $n_registros = $dataRaw[1][1];
                    $n_pdfs = $dataRaw[2][1];

                    $excelFiles[] = [
                        'file' => $fileExcel,
                        'n_registros' => $n_registros,
                        'n_pdfs' => $n_pdfs,
                        'id_plantilla' => $id_plantilla,
                        'plantilla' => $plantilla->nombre_plantilla
                    ];
                }
            }
        }

        // Si hay archivos bloqueados, devolver la advertencia
        if (!empty($archivosBloqueados)) {
            return ['abierto' => true, 'archivosBloqueados' => $archivosBloqueados];
        }

        return $excelFiles;
    }

    private function convertirFechasEnArray(array $data): array
    {
        return array_map(function ($row) {
            foreach (['fecha_publicacion', 'fecha_desfijacion'] as $campo) {
                if (isset($row[$campo]) && is_numeric($row[$campo])) {
                    try {
                        $row[$campo] = \Carbon\Carbon::instance(Date::excelToDateTimeObject($row[$campo]))->format('n/j/Y');
                    } catch (\Exception $e) {
                        $row[$campo] = null;
                    }
                }
            }
            return $row;
        }, $data);
    }


    private function conversionDateExcelDay($fechaPublicacion, $fechaDesfijacion, $diasEsperados = 5)
    {
        try {
            $fechaPublicacion = $this->parseFechaExcel($fechaPublicacion);
            $fechaDesfijacion = $this->parseFechaExcel($fechaDesfijacion);

            if ($fechaDesfijacion->diffInDays($fechaPublicacion) !== $diasEsperados) {
                Log::warning("La diferencia entre {$fechaPublicacion->toDateString()} y {$fechaDesfijacion->toDateString()} no es de {$diasEsperados} días.");
                return ["La desfijacion entre fechas no es de {$diasEsperados} días."];
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
            // Log::info("Fecha de publicación: {$fechaPublicacion->toDateString()} Fecha de desfijación: {$fechaDesfijacion->toDateString()} Fecha esperada de desfijación: {$fechaEsperada->toDateString()}");

            if ($fechaDesfijacion->lt($fechaPublicacion) || $fechaDesfijacion->gt($fechaEsperada)) {
                Log::warning("La fecha de desfijación debería estar entre {$fechaPublicacion->toDateString()} y {$fechaEsperada->toDateString()}, pero se recibió {$fechaDesfijacion->toDateString()}.");
                return ["La fecha de desfijación debería estar entre {$fechaPublicacion->toDateString()} y {$fechaEsperada->toDateString()}, pero se recibió {$fechaDesfijacion->toDateString()}."];
            }

            return [];
            // return ['fecha_publicacion' => $fechaPublicacion->toDateString(),'fecha_desfijacion' => $fechaDesfijacion->toDateString(),];
        } catch (\Exception $e) {
            Log::error("Error al convertir fechas: " . $e->getMessage());
            return ["Error al convertir fechas: " . $e->getMessage()];
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

    function procesando()
    {
        $organismo = $this->organismo;
        $excelFiles = $this->files_plantilla();

        foreach ($excelFiles as &$archivo) {
            $evento = DB::table('evento_auditoria')
                ->whereJsonContains('datos_adicionales->archivo', $archivo['file'])
                ->orderByDesc('created_at')
                ->first();

            if ($evento) {
                $datos = json_decode($evento->datos_adicionales ?? '{}', true);

                // ✅ Tomamos el progreso directamente desde datos_adicionales
                $porcentaje = isset($datos['progreso']) ? (int) $datos['progreso'] : 0;

                $archivo['procesados'] = $porcentaje; // este campo es opcional
                $archivo['porcentaje'] = min($porcentaje, 100);
                $archivo['estado_codigo'] = $evento->estado_auditoria;
                $archivo['estado'] = match ($evento->estado_auditoria) {
                    'P' => 'Publicado',
                    'F' => 'Fallido',
                    default => 'En proceso',
                };
                $archivo['observaciones'] = $datos['observaciones'] ?? '';
                $archivo['fecha'] = $evento->fecha_auditoria;
            } else {
                $archivo['estado_codigo'] = null;
            }
        }

        // ❗ Filtrar solo archivos en estado 'E' o 'P'
        $excelFiles = array_filter($excelFiles, function ($archivo) {
            return in_array($archivo['estado_codigo'], ['E', 'P']);
        });

        Log::debug("archivos: " . json_encode($excelFiles));
        $excelCount = count($excelFiles);

        return view('admin.import.procesando', compact('organismo', 'excelFiles', 'excelCount'));
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

    public function estaBloqueado($rutaArchivo)
    {
        $handle = @fopen($rutaArchivo, 'r+');

        if ($handle === false) {
            return true; // No se puede abrir: probablemente está bloqueado o en uso
        }

        fclose($handle);
        return false; // El archivo está libre
    }
    private function extraerCodigoDesdeColumna(array &$rows, array $headers, string $nombreColumna)
    {
        $indice = array_search(strtolower($nombreColumna), array_map('strtolower', $headers));

        if ($indice !== false) {
            foreach ($rows as &$fila) {
                if (isset($fila[$indice])) {
                    $valorOriginal = trim($fila[$indice]);
                    $codigo = strpos($valorOriginal, '-') !== false
                        ? explode('-', $valorOriginal)[0]
                        : $valorOriginal;

                    $fila[$indice] = $codigo;
                }
            }
            unset($fila); // buena práctica
        }
    }
    public function show()
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
}
