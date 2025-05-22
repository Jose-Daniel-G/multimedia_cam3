<?php

if (!function_exists('normalizar')) {
    function normalizar($cadena) {
        // Convertir a minúsculas para evitar diferencias en mayúsculas
        $cadena = mb_strtolower($cadena, 'UTF-8');

        // Reemplazar caracteres con tildes y la "ñ"
        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'n'];
        $cadena = str_replace($buscar, $reemplazar, $cadena);

        // Eliminar cualquier otro carácter no alfanumérico excepto espacios
        return preg_replace('/[^a-z0-9 ]/', '', $cadena);
    }
}
if (!function_exists('extraerCodigoDesdeColumna')) {
    function extraerCodigoDesdeColumna(array &$rows, array $headers, string $nombreColumna)
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