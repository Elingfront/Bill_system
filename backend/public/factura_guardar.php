<?php
require_once __DIR__ . '/../boostrap.php';

use App\Controllers\FacturaController;

$controller = new FacturaController();
$controller->guardar();
