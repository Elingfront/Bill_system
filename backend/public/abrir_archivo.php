<?php
// abrir_archivo.php
if (isset($_GET['file'])) {
    $path = $_GET['file'];
    // Validamos que el archivo exista y esté en nuestra carpeta de datos
    if (file_exists($path) && strpos($path, 'ALT_SISTEMA_DATA') !== false) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.basename($path).'"');
        readfile($path);
        exit;
    }
}
die("Archivo no encontrado o acceso denegado.");