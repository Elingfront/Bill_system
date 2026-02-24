<?php
// Recibimos los datos del link generado en el Dashboard
$nombrePdf = $_GET['archivo'] ?? '';
$rutaCarpeta = $_GET['directorio'] ?? '';

$rutaCompleta = $rutaCarpeta . $nombrePdf;

if (!empty($nombrePdf) && file_exists($rutaCompleta)) {
    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename="' . $nombrePdf . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    @readfile($rutaCompleta);
} else {
    echo "<h3>Error: El archivo no se encuentra en el disco C:</h3>";
    echo "Ruta buscada: " . $rutaCompleta;
}