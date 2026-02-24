<?php
namespace App\Controllers;

require_once dirname(__DIR__) . '/Core/Database.php'; 
require_once dirname(__DIR__) . '/Models/Factura.php'; 

use App\Models\Factura;
use App\Core\Database;

class FacturaController {
    
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $uploadPath = "C:\\ALT_SISTEMA_DATA\\facturas"; 
            $modelo = new Factura();
            
            $tipo = $_POST['tipo_factura'] ?? 'compra';
            $estaPagada = isset($_POST['pagada']); 
            $estadoFinal = $estaPagada ? 'pagada' : 'pendiente';

            $datos = [
                'entidad_id'    => $_POST['entidad_id'] ?? null,
                'fecha_emision' => $_POST['fecha_emision'] ?? date('Y-m-d'),
                'estado'        => $estadoFinal,
                'usuario_id'    => 1,
                'archivo_path'  => '',
                'ruta_carpeta'  => '',
                'soporte_pago_path' => null,
                'egreso_path'   => null,
                'fecha_pago'    => $estaPagada ? date('Y-m-d') : null
            ];

            if (!$datos['entidad_id']) throw new \Exception('Debe seleccionar un tercero');

            // 1. Registro inicial en BD
            $idGenerado = ($tipo === 'compra') ? $modelo->guardar($datos) : $modelo->guardarVenta($datos);
            if (!$idGenerado) throw new \Exception('Error al insertar registro');

            // 2. Crear carpeta del Folio
            $folioTexto = str_pad($idGenerado, 3, "0", STR_PAD_LEFT);
            $subDir = ($tipo === 'compra') ? 'compras' : 'ventas';
            $baseDir = $uploadPath . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . "FACT_" . $folioTexto . DIRECTORY_SEPARATOR;

            if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

            // 3. Mover Factura
            $nombreFactura = "factura_" . $folioTexto . ".pdf";
            move_uploaded_file($_FILES['pdf_factura']['tmp_name'], $baseDir . $nombreFactura);

            // 4. Mover archivos adicionales si aplica
            $nombreSoporte = null;
            $nombreEgreso = null;

            if ($estaPagada) {
                if (!empty($_FILES['archivo_soporte']['name'])) {
                    $nombreSoporte = "soporte_" . $folioTexto . ".pdf";
                    move_uploaded_file($_FILES['archivo_soporte']['tmp_name'], $baseDir . $nombreSoporte);
                }
                if (!empty($_FILES['archivo_contable']['name'])) {
                    $prefijo = ($tipo === 'compra') ? "egreso_" : "recibo_";
                    $nombreEgreso = $prefijo . $folioTexto . ".pdf";
                    move_uploaded_file($_FILES['archivo_contable']['tmp_name'], $baseDir . $nombreEgreso);
                }
            }

            // 5. Actualizar rutas definitivas
            $modelo->actualizarRutas($idGenerado, $nombreFactura, $baseDir, $tipo, $nombreSoporte, $nombreEgreso);
            
            header("Location: /sistema_facturas/backend/views/cargar_factura.php?res=ok");
            
        } catch (\Exception $e) {
            header("Location: /sistema_facturas/backend/views/cargar_factura.php?res=error&msg=" . urlencode($e->getMessage()));
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new FacturaController())->guardar();
}