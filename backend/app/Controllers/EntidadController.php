<?php
namespace App\Controllers;

require_once dirname(__DIR__, 2) . '/../vendor/autoload.php';
use App\Models\entidadM;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = new entidadM();

     $datos = [
        'nombre'    => $_POST['nombre'] ?? null,
        'nit'       => $_POST['nit'] ?? null,
        'tipo'      => $_POST['tipo'] ?? 'proveedor', // Valor por defecto
        'telefono'  => $_POST['telefono'] ?? null,
        'correo'    => $_POST['correo'] ?? null,  
        'direccion' => $_POST['direccion'] ?? null
    ];

    if($modelo->guardar($datos)) {
        header("Location: ../../views/entidad.php"); // Volver a la factura
        exit;
    } else {
        die ("ERROR: No se pudo registrar el tercero en la BD.");
    }
}