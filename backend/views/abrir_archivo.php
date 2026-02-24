<?php

if (isset($_GET['file'])) {
    $rutaCompleta = $_GET['file'];

    if (file_exists($rutaCompleta) && is_file($rutaCompleta)) {
        $mime = mime_content_type($rutaCompleta);

        if ($mime === 'application/pdf') {

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($rutaCompleta) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');    
            
            
            readfile($rutaCompleta);
                        exit;
                    } else {
                        echo "El archivo no es un PDF válido.";
                    }
                } else {
                    echo "Error: El archivo físico no se encuentra en la ruta: " . htmlspecialchars($rutaCompleta);
                }
            } else {
                echo "No se especificó ningún archivo.";
            }
?>