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
