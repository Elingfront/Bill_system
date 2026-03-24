<?php
namespace App\Controllers;

require_once dirname(__DIR__) . '/Core/Database.php';
require_once dirname(__DIR__) . '/Models/Factura.php';

use App\Models\Factura;
use App\Core\Database;
use Exception;

class FacturaController {

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            $modelo = new Factura();
            $db     = Database::getConnection();

            // ─────────────────────────────────────────────────────────────────
            // 1. DATOS DEL FORMULARIO
            // ─────────────────────────────────────────────────────────────────
            $numFactura = trim($_POST['numero_factura'] ?? '');
            if (!$numFactura) throw new Exception("El número de factura es obligatorio.");

            $fechaEmision = $_POST['fecha_emision'] ?? date('Y-m-d');
            $tipo         = $_POST['tipo_factura']  ?? 'compra';
            $estaPagada   = isset($_POST['pagada']);
            $estado       = $estaPagada ? 'pagada' : 'pendiente';
            $usuarioId    = $_SESSION['user_id'] ?? 1;
            $entidadId    = $_POST['entidad_id']  ?? null;

            if (!$entidadId) throw new Exception("Debe seleccionar un tercero.");

            // ─────────────────────────────────────────────────────────────────
            // 2. NIT Y NOMBRE DEL TERCERO
            //    Vienen de los campos ocultos llenados por procesar_ia.php.
            //    Si están vacíos (selección manual sin IA), los buscamos en DB.
            // ─────────────────────────────────────────────────────────────────
            $nitCedula     = trim($_POST['nit_cedula']     ?? '');
            $nombreTercero = trim($_POST['nombre_tercero'] ?? '');

            if (empty($nitCedula) || empty($nombreTercero)) {
                $stmt = $db->prepare(
                    "SELECT nit_cedula, nombre FROM entidades WHERE id = ? LIMIT 1"
                );
                $stmt->execute([$entidadId]);
                $entidad = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($entidad) {
                    $nitCedula     = $nitCedula     ?: ($entidad['nit_cedula'] ?? '');
                    $nombreTercero = $nombreTercero ?: ($entidad['nombre']     ?? '');
                }
            }

            // ─────────────────────────────────────────────────────────────────
            // 3. SELLO ALT  (YYMM-NúmeroFactura)
            // ─────────────────────────────────────────────────────────────────
            $periodo  = date('ym', strtotime($fechaEmision));
            $selloAlt = $periodo . "-" . $numFactura;

            // ─────────────────────────────────────────────────────────────────
            // 4. RUTAS DE CARPETAS
            // ─────────────────────────────────────────────────────────────────
            $mesesNombres = [
                1 => 'Enero',     2 => 'Febrero',   3 => 'Marzo',
                4 => 'Abril',     5 => 'Mayo',       6 => 'Junio',
                7 => 'Julio',     8 => 'Agosto',     9 => 'Septiembre',
                10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $mesNum    = (int) date('m', strtotime($fechaEmision));
            $anioNum   = date('Y', strtotime($fechaEmision));
            $nombreMes = $mesesNombres[$mesNum];

            $uploadPath = "C:\\ALT_SISTEMA_DATA\\facturas";
            $subDir     = ($tipo === 'compra') ? 'compras' : 'ventas';
            $folderName = "FACT_" . $numFactura;

            // Estructura: C:\ALT_SISTEMA_DATA\facturas\compras\2026\Febrero\FACT_3866\
            $rutaRaiz = implode(DIRECTORY_SEPARATOR, [
                $uploadPath, $subDir, $anioNum, $nombreMes, $folderName, ''
            ]);

            $rutaCuentas = null;
            if (!$estaPagada) {
                $subDirCuentas = ($tipo === 'compra') ? 'Cuentas de Pago' : 'Cuentas de Cobro';
                $rutaCuentas   = implode(DIRECTORY_SEPARATOR, [
                    $uploadPath, $subDirCuentas, $folderName, ''
                ]);
            }

            if (!is_dir($rutaRaiz)) {
                if (!mkdir($rutaRaiz, 0777, true)) {
                    throw new Exception("Error al crear carpeta: " . $rutaRaiz);
                }
            }
            if ($rutaCuentas && !is_dir($rutaCuentas)) {
                if (!mkdir($rutaCuentas, 0777, true)) {
                    throw new Exception("Error al crear carpeta de cuentas: " . $rutaCuentas);
                }
            }

            // ─────────────────────────────────────────────────────────────────
            // 5. SUBIR PDF DE FACTURA
            // ─────────────────────────────────────────────────────────────────
            $nombreFactura = null;
            if (
                isset($_FILES['pdf_factura']) &&
                $_FILES['pdf_factura']['error'] === UPLOAD_ERR_OK
            ) {
                $nombreFactura = $_FILES['pdf_factura']['name'];
                if (!move_uploaded_file($_FILES['pdf_factura']['tmp_name'], $rutaRaiz . $nombreFactura)) {
                    throw new Exception("Error al mover el archivo PDF.");
                }
                if ($rutaCuentas) {
                    copy($rutaRaiz . $nombreFactura, $rutaCuentas . $nombreFactura);
                }
            }

            // ─────────────────────────────────────────────────────────────────
            // 6. GUARDAR EN BASE DE DATOS
            //    Ahora incluye: numero_factura_ia, nit_cedula y nombre
            // ─────────────────────────────────────────────────────────────────
            $datos = [
                'sello_alt'         => $selloAlt,
                'numero_factura_ia' => $numFactura,   // ← antes nunca se guardaba
                'entidad_id'        => $entidadId,
                'nit_cedula'        => $nitCedula,    // ← antes siempre NULL
                'nombre'            => $nombreTercero,// ← antes siempre NULL
                'fecha_emision'     => $fechaEmision,
                'estado'            => $estado,
                'usuario_id'        => $usuarioId,
                'archivo_path'      => $nombreFactura,
                'ruta_carpeta'      => $rutaRaiz,
                'soporte_pago_path' => null,
                'egreso_path'       => null,
                'fecha_pago'        => $estaPagada ? date('Y-m-d') : null,
            ];

            $id = ($tipo === 'compra')
                ? $modelo->guardar($datos)
                : $modelo->guardarVenta($datos);

            if (!$id) throw new Exception('Error al registrar en la base de datos.');

            // ─────────────────────────────────────────────────────────────────
            // 7. SOPORTES DE PAGO (solo si entra marcada como pagada)
            // ─────────────────────────────────────────────────────────────────
            if ($estaPagada) {
                $nSoporte = $_FILES['archivo_soporte']['name'] ?? null;
                $nEgreso  = $_FILES['archivo_contable']['name'] ?? null;

                if ($nSoporte && $_FILES['archivo_soporte']['error'] === UPLOAD_ERR_OK) {
                    move_uploaded_file($_FILES['archivo_soporte']['tmp_name'], $rutaRaiz . $nSoporte);
                }
                if ($nEgreso && $_FILES['archivo_contable']['error'] === UPLOAD_ERR_OK) {
                    move_uploaded_file($_FILES['archivo_contable']['tmp_name'], $rutaRaiz . $nEgreso);
                }

                $modelo->actualizarRutas($id, $nombreFactura, $rutaRaiz, $tipo, $nSoporte, $nEgreso);
            }

            echo json_encode(['status' => 'success', 'sello' => $selloAlt]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
}