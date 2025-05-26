<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/* -------------------------------------------------------------------------- */
/*                        Validación de Archivos                              */
/* -------------------------------------------------------------------------- */

if (!function_exists('nombresValidos')) {
    function nombresValidos($rows, $indiceColumna)
    {
        return array_values(array_filter(
            array_map(function ($fila) use ($indiceColumna) {
                return isset($fila[$indiceColumna]) ? strtolower(trim($fila[$indiceColumna])) : '';
            }, $rows),
            fn($h) => $h !== ''
        ));
    }
}
/* -------------------------------------------------------------------------- */
/*                        Validación de Archivos                              */
/* -------------------------------------------------------------------------- */

if (!function_exists('esArchivoValido')) {
    function esArchivoValido($contenido, $rutaCarpetaUsuario)
    {
        $extensionesPermitidas = ['csv', 'xlsx', 'xls'];
        return array_filter($contenido, function ($archivo) use ($rutaCarpetaUsuario, $extensionesPermitidas) {
            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            return is_file($rutaCarpetaUsuario . '/' . $archivo) && in_array($extension, $extensionesPermitidas);
        });
    }
}

/* -------------------------------------------------------------------------- */
/*                          Conversión de Fechas Excel                        */
/* -------------------------------------------------------------------------- */
if (!function_exists('parseFechaExcel')) {
    function parseFechaExcel($fecha)
    {
        try {
            return is_numeric($fecha)
                ? Carbon::instance(Date::excelToDateTimeObject($fecha))
                : Carbon::parse($fecha);
        } catch (\Exception $e) {
            return null;
        }
    }
}

/* -------------------------------------------------------------------------- */
/*                Conversión de fechas con validación por mes                */
/* -------------------------------------------------------------------------- */
if (!function_exists('conversionDateExcelMonth')) {
    function conversionDateExcelMonth($fecha_publicacion, $fecha_desfijacion, $mesesEsperados = 1)
    {
        try {
            $fechaPublicacion = parseFechaExcel($fecha_publicacion);
            $fechaDesfijacion = parseFechaExcel($fecha_desfijacion);

            if (!$fechaPublicacion || !$fechaDesfijacion) {
                return ["Error al interpretar fechas."];
            }

            $fechaEsperada = $fechaPublicacion->copy()->addMonths($mesesEsperados);

            if ($fechaDesfijacion->lt($fechaPublicacion) || $fechaDesfijacion->gt($fechaEsperada)) {
                Log::warning("Fecha de desfijación fuera del rango esperado: {$fechaDesfijacion->toDateString()}");
                return ["La fecha de desfijación debe estar entre {$fechaPublicacion->toDateString()} y {$fechaEsperada->toDateString()}."];
            }

            return [];
        } catch (\Exception $e) {
            Log::error("Error al validar fechas por mes: " . $e->getMessage());
            return ["Error al validar fechas: " . $e->getMessage()];
        }
    }
}

/* -------------------------------------------------------------------------- */
/*               Conversión de fechas con validación por días                */
/* -------------------------------------------------------------------------- */
if (!function_exists('conversionDateExcelDay')) {
    function conversionDateExcelDay($fechaPublicacion, $fechaDesfijacion, $diasEsperados = 5)
    {
        try {
            $fechaPublicacion = parseFechaExcel($fechaPublicacion);
            $fechaDesfijacion = parseFechaExcel($fechaDesfijacion);

            if (!$fechaPublicacion || !$fechaDesfijacion) {
                return ["Error al interpretar fechas."];
            }

            if ($fechaDesfijacion->diffInDays($fechaPublicacion) !== $diasEsperados) {
                return ["La desfijación entre fechas no es de {$diasEsperados} días."];
            }

            return [];
        } catch (\Exception $e) {
            Log::error("Error al validar fechas por día: " . $e->getMessage());
            return ["Error al procesar fechas: " . $e->getMessage()];
        }
    }
}

/* -------------------------------------------------------------------------- */
/*                      Normalización de Cadenas                             */
/* -------------------------------------------------------------------------- */
if (!function_exists('normalizar')) {
    function normalizar($cadena)
    {
        $cadena = mb_strtolower($cadena, 'UTF-8');
        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'n'];
        $cadena = str_replace($buscar, $reemplazar, $cadena);
        return preg_replace('/[^a-z0-9 ]/', '', $cadena);
    }
}

/* -------------------------------------------------------------------------- */
/*         Convertir fechas en array (formato n/j/Y para excel)              */
/* -------------------------------------------------------------------------- */
if (!function_exists('convertirFechasEnArray')) {
    function convertirFechasEnArray(array $data): array
    {
        return array_map(function ($row) {
            foreach (['fecha_publicacion', 'fecha_desfijacion'] as $campo) {
                if (isset($row[$campo]) && is_numeric($row[$campo])) {
                    try {
                        $row[$campo] = Carbon::instance(Date::excelToDateTimeObject($row[$campo]))->format('n/j/Y');
                    } catch (\Exception $e) {
                        $row[$campo] = null;
                    }
                }
            }
            return $row;
        }, $data);
    }
}

/* -------------------------------------------------------------------------- */
/*         Extraer Código desde Columna (tipo "1234 - Nombre")               */
/* -------------------------------------------------------------------------- */
if (!function_exists('extraerCodigoDesdeColumna')) {
    function extraerCodigoDesdeColumna(array &$rows, array $headers, string $nombreColumna)
    {
        $indice = array_search(strtolower($nombreColumna), array_map('strtolower', $headers));
        if ($indice === false) return;

        foreach ($rows as &$fila) {
            if (isset($fila[$indice])) {
                $valor = trim($fila[$indice]);
                $fila[$indice] = explode('-', $valor)[0];
            }
        }
        unset($fila);
    }
}
/* -------------------------------------------------------------------------- */
/*                      Valida si el archivo es PDF                           */
/* -------------------------------------------------------------------------- */
if (!function_exists('esPDFValido')) {
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
}

/* -------------------------------------------------------------------------- */
/*          Valida si el archivo es Excel se encuentra abierto                */
/* -------------------------------------------------------------------------- */
if (!function_exists('estaBloqueado')) {

    function estaBloqueado($rutaArchivo)
    {
        $handle = @fopen($rutaArchivo, 'r+');

        if ($handle === false) {
            return true; // No se puede abrir: probablemente está bloqueado o en uso
        }

        fclose($handle);
        return false; // El archivo está libre
    }
}
/* -------------------------------------------------------------------------- */
/*             Verifica obtener progreso Carga de archivo                     */
/* -------------------------------------------------------------------------- */
function obtenerProgresoCarga()
{
    $inicio = microtime(true);

    $progreso = DB::table('evento_auditoria')
        ->whereIn('estado_auditoria', ['E'])
        ->orderByDesc('fecha_auditoria')
        ->get()
        ->map(function ($evento) {
            $datos_json = $evento->datos_adicionales ?? '{}';
            $datos = json_decode($datos_json, true);
            Log::warning('Datos ', ['datos_adicionales' => $datos]);

            // Si el resultado es string (porque es JSON doble), decodifica otra vez
            if (is_string($datos)) {
                $datos = json_decode($datos, true);
            } else if (is_array($datos)) {
                $datos = $datos;
            } else {
                $datos = [];
            }

            // Validar y normalizar el campo progreso
            $progreso_raw = $datos['progreso'] ?? 0;
            $progreso_float = is_numeric($progreso_raw) ? floatval($progreso_raw) : 0;
            $progreso = min(max(0, (int) $progreso_float), 100);

            return [
                'archivo' => $datos['archivo'] ?? 'Desconocido',
                'progreso' => $progreso,
                'n_registros' => $evento->cont_registros,
                'n_pdfs' => $datos['pdfsAsociados'] ?? 0,
                'estado_codigo' => $evento->estado_auditoria,
                'estado' => 'En proceso', // ya está filtrado
                'observaciones' => $datos['observaciones'] ?? '',
                'fecha' => $evento->fecha_auditoria,
                'id_plantilla' => $evento->id_plantilla ?? $datos['tipo_plantilla'] ?? null,
            ];
        })->toArray();
    sleep(2); // <- Simula una pausa de 2 segundos
    Log::debug('Tiempo en obtener eventos: ' . (microtime(true) - $inicio));
    Log::info('Progreso de carga obtenido', [
        'progreso log' => $progreso,
    ]);

    return $progreso;
}

// if (!function_exists('conversor')) {
//     function conversor($data, $columnas_no_vacias) {
//         return $data = array_map(function ($fila) use ($columnas_no_vacias) {
//             $valores = array_values($fila);
        
//             // Convertir HORA_REG
//             $posHoraReg = array_search('HORA_REG', $columnas_no_vacias);
//             if ($posHoraReg !== false && isset($valores[$posHoraReg]) && is_numeric($valores[$posHoraReg])) {
//                 $segundos = round($valores[$posHoraReg] * 86400);
//                 $valores[$posHoraReg] = gmdate('H:i:s', $segundos);
//             }
        
//             // Convertir FECHAS
//             $fechasColumnas = [ 'FEC_ACT_TRA', 'FEC_REG','fecha_publicacion', 'fecha_desfijacion',];
//             foreach ($fechasColumnas as $fechaCol) {
//                 $index = array_search($fechaCol, $columnas_no_vacias);
//                 if ($index !== false && isset($valores[$index]) && is_numeric($valores[$index])) {
//                     $valores[$index] = Carbon::createFromDate(1900, 1, 1)->addDays($valores[$index] - 2)->format('Y-m-d');
//                 }
//             }
        
//             if (count($columnas_no_vacias) !== count($valores)) {
//                 dd('❌ Error: Número de columnas_no_vacias y valores no coinciden.', [
//                     'columnas_no_vacias' => $columnas_no_vacias,
//                     'valores' => $valores,
//                 ]);
//             }
        
//             return array_combine($columnas_no_vacias, $valores);
//         }, $data);
//     }
// }


// if (!function_exists('verificarPdfsEnCsv')) {
//     function verificarPdfsEnCsv($username, $folder)
//     {
//         // Configuración de rutas
//         $baseDir = storage_path('app/public'); // Nueva ruta correcta 
//         $rutaCarpeta = $baseDir . '/users/' . $username; // Ruta Origen
//         $destino = $baseDir . "/pdfs/" . $folder; // Ruta Destino
//         // dd([
//         //     'baseDir' => $baseDir,
//         //     'rutaCarpeta' => $rutaCarpeta,
//         //     'destino' => $destino,
//         // ]);
//         $columnaNombre = 'nom_con';

//         try {
//             // Verificar si la carpeta existe
//             if (!is_dir($rutaCarpeta)) {
//                 return ["success" => false, "message" => "Error: La carpeta de origen no existe."];
//             }

//             // Obtener archivos de la carpeta
//             $archivos = scandir($rutaCarpeta);

//             // Filtrar archivos CSV
//             $archivosCsv = array_filter($archivos, function ($archivo) use ($rutaCarpeta) {
//                 return is_file($rutaCarpeta . '/' . $archivo) && pathinfo($archivo, PATHINFO_EXTENSION) === 'csv';
//             });

//             // Filtrar archivos PDF y limpiar nombres
//             $archivosPdf = array_map(
//                 fn($pdf) => strtolower(trim($pdf)),
//                 array_filter($archivos, function ($archivo) use ($rutaCarpeta) {
//                     return is_file($rutaCarpeta . '/' . $archivo) && pathinfo($archivo, PATHINFO_EXTENSION) === 'pdf';
//                 })
//             );

//             if (empty($archivosCsv)) {
//                 return ["success" => false, "message" => "Error: No se encontró ningún archivo CSV en la carpeta."];
//             }

//             if (count($archivosCsv) > 1) {
//                 return ["success" => false, "message" => "Error: Solo debe haber un archivo CSV en la carpeta."];
//             }

//             if (empty($archivosPdf)) {
//                 return ["success" => false, "message" => "Error: No hay suficientes archivos PDF."];
//             }

//             // Obtener el archivo CSV
//             $archivoCsv = $rutaCarpeta . '/' . reset($archivosCsv);
//             $csv = array_map('str_getcsv', file($archivoCsv));

//             // Obtener encabezados del CSV
//             $encabezados = array_map('trim', $csv[0]);
//             $datos = array_slice($csv, 1);

//             if (!in_array($columnaNombre, $encabezados)) {
//                 return ["success" => false, "message" => "Error: El archivo CSV no contiene la columna '$columnaNombre'."];
//             }

//             // Obtener nombres válidos desde el CSV
//             $indiceColumna = array_search($columnaNombre, $encabezados);
//             $nombresValidos = array_map(fn($fila) => strtolower(trim($fila[$indiceColumna])), $datos);

//             // Verificar PDFs que no están en el CSV
//             $pdfsFaltantes = array_filter($nombresValidos, fn($nombre) => !in_array("$nombre.pdf", $archivosPdf));
//             $pdfNoEncontrados = array_filter($archivosPdf, fn($pdf) => !in_array(str_replace('.pdf', '', $pdf), $nombresValidos));

//             if (!empty($pdfNoEncontrados)) {
//                 return ["success" => false, "message" => "Error: Los siguientes PDFs no están en el CSV: " . implode(", ", $pdfNoEncontrados)];
//             }

//             if (!empty($pdfsFaltantes)) {
//                 return ["success" => false, "message" => "Error: Faltan los siguientes PDFs: " . implode(", ", $pdfsFaltantes)];
//             }

//             // Mover archivos PDF a la carpeta de destino
//             if (!is_dir($destino)) {
//                 mkdir($destino, 0777, true);
//             }

//             foreach ($archivosPdf as $pdf) {
//                 rename("$rutaCarpeta/$pdf", "$destino/$pdf");
//             }

//             // Importar el CSV
//             Excel::import(new NotificacionesHaciendaImport, new \Illuminate\Http\UploadedFile($archivoCsv, basename($archivoCsv)));

//             return ["success" => true, "message" => "Éxito: Todos los archivos PDF fueron movidos correctamente."];
//         } catch (Exception $e) {
//             return ["success" => false, "message" => "Error inesperado: " . $e->getMessage()];
//         }
//     }
// }