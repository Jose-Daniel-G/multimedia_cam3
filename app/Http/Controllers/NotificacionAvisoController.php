<?php

namespace App\Http\Controllers;

use App\Models\NotificacionAviso;
use App\Jobs\ImportarNotificaciones;
use App\Models\EventoAuditoria;
use App\Models\Organismo;
use App\Models\TipoPlantilla;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Exception;

class NotificacionAvisoController extends Controller
{
    protected $usuario;
    protected $organismo;
    protected $username;
    protected $baseDir;
    protected $rutaCarpetaUsuario;

    public function __construct()
    {
        // Middlewares de permisos
        $this->middleware('permission:notificacion.index')->only('index');
        $this->middleware('permission:notificacion.edit')->only('edit');
        $this->middleware('permission:notificacion.create')->only('create');
        $this->middleware('permission:notificacion.delete')->only('destroy');

        // Middleware para inicializar propiedades del usuario
        $this->middleware(function ($request, $next) {
            $this->usuario   = Auth::user();
            $this->organismo = Organismo::find($this->usuario->organismo_id);
            $this->username  = strtok($this->usuario->email, '@');
            $this->baseDir   = storage_path('app/public');
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
            ->orderByDesc('fecha_auditoria')
            ->get();

        $excelFiles = [];

        foreach ($eventos as $evento) {
            $datos = json_decode($evento->datos_adicionales ?? '{}', true);
            if (is_string($datos)) {
                $datos = json_decode($datos, true); // manejar doble codificación
            }

            $archivo = $datos['archivo'] ?? null;
            if (!$archivo) {continue;}

            $procesados = $evento->cont_registros;
            $total      = $procesados; // No se guarda n_registros en los datos, así que lo igualamos
            $porcentaje = 100;         // Como el estado es 'P', asumimos 100%

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
        $validacion = esArchivoValido($contenido, $this->rutaCarpetaUsuario);
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
        $archivosPdf = esPDFValido($contenido_pdf, $rutaCarpetaOrigen);                             // valida que los PDF esten registrados en csv, xlsx, xls
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
            Log::debug("ID Plantilla: " . $id_plantilla);
            $pdfsAsociados = $resultado['pdfsAsociados'];
            $headers      = $resultado['headers'];
            $rows         = $resultado['rows'];

            $data = array_map(function ($row) use ($headers) {
                return array_combine($headers, $row);
            }, $rows);

            $errores = [];

            $data = convertirFechasEnArray($data);

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

                    $erroresFecha = conversionDateExcelMonth($row['fecha_publicacion'], $row['fecha_desfijacion'], 1);
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

                    $erroresFecha = conversionDateExcelDay($row['fecha_publicacion'], $row['fecha_desfijacion'], 5);
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
                'datos_adicionales' => [
                    'organismo_id' => $organismo->id,
                    'archivo' => $archivoExcel,
                    'archivo_cargado' => true,
                    'tipo_plantilla' => $id_plantilla,
                    'pdfsAsociados' => $pdfsAsociados,
                ],
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
            // 🔍 Registro manual del error en la tabla activity_log
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'archivo' => $archivoExcel,
                    'organismo_id' => $organismo->id,
                    'mensaje' => $e->getMessage(),
                    'archivo_path' => $rutaArchivoExcel ?? null
                ])
                ->log('❌ Error durante la carga del archivo');
            return response()->json(['error' => 'Error al iniciar la importación: ' . $e->getMessage()], 500);
        }
    }

    public function processFile($rutaArchivoExcel, $archivosPdf)
    {
        $spreadsheet = IOFactory::load($rutaArchivoExcel);
        $sheet = $spreadsheet->getSheet(0); // Forzar la primera hoja (índice 0)
        $sheetData = $sheet->toArray();     // Obtener todos los datos de la hoja como array

        // Validación básica
        if (empty($sheetData) || !isset($sheetData[0][1])) {return ['error' => '❌ El archivo está vacío o mal formado.'];}
        // Validar que exista fila de encabezados (fila 4)
        if (!isset($sheetData[3])) {return ['error' => '❌ No se encontró la fila de headers (fila 4) en la hoja.'];}

        // Lectura de celdas específicas
        $id_plantilla_raw = $sheet->getCell('B1')->getValue();

        $id_plantilla = substr((string)$id_plantilla_raw, 0, 1);
        $headers      = array_map('trim', $sheetData[3]);
        $n_registros  = $sheet->getCell('B2')->getCalculatedValue();
        $n_pdfs       = $sheet->getCell('B3')->getCalculatedValue();

        Log::debug("registros: " . json_encode($n_registros) . " pdfsAsociados: " . json_encode($n_pdfs));

        if ($n_registros != $n_pdfs) {return ['error' => '❌ El número de registros no coincide con la cantidad de archivos pdf.'];}

        $columnaNombre = in_array($id_plantilla, [1, 2]) ? 'NO_ACT_TRA' : 'objeto_contrato';

        // Obtener datos desde la fila 5 en adelante (índice 4)
        $rows = array_filter(array_slice($sheetData, 4),
            fn($fila) => is_array($fila) && count(array_filter($fila)) > 0
        );

        $indiceColumna = array_search($columnaNombre, $headers);

        $nombresValidos = nombresValidos($rows, $indiceColumna);;

        extraerCodigoDesdeColumna($rows, $headers, 'tipo_acto_tramite');
        extraerCodigoDesdeColumna($rows, $headers, 'tipo_impuesto');

        // Limpiar nombres de archivos PDF (sin extensión)
        $archivosPdf = array_map(function ($archivo) {
            return preg_replace('/(\.pdf)+$/i', '', $archivo);
        }, $archivosPdf);

        $pdfsFaltantes = array_filter($nombresValidos, fn($nombre) => !in_array($nombre, $archivosPdf));
        $pdfNoEncontrados = array_filter($archivosPdf, fn($pdf) => !in_array($pdf, $nombresValidos));

        return [
            'id_plantilla' => $id_plantilla,
            'pdfsFaltantes' => $pdfsFaltantes,
            'pdfNoEncontrados' => $pdfNoEncontrados,
            'pdfsAsociados' => $n_pdfs,
            'headers' => $headers,
            'rows' => $rows,
        ];
    }


    public function files_plantilla()
    {
        $resultado = $this->files_plantilla_sin_cache();

        if (is_array($resultado) && !isset($resultado['abierto'])) {
            Cache::put("archivos_excel_{$this->username}", $resultado, 10);
        }

        return $resultado;
    }

    public function files_plantilla_sin_cache()
    {
        // Preparar ruta según el origen
        $files = Storage::disk('public')->files("users/{$this->username}");
        $rutaCarpetaUsuario = $this->baseDir . "/users/{$this->username}";

        $excelRawFiles = collect($files)
            ->filter(fn($file) => in_array(pathinfo($file, PATHINFO_EXTENSION), ['xlsx', 'xls', 'csv']))
            ->map(fn($file) => basename($file))
            ->toArray();

        $excelFiles = [];
        $archivosBloqueados = [];

        foreach ($excelRawFiles as $fileExcel) {
            $rutaArchivoExcel = $rutaCarpetaUsuario . '/' . $fileExcel;

            if (!file_exists($rutaArchivoExcel)) continue;

            if (estaBloqueado($rutaArchivoExcel)) {
                Log::warning("El archivo {$fileExcel} está abierto o en uso.");
                $archivosBloqueados[] = $fileExcel;
                break;
            }

            try {
                $spreadsheet = IOFactory::load($rutaArchivoExcel);
                $sheet = $spreadsheet->getSheet(0);

                $id_plantilla_raw = $sheet->getCell('B1')->getValue();
                $id_plantilla = substr((string)$id_plantilla_raw, 0, 1);

                $n_registros = $sheet->getCell('B2')->getCalculatedValue();
                $n_pdfs = $sheet->getCell('B3')->getCalculatedValue();

                $plantilla = TipoPlantilla::find($id_plantilla);

                $excelFiles[] = [
                    'file' => $fileExcel,
                    'n_registros' => is_numeric($n_registros) ? (int)$n_registros : 0,
                    'n_pdfs' => is_numeric($n_pdfs) ? (int)$n_pdfs : 0,
                    'id_plantilla' => $id_plantilla,
                    'plantilla' => $plantilla?->nombre_plantilla ?? 'Desconocida',
                ];
            } catch (\Exception $e) {
                // 🔍 Registro manual del error en la tabla activity_log
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'archivo' => $fileExcel,
                        'organismo_id' => $this->organismo->id,
                        'mensaje' => $e->getMessage(),
                        'archivo_path' => $rutaArchivoExcel ?? null
                    ])
                    ->log('❌ "Error procesando');
                Log::error("Error procesando $fileExcel: " . $e->getMessage());
            }
        }

        if (!empty($archivosBloqueados)) {
            return ['abierto' => true, 'archivosBloqueados' => $archivosBloqueados];
        }

        return $excelFiles;
    }

    public function procesandoView()
    {
        $organismo = $this->organismo;
        $excelFiles = obtenerProgresoCarga();
        $excelCount = count($excelFiles);

        return view('admin.import.procesando', compact('organismo', 'excelFiles', 'excelCount'));
    }

    public function jsonProgreso()
    {
        return response()->json(array_values(obtenerProgresoCarga()));
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
