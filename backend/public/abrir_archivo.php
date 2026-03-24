<?php
if (isset($_GET['file'])) {
    $path = $_GET['file'];
    
    // Validar que el archivo exista y esté en nuestra carpeta de datos
    if (file_exists($path) && strpos($path, 'ALT_SISTEMA_DATA') !== false) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        switch ($ext) {
            case 'pdf':
                header('Content-Type: application/pdf');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            default:
                header('Content-Type: application/octet-stream');
                break;
        }
        
        header('Content-Disposition: inline; filename="'.basename($path).'"');
        readfile($path);
        exit;
    }
}
die("Archivo no encontrado o acceso denegado.");
