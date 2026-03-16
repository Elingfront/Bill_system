<?php
require_once dirname(__DIR__) . '/Core/Database.php'; 
require_once dirname(__DIR__) . '/Models/Factura.php'; 

use App\Models\Factura;
use App\Core\Database;

class FacturaController {
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        header('Content-Type: application/json');

        try {
            $modelo = new Factura();
            
            $numFactura = $_POST['numero_factura'] ?? 'SIN_NUMERO';
            $folioCarpeta = "FACT_" . $numFactura;

            $tipo = $_POST['tipo_factura'] ?? 'compra';
            $estaPagada = isset($_POST['pagada']); 
            $estado = $estaPagada ? 'pagada' : 'pendiente';

            $uploadPath = "C:\\ALT_SISTEMA_DATA\\facturas"; 
            
            // --- 1. DEFINIR RUTAS ---
            // Ruta Raíz (Siempre existe)
            $subDirRaiz = ($tipo === 'compra') ? 'compras' : 'ventas';
            $rutaRaiz = $uploadPath . DIRECTORY_SEPARATOR . $subDirRaiz . DIRECTORY_SEPARATOR . $folioCarpeta . DIRECTORY_SEPARATOR;

            // Ruta de Cuentas (Solo si está pendiente)
            $rutaCuentas = null;
            if (!$estaPagada) {
                $subDirCuentas = ($tipo === 'compra') ? 'Cuentas de Pago' : 'Cuentas de Cobro';
                $rutaCuentas = $uploadPath . DIRECTORY_SEPARATOR . $subDirCuentas . DIRECTORY_SEPARATOR . $folioCarpeta . DIRECTORY_SEPARATOR;
            }

            // Crear carpetas si no existen
            if (!is_dir($rutaRaiz)) mkdir($rutaRaiz, 0777, true);
            if ($rutaCuentas && !is_dir($rutaCuentas)) mkdir($rutaCuentas, 0777, true);

            // --- 2. PROCESAR EL PDF ---
            $nombreFactura = $_FILES['pdf_factura']['name'] ?? null;
            if ($nombreFactura) {
                // Primero se sube a la RAÍZ
                move_uploaded_file($_FILES['pdf_factura']['tmp_name'], $rutaRaiz . $nombreFactura);
                
                // Si está pendiente, hacemos el ESPEJO (Copiamos de Raíz a Cuentas)
                if ($rutaCuentas) {
                    copy($rutaRaiz . $nombreFactura, $rutaCuentas . $nombreFactura);
                }
            }

            // --- 3. PREPARAR DATOS PARA BD ---
            $datos = [
                'entidad_id'        => $_POST['entidad_id'] ?? null,
                'fecha_emision'     => $_POST['fecha_emision'] ?? date('Y-m-d'),
                'estado'            => $estado,
                'usuario_id'        => 1,
                'archivo_path'      => $nombreFactura, 
                'ruta_carpeta'      => ($estaPagada ? $rutaRaiz : $rutaCuentas), // La vista buscará en Cuentas si está pendiente
                'soporte_pago_path' => null,
                'egreso_path'       => null,
                'fecha_pago'        => $estaPagada ? date('Y-m-d') : null
            ];

            // Guardar en BD
            $idGenerado = ($tipo === 'compra') ? $modelo->guardar($datos) : $modelo->guardarVenta($datos);
            if (!$idGenerado) throw new Exception('Error al insertar');

            // --- 4. SI ENTRA PAGADA DE UNA VEZ, PROCESAR SOPORTES ---
            if ($estaPagada) {
                $nombreSoporte = $_FILES['archivo_soporte']['name'] ?? null;
                $nombreEgreso = $_FILES['archivo_contable']['name'] ?? null;

                if ($nombreSoporte) move_uploaded_file($_FILES['archivo_soporte']['tmp_name'], $rutaRaiz . $nombreSoporte);
                if ($nombreEgreso) move_uploaded_file($_FILES['archivo_contable']['tmp_name'], $rutaRaiz . $nombreEgreso);
                
                $modelo->actualizarRutas($idGenerado, $nombreFactura, $rutaRaiz, $tipo, $nombreSoporte, $nombreEgreso);
            }

            echo json_encode(['status' => 'success']);
            exit;

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new FacturaController())->guardar();
}