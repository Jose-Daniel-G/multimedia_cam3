<?php

namespace App\Http\Controllers;

use App\Models\NotificacionAviso;
use App\Imports\NotificacionAvisoImport; // Asegúrate de que esta clase exista
use App\Jobs\ImportarNotificaciones; // Asegúrate de que esta clase exista
use App\Models\EventoAuditoria;
use App\Models\Organismo;
use App\Models\TipoPlantilla;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel; // Asegúrate de tener Maatwebsite/Laravel-Excel instalado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader; // Si es necesario, asegúrate de tenerlo instalado
use PhpOffice\PhpSpreadsheet\IOFactory; // Asegúrate de tener PhpSpreadsheet instalado
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response; // Importar Response para los códigos HTTP

class NotificacionAvisoController extends Controller
{
    protected $usuario;
    protected $organismo;
    protected $username;
    protected $baseDir;
    protected $rutaCarpetaUsuario;

    public function __construct()
    {
        // El middleware para inicializar las propiedades del usuario
        // debe ser 'api' o 'sanctum' si se usa en rutas de API
        $this->middleware(function ($request, $next) {
            // Asegúrate de que Auth::user() retorne un usuario autenticado
            // para que estas propiedades se inicialicen correctamente.
            // Las rutas que usan este controlador deben estar protegidas por 'auth:sanctum'
            // o el guard API que uses.
            $this->usuario = Auth::user();

            if (!$this->usuario) {
                // Si el usuario no está autenticado, no se pueden inicializar las propiedades
                // y se devolverá un error 401 si no está protegido por middleware.
                // Si la ruta está protegida por 'auth:sanctum', Laravel ya manejaría el 401.
                return response()->json(['message' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
            }

            $this->organismo = Organismo::find($this->usuario->organismo_id);
            $this->username = strtok($this->usuario->email, '@');
            $this->baseDir = storage_path('app/public');
            $this->rutaCarpetaUsuario = $this->baseDir . '/users/' . $this->username;

            return $next($request);
        });
    }

    /**
     * Obtiene una lista de archivos Excel procesados y publicados por el usuario.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Traer solo eventos publicados por el usuario autenticado
        $eventos = DB::table('evento_auditoria')
            ->where('idusuario', $this->usuario->id) // Usar $this->usuario->id inicializado
            ->where('estado_auditoria', 'P')
            ->orderByDesc('created_at')
            ->get();

        $excelFiles = [];

        foreach ($eventos as $evento) {
            $datos = json_decode($evento->datos_adicionales ?? '{}', true);
            if (is_string($datos)) {
                $datos = json_decode($datos, true); // Manejar doble codificación si ocurre
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
                'n_pdfs' => $datos['pdfsAsociados'] ?? '-',
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

        return response()->json([
            'message' => 'Archivos Excel publicados obtenidos exitosamente',
            'data' => $excelFiles,
            'count' => $excelCount
        ], Response::HTTP_OK);
    }

    /**
     * Prepara datos para la creación de un nuevo proceso de importación.
     * Muestra los archivos disponibles para importar, excluyendo los bloqueados o en proceso.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $excelFiles = $this->files_plantilla();

        // Verificar si existen archivos bloqueados
        if (isset($excelFiles['abierto']) && $excelFiles['abierto']) {
            $archivosBloqueados = implode(', ', $excelFiles['archivosBloqueados']);
            return response()->json([
                'error' => "Los siguientes archivos están abiertos: {$archivosBloqueados}. Por favor, ciérralos para continuar."
            ], Response::HTTP_LOCKED); // 423 Locked es un buen código para recursos bloqueados
        }

        // Filtrar archivos que aún no están siendo procesados
        $archivosFiltrados = array_filter($excelFiles, function ($archivo) {
            $fileName = $archivo['file'];

            return !DB::table('evento_auditoria')
                ->whereJsonContains('datos_adicionales->archivo', $fileName)
                ->exists();
        });
        $excelFiles = $archivosFiltrados;
        $excelCount = count($archivosFiltrados);

        return response()->json([
            'message' => 'Archivos Excel disponibles para importación obtenidos exitosamente',
            'data' => $excelFiles,
            'count' => $excelCount
        ], Response::HTTP_OK);
    }

    /**
     * Almacena un nuevo archivo Excel para procesar y despacha un job de importación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // En una API, si se espera una subida de archivo real, usarías $request->file('file').
        // Sin embargo, tu código actual usa $request->input('file'), lo que implica que
        // el nombre del archivo se envía en el cuerpo de la petición.
        // Mantenemos la lógica actual, asumiendo que el archivo ya existe en el servidor.
        $archivoExcel = $request->input('file');

        if (empty($archivoExcel)) {
            return response()->json(['error' => 'El nombre del archivo Excel es requerido.'], Response::HTTP_BAD_REQUEST);
        }

        $fileExcelNamefolder = pathinfo($archivoExcel, PATHINFO_FILENAME);
        $rutaCarpetaOrigen = $this->rutaCarpetaUsuario . '/' . $fileExcelNamefolder;
        $rutaArchivoExcel = $this->rutaCarpetaUsuario . '/' . $archivoExcel;

        // Validaciones de existencia de archivos y carpetas
        $contenido = scandir($this->rutaCarpetaUsuario);
        $validacionArchivos = $this->esArchivoValido($contenido, $this->rutaCarpetaUsuario);
        if (empty($validacionArchivos)) {
            return response()->json(['error' => 'Error: No se encontró ningún archivo CSV, XLSX o XLS en la carpeta del usuario.'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_dir($rutaCarpetaOrigen)) {
            return response()->json(['error' => 'Error: No existe la carpeta de origen para los PDFs. Asegúrate de que el archivo Excel tenga una carpeta con el mismo nombre.'], Response::HTTP_BAD_REQUEST);
        }

        if (!file_exists($rutaArchivoExcel)) {
            return response()->json(['error' => 'Error: El archivo Excel especificado no existe en la carpeta del usuario.'], Response::HTTP_BAD_REQUEST);
        }


        $contenido_pdf = scandir($rutaCarpetaOrigen);
        $archivosPdf = $this->esPDFValido($contenido_pdf, $rutaCarpetaOrigen);

        $resultado = $this->processFile($rutaArchivoExcel, $archivosPdf);

        if (isset($resultado['error'])) {
            return response()->json(['error' => $resultado['error']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Validaciones de PDFs (si apply)
        if (!empty($resultado['pdfNoEncontrados'])) {
            return response()->json(['error' => 'Error: Los siguientes PDFs no están referenciados en el archivo Excel: ' . implode(", ", $resultado['pdfNoEncontrados'])], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!empty($resultado['pdfsFaltantes'])) {
            return response()->json(['error' => 'Error: Faltan los siguientes archivos PDF en la carpeta de origen: ' . implode(", ", $resultado['pdfsFaltantes'])], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $ultimo = DB::table('notificaciones_avisos')->max('publi_notificacion');
            $publi_notificacion = $ultimo ? $ultimo + 1 : 1;
            $id_plantilla = $resultado['id_plantilla'];
            $pdfsAsociados = $resultado['pdfsAsociados'];
            $headers = $resultado['headers'];
            $rows = $resultado['rows'];

            $data = array_map(function ($row) use ($headers) {
                // Asegurarse de que el número de elementos en $row coincide con $headers
                // antes de array_combine para evitar errores en filas mal formadas.
                if (count($row) !== count($headers)) {
                    Log::warning("Fila con número de columnas inconsistente. Esperado: " . count($headers) . ", Obtenido: " . count($row));
                    // Podrías lanzar una excepción o manejar este caso de otra manera
                    return null; // o un array vacío para que sea filtrado después
                }
                return array_combine($headers, $row);
            }, $rows);

            // Filtrar filas nulas si se produjo un error en array_combine
            $data = array_filter($data);

            $erroresValidacion = [];

            $data = $this->convertirFechasEnArray($data);

            foreach ($data as $index => $row) {
                // Log::debug("Fila $index: " . json_encode($row)); // Descomentar para depuración detallada

                $rules = [
                    'nombre_ciudadano' => ['required', 'string', 'max:255'],
                    'cedula_identificacion' => ['required', 'regex:/^[0-9]+$/'],
                    'fecha_publicacion' => ['required'],
                    'fecha_desfijacion' => ['required'],
                ];

                if (in_array($id_plantilla, [1, 2])) {
                    $row['tipo_impuesto'] = trim($row['tipo_impuesto'] ?? ''); // Asegurar que exista y sea string para trim

                    $rules = array_merge($rules, [
                        'tipo_impuesto' => ['required', 'regex:/^[0-9]+$/', 'integer'],
                        'tipo_acto_tramite' => ['required', 'regex:/^[0-9]+$/', 'integer'],
                        'tipo_causa_devolucion' => ['required', 'regex:/^[0-9]+$/', 'integer'],
                        'tipo_estado_publicacion' => ['required', 'regex:/^[0-9]+$/', 'integer'],
                    ]);

                    $erroresFecha = $this->conversionDateExcelMonth($row['fecha_publicacion'] ?? null, $row['fecha_desfijacion'] ?? null, 1);
                    if (!empty($erroresFecha)) {
                        foreach ($erroresFecha as $mensaje) {
                            $erroresValidacion[$mensaje] = ($erroresValidacion[$mensaje] ?? 0) + 1;
                        }
                    }
                } else {
                    $rules = array_merge($rules, [
                        'liquidacion' => ['nullable', 'regex:/^[a-zA-Z0-9]+$/'],
                        'objeto_contrato' => ['required', 'regex:/^[0-9_]+$/'],
                        'id_predio' => ['required', 'regex:/^[0-9]+$/'],
                    ]);

                    $erroresFecha = $this->conversionDateExcelDay($row['fecha_publicacion'] ?? null, $row['fecha_desfijacion'] ?? null, 5);
                    if (!empty($erroresFecha)) {
                        foreach ($erroresFecha as $mensaje) {
                            $erroresValidacion[$mensaje] = ($erroresValidacion[$mensaje] ?? 0) + 1;
                        }
                    }
                }

                $validator = Validator::make($row, $rules);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $mensaje) {
                        $erroresValidacion[$mensaje] = ($erroresValidacion[$mensaje] ?? 0) + 1;
                    }
                }
            }

            if (!empty($erroresValidacion)) {
                return response()->json(['title' => 'Errores de validación encontrados en el archivo', 'errors' => $erroresValidacion], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            EventoAuditoria::create([
                'id_publi_noti' => $publi_notificacion,
                'idusuario' => $this->usuario->id, // Usar el ID del usuario inicializado
                'id_plantilla' => $id_plantilla,
                'cont_registros' => 0, // Se actualizará en el Job
                'estado_auditoria' => 'E', // 'E' para "En proceso"
                'datos_adicionales' => json_encode([
                    'organismo_id' => $this->organismo->id,
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
                $this->organismo->id,
                1, // estadoAuditoriaId (corresponde a 'E' en tu lógica)
                $rutaArchivoExcel,
                $this->usuario->id, // Pasa el ID del usuario directamente al Job
                $fileExcelNamefolder,
                $rutaCarpetaOrigen,
                $this->baseDir . "/pdfs/" . Str::snake($this->organismo->depe_nomb) . "/" . $this->username, // Destino PDFs
                $archivoExcel,
                $this->rutaCarpetaUsuario
            );

            DB::commit();
            return response()->json([
                'success' => 'Archivo en proceso de importación. Puedes verificar el estado en la sección de "Procesando".',
                'info' => $id_plantilla
            ], Response::HTTP_ACCEPTED); // 202 Accepted indica que la solicitud fue aceptada para procesamiento
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al iniciar la importación en NotificacionAvisoController@store: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error interno del servidor al iniciar la importación: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Procesa el archivo Excel/CSV, extrae headers y filas, y realiza validaciones iniciales.
     *
     * @param string $rutaArchivoExcel La ruta completa al archivo Excel/CSV.
     * @param array $archivosPdf Un array de nombres de archivos PDF válidos encontrados.
     * @return array Con los datos procesados o un error.
     */
    public function processFile($rutaArchivoExcel, $archivosPdf)
    {
        try {
            $sheet_file = Excel::toArray([], $rutaArchivoExcel)[0] ?? [];

            if (empty($sheet_file) || !isset($sheet_file[0][1])) {
                return ['error' => 'El archivo Excel/CSV está vacío o mal formado. No se pudo leer la información inicial.'];
            }

            $registros = $sheet_file[1][1] ?? null;
            $pdfsAsociados = $sheet_file[2][1] ?? null;

            if (is_null($registros) || is_null($pdfsAsociados)) {
                return ['error' => 'No se pudo obtener el número de registros o PDFs asociados del archivo.'];
            }

            Log::debug("Registros: " . $registros . ", PDFs Asociados: " . $pdfsAsociados);

            if ($registros != $pdfsAsociados) {
                return ['error' => 'El número de registros en el archivo no coincide con la cantidad de PDFs asociados indicada.'];
            }

            $id_plantilla = substr($sheet_file[0][1] ?? '', 0, 1);
            if (!in_array($id_plantilla, ['1', '2', '3', '4', '5'])) { // Asume plantillas válidas
                return ['error' => 'ID de plantilla inválido o no encontrado en el archivo.'];
            }

            $columnaNombre = in_array($id_plantilla, ['1', '2']) ? 'NO_ACT_TRA' : 'objeto_contrato';

            Log::debug("Plantilla detectada: " . $id_plantilla);

            if (!isset($sheet_file[3])) {
                return ['error' => 'No se encontró la fila de encabezados (fila 4) en la hoja del archivo.'];
            }

            $headers = array_map('trim', $sheet_file[3]);
            $rows = array_filter(array_slice($sheet_file, 4), fn($fila) => is_array($fila) && count(array_filter($fila)) > 0);

            // Validar si los headers son suficientes o si faltan columnas clave.
            if (empty($headers)) {
                return ['error' => 'Los encabezados del archivo están vacíos.'];
            }
            // Puedes añadir más validaciones de headers aquí, ej:
            // if (!in_array('nombre_ciudadano', $headers)) { ... }


            $indiceColumna = array_search($columnaNombre, $headers);
            if ($indiceColumna === false) {
                return ['error' => "Columna clave '{$columnaNombre}' no encontrada en los encabezados del archivo."];
            }


            $nombresValidos = array_values(array_filter(
                array_map(function ($fila) use ($indiceColumna) {
                    return isset($fila[$indiceColumna]) ? strtolower(trim($fila[$indiceColumna])) : '';
                }, $rows),
                fn($h) => $h !== ''
            ));

            $this->extraerCodigoDesdeColumna($rows, $headers, 'tipo_acto_tramite');
            $this->extraerCodigoDesdeColumna($rows, $headers, 'tipo_impuesto');

            $archivosPdfBase = array_map(function ($archivo) {
                return preg_replace('/(\.pdf)+$/i', '', $archivo); // Remueve la extensión .pdf
            }, $archivosPdf);

            $pdfsFaltantes = array_filter($nombresValidos, fn($nombre) => !in_array($nombre, $archivosPdfBase));
            $pdfNoEncontrados = array_filter($archivosPdfBase, fn($pdf) => !in_array($pdf, $nombresValidos));


            return [
                'id_plantilla' => $id_plantilla,
                'pdfsFaltantes' => array_values($pdfsFaltantes), // Asegura que sean arrays indexados
                'pdfNoEncontrados' => array_values($pdfNoEncontrados), // Asegura que sean arrays indexados
                'pdfsAsociados' => $pdfsAsociados, // Cantidad de PDFs asociados del Excel
                'headers' => $headers,
                'rows' => $rows,
            ];
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error("Error de PhpSpreadsheet al procesar archivo: " . $e->getMessage());
            return ['error' => 'Error al leer el archivo Excel/CSV. Asegúrate de que sea un formato válido y no esté corrupto: ' . $e->getMessage()];
        } catch (Exception $e) {
            Log::error("Error inesperado en processFile: " . $e->getMessage());
            return ['error' => 'Ocurrió un error inesperado al procesar el archivo: ' . $e->getMessage()];
        }
    }


    /**
     * Obtiene y prepara una lista de archivos Excel/CSV de la carpeta del usuario.
     *
     * @return array
     */
    function files_plantilla()
    {
        // Asegúrate de que el disco 'public' esté configurado correctamente en config/filesystems.php
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
                // Si encuentras un archivo bloqueado, puedes decidir si quieres seguir buscando
                // o si devuelves la advertencia inmediatamente. Aquí se detiene y devuelve.
                return ['abierto' => true, 'archivosBloqueados' => $archivosBloqueados];
            } else {
                if (!file_exists($rutaArchivoExcel)) continue;

                try {
                    $dataRaw = Excel::toArray([], $rutaArchivoExcel)[0] ?? [];

                    if (isset($dataRaw[0][1])) {
                        $id_plantilla = substr($dataRaw[0][1], 0, 1);
                        $plantilla = TipoPlantilla::find($id_plantilla);
                        $n_registros = $dataRaw[1][1] ?? null;
                        $n_pdfs = $dataRaw[2][1] ?? null;

                        $excelFiles[] = [
                            'file' => $fileExcel,
                            'n_registros' => $n_registros,
                            'n_pdfs' => $n_pdfs,
                            'id_plantilla' => $id_plantilla,
                            'plantilla' => optional($plantilla)->nombre_plantilla // Usa optional para evitar errores si no se encuentra la plantilla
                        ];
                    }
                } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                    Log::error("Error al leer el archivo Excel/CSV '{$fileExcel}': " . $e->getMessage());
                    // Puedes optar por añadirlo a una lista de errores o simplemente ignorarlo
                } catch (Exception $e) {
                    Log::error("Error inesperado al procesar '{$fileExcel}': " . $e->getMessage());
                }
            }
        }

        return $excelFiles;
    }

    /**
     * Convierte valores numéricos de fecha de Excel a formato de fecha en un array de datos.
     *
     * @param array $data El array de datos a procesar.
     * @return array
     */
    private function convertirFechasEnArray(array $data): array
    {
        return array_map(function ($row) {
            foreach (['fecha_publicacion', 'fecha_desfijacion'] as $campo) {
                if (isset($row[$campo])) {
                    $valor = $row[$campo];
                    if (is_numeric($valor)) {
                        try {
                            $row[$campo] = Carbon::instance(Date::excelToDateTimeObject($valor))->format('Y-m-d'); // Formato ISO 8601
                        } catch (\Exception $e) {
                            Log::error("Error al convertir fecha Excel numérica '{$valor}': " . $e->getMessage());
                            $row[$campo] = null; // O dejar el valor original si se prefiere
                        }
                    } else {
                        // Si no es numérico, intentar parsear como string directamente (ya debería estar en formato legible)
                        try {
                            $row[$campo] = Carbon::parse($valor)->format('Y-m-d');
                        } catch (\Exception $e) {
                            Log::error("Error al parsear fecha string '{$valor}': " . $e->getMessage());
                            $row[$campo] = null;
                        }
                    }
                }
            }
            return $row;
        }, $data);
    }

    /**
     * Valida la diferencia de días entre dos fechas (para plantilla tipo "día").
     *
     * @param mixed $fechaPublicacion La fecha de publicación (valor de Excel o string).
     * @param mixed $fechaDesfijacion La fecha de desfijación (valor de Excel o string).
     * @param int $diasEsperados La cantidad de días esperados de diferencia.
     * @return array Errores encontrados o un array vacío si no hay errores.
     */
    private function conversionDateExcelDay($fechaPublicacion, $fechaDesfijacion, $diasEsperados = 5)
    {
        try {
            $fechaPublicacion = $this->parseFechaExcel($fechaPublicacion);
            $fechaDesfijacion = $this->parseFechaExcel($fechaDesfijacion);

            if (!$fechaPublicacion || !$fechaDesfijacion) {
                return ["Error de formato en una de las fechas (Publicación/Desfijación)."];
            }

            if ($fechaDesfijacion->diffInDays($fechaPublicacion) !== $diasEsperados) {
                Log::warning("La diferencia entre {$fechaPublicacion->toDateString()} y {$fechaDesfijacion->toDateString()} no es de {$diasEsperados} días.");
                return ["La desfijación entre fechas no es de {$diasEsperados} días."];
            }

            return []; // Sin errores
        } catch (\Exception $e) {
            Log::error("Error al convertir fechas en conversionDateExcelDay: " . $e->getMessage());
            return ["Error interno al procesar fechas de tipo día: " . $e->getMessage()];
        }
    }

    /**
     * Valida la diferencia de meses entre dos fechas (para plantilla tipo "mes").
     *
     * @param mixed $fecha_publicacion La fecha de publicación (valor de Excel o string).
     * @param mixed $fecha_desfijacion La fecha de desfijación (valor de Excel o string).
     * @param int $mesesEsperados La cantidad de meses esperados de diferencia.
     * @return array Errores encontrados o un array vacío si no hay errores.
     */
    private function conversionDateExcelMonth($fecha_publicacion, $fecha_desfijacion, $mesesEsperados = 1)
    {
        try {
            $fechaPublicacion = $this->parseFechaExcel($fecha_publicacion);
            $fechaDesfijacion = $this->parseFechaExcel($fecha_desfijacion);

            if (!$fechaPublicacion || !$fechaDesfijacion) {
                return ["Error de formato en una de las fechas (Publicación/Desfijación)."];
            }

            // Calcular la fecha esperada sumando el número de meses
            $fechaEsperada = $fechaPublicacion->copy()->addMonths($mesesEsperados);

            if ($fechaDesfijacion->lt($fechaPublicacion) || $fechaDesfijacion->gt($fechaEsperada)) {
                Log::warning("La fecha de desfijación debería estar entre {$fechaPublicacion->toDateString()} y {$fechaEsperada->toDateString()}, pero se recibió {$fechaDesfijacion->toDateString()}.");
                return ["La fecha de desfijación debería estar entre {$fechaPublicacion->toDateString()} y {$fechaEsperada->toDateString()}, pero se recibió {$fechaDesfijacion->toDateString()}."];
            }

            return [];
        } catch (\Exception $e) {
            Log::error("Error al convertir fechas en conversionDateExcelMonth: " . $e->getMessage());
            return ["Error interno al procesar fechas de tipo mes: " . $e->getMessage()];
        }
    }

    /**
     * Parsea un valor de fecha, ya sea numérico (de Excel) o string, a un objeto Carbon.
     *
     * @param mixed $fecha El valor de la fecha.
     * @return \Carbon\Carbon|null
     */
    private function parseFechaExcel($fecha)
    {
        if (is_null($fecha)) {
            return null;
        }

        try {
            return is_numeric($fecha)
                ? Carbon::instance(Date::excelToDateTimeObject($fecha))
                : Carbon::parse($fecha);
        } catch (\Exception $e) {
            Log::warning("No se pudo parsear la fecha: '{$fecha}'. Error: " . $e->getMessage());
            return null; // Retorna null si no se puede parsear
        }
    }

    /**
     * Obtiene y devuelve el estado de los archivos Excel en proceso de importación.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function procesando()
    {
        $excelFiles = $this->files_plantilla();

        // Si files_plantilla devolvió un error de archivo bloqueado, lo manejamos aquí.
        if (isset($excelFiles['abierto']) && $excelFiles['abierto']) {
            $archivosBloqueados = implode(', ', $excelFiles['archivosBloqueados']);
            return response()->json([
                'error' => "Los siguientes archivos están abiertos o en uso: {$archivosBloqueados}."
            ], Response::HTTP_LOCKED);
        }

        foreach ($excelFiles as &$archivo) {
            $evento = DB::table('evento_auditoria')
                ->whereJsonContains('datos_adicionales->archivo', $archivo['file'])
                ->orderByDesc('created_at')
                ->first();

            if ($evento) {
                $datos = json_decode($evento->datos_adicionales ?? '{}', true);

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
                // Si no hay evento de auditoría para este archivo, lo marcamos como "Pendiente" o "No procesado"
                $archivo['estado_codigo'] = 'N'; // Nuevo estado para "No procesado"
                $archivo['estado'] = 'No Procesado';
                $archivo['porcentaje'] = 0;
                $archivo['procesados'] = 0;
                $archivo['observaciones'] = 'Aún no se ha iniciado el procesamiento';
                $archivo['fecha'] = null;
            }
        }

        // Filtrar solo archivos en estado 'E' (En proceso) o 'P' (Publicado)
        $excelFiles = array_filter($excelFiles, function ($archivo) {
            return in_array($archivo['estado_codigo'], ['E', 'P']);
        });

        $excelFiles = array_values($excelFiles); // Reindexar el array después de filtrar
        Log::debug("Archivos en proceso o publicados: " . json_encode($excelFiles));
        $excelCount = count($excelFiles);

        return response()->json([
            'message' => 'Estado de archivos en proceso/publicados obtenidos exitosamente',
            'data' => $excelFiles,
            'count' => $excelCount
        ], Response::HTTP_OK);
    }

    /**
     * Valida si los archivos de la carpeta del usuario son válidos para importar.
     *
     * @param array $contenido Contenido de la carpeta.
     * @param string $rutaCarpetaUsuario Ruta de la carpeta del usuario.
     * @return array Archivos válidos.
     */
    function esArchivoValido($contenido, $rutaCarpetaUsuario)
    {
        $archivosValidos = array_filter($contenido, function ($archivo) use ($rutaCarpetaUsuario) {
            $extensionesPermitidas = ['csv', 'xlsx', 'xls'];
            $extension = pathinfo($archivo, PATHINFO_EXTENSION);
            return is_file($rutaCarpetaUsuario . '/' . $archivo) && in_array(strtolower($extension), $extensionesPermitidas);
        });

        return array_values($archivosValidos); // Asegura array indexado
    }

    /**
     * Valida si los archivos PDF en la carpeta son válidos.
     *
     * @param array $contenido Contenido de la carpeta.
     * @param string $rutaCarpetaUsuario Ruta de la carpeta.
     * @return array Archivos PDF válidos (nombres en minúscula sin extensión .pdf).
     */
    function esPDFValido($contenido, $rutaCarpetaUsuario)
    {
        $archivosPdf = array_map(
            fn($pdf) => strtolower(trim($pdf)),
            array_filter($contenido, function ($archivo) use ($rutaCarpetaUsuario) {
                return is_file($rutaCarpetaUsuario . '/' . $archivo) && strtolower(pathinfo($archivo, PATHINFO_EXTENSION)) === 'pdf';
            })
        );
        return array_values($archivosPdf); // Asegura array indexado
    }

    /**
     * Verifica si un archivo está bloqueado (abierto por otro proceso).
     *
     * @param string $rutaArchivo La ruta completa al archivo.
     * @return bool True si está bloqueado, false si está libre.
     */
    public function estaBloqueado($rutaArchivo)
    {
        // Se intenta abrir el archivo en modo de lectura y escritura.
        // Si otro proceso lo tiene abierto de forma exclusiva, fopen fallará.
        $handle = @fopen($rutaArchivo, 'r+'); // @ para suprimir advertencias de PHP

        if ($handle === false) {
            return true; // No se puede abrir: probablemente está bloqueado o en uso
        }

        fclose($handle); // Cierra el archivo si se pudo abrir
        return false; // El archivo está libre
    }

    /**
     * Extrae un código de una columna basada en un guion y actualiza la fila.
     *
     * @param array $rows Referencia al array de filas.
     * @param array $headers Los encabezados de la tabla.
     * @param string $nombreColumna El nombre de la columna a procesar.
     */
    private function extraerCodigoDesdeColumna(array &$rows, array $headers, string $nombreColumna)
    {
        // Convierte los headers a minúsculas para una búsqueda insensible a mayúsculas/minúsculas
        $indice = array_search(strtolower($nombreColumna), array_map('strtolower', $headers));

        if ($indice !== false) {
            foreach ($rows as &$fila) {
                // Verifica que la clave exista antes de intentar acceder a ella
                if (isset($fila[$indice])) {
                    $valorOriginal = trim($fila[$indice]);
                    $codigo = strpos($valorOriginal, '-') !== false
                        ? explode('-', $valorOriginal)[0]
                        : $valorOriginal;

                    $fila[$indice] = $codigo;
                }
            }
            unset($fila); // Anular la referencia después del bucle
        }
    }

    /**
     * Muestra una notificación o aviso específico (Placeholder para API).
     *
     * @param int $id El ID de la notificación/aviso.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $notificacion = NotificacionAviso::find($id);

        if (!$notificacion) {
            return response()->json(['message' => 'Notificación/Aviso no encontrado'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $notificacion], Response::HTTP_OK);
    }

    /**
     * Actualiza una notificación o aviso específico (Placeholder para API).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NotificacionAviso  $notificacionAviso
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, NotificacionAviso $notificacionAviso) // Cambiado $file a $notificacionAviso para binding de modelo
    {
        // Implementar lógica de validación y actualización
        // $request->validate([...]);
        // $notificacionAviso->update($request->all());

        return response()->json(['message' => 'Funcionalidad de actualización no implementada aún.'], Response::HTTP_NOT_IMPLEMENTED);
    }

    /**
     * Elimina una notificación o aviso específico (Placeholder para API).
     *
     * @param  \App\Models\NotificacionAviso  $notificacionAviso
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(NotificacionAviso $notificacionAviso) // Cambiado $file a $notificacionAviso para binding de modelo
    {
        // Implementar lógica de eliminación
        // $notificacionAviso->delete();

        return response()->json(['message' => 'Funcionalidad de eliminación no implementada aún.'], Response::HTTP_NOT_IMPLEMENTED);
    }
}
