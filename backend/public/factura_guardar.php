<?php
require_once __DIR__ . '/../boostrap.php';
require_once __DIR__ . '/../app/Controllers/FacturaController.php';

use App\Controllers\FacturaController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new FacturaController();
    $controller->guardar();
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
