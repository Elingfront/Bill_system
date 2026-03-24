<?php
require_once __DIR__ . '/../app/Core/Database.php'; 
use App\Core\Database;

$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $factura_id = $_POST['factura_id'];
    $tipo = $_POST['tipo_proceso']; // Recibe 'compra' o 'venta' melitico
    $tabla = ($tipo === 'compra') ? 'facturas_compra' : 'facturas_venta';

    try {
        // 1. Obtener info actual
        $stmt = $db->prepare("SELECT id, archivo_path, ruta_carpeta, fecha_emision FROM $tabla WHERE id = ?");
        $stmt->execute([$factura_id]);
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$factura) throw new Exception("Factura no encontrada");

        $archivoPDF = $factura['archivo_path'];
        $rutaOrigen = $factura['ruta_carpeta']; 

        // 2. Identificar la carpeta del folio (FACT_XXX)
        $partes = explode(DIRECTORY_SEPARATOR, rtrim($rutaOrigen, DIRECTORY_SEPARATOR));
        $folioCarpeta = end($partes);
        
        // 3. Definir Destino Raíz con estructura Año/Mes (igual que compras)
        $subDirRaiz = ($tipo === 'compra') ? 'compras' : 'ventas';
        
        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $fechaEmision = $factura['fecha_emision'] ?? date('Y-m-d');
        $anioNum = date('Y', strtotime($fechaEmision));
        $mesNum = (int)date('m', strtotime($fechaEmision));
        $nombreMes = $mesesNombres[$mesNum];
        
        $rutaDestino = "C:\\ALT_SISTEMA_DATA\\facturas\\" . $subDirRaiz . "\\" . $anioNum . "\\" . $nombreMes . "\\" . $folioCarpeta . "\\";

        if (!is_dir($rutaDestino)) mkdir($rutaDestino, 0777, true);

        // --- EL MOVIMIENTO MAESTRO ---
        // Si el PDF ya estaba en raíz (porque se guardó ahí al crear), no pasa nada.
        // Si estaba solo en Cuentas, lo movemos a raíz.
        if (file_exists($rutaOrigen . $archivoPDF) && !file_exists($rutaDestino . $archivoPDF)) {
            rename($rutaOrigen . $archivoPDF, $rutaDestino . $archivoPDF);
        }

        // --- SUBIR LOS 2 QUE FALTAN (Para completar los 3 en la carpeta) ---
        $nombreSoporte = null;
        if (isset($_FILES['soporte_pago']) && $_FILES['soporte_pago']['error'] === 0) {
            $nombreSoporte = $_FILES['soporte_pago']['name'];
            move_uploaded_file($_FILES['soporte_pago']['tmp_name'], $rutaDestino . $nombreSoporte);
        }

        $nombreEgreso = null;
        if (isset($_FILES['egreso']) && $_FILES['egreso']['error'] === 0) {
            $nombreEgreso = $_FILES['egreso']['name'];
            move_uploaded_file($_FILES['egreso']['tmp_name'], $rutaDestino . $nombreEgreso);
        }

        // 4. LIMPIAR CARPETA TEMPORAL (origen)
        if ($rutaOrigen !== $rutaDestino && is_dir($rutaOrigen)) {
            $files = glob($rutaOrigen . '*');
            foreach ($files as $file) { if (is_file($file)) unlink($file); }
            @rmdir($rutaOrigen);
        }

        // 4b. LIMPIAR CARPETA DE CUENTAS (Pago o Cobro) 
        $subDirCuentas = ($tipo === 'compra') ? 'Cuentas de Pago' : 'Cuentas de Cobro';
        $rutaCuentas = "C:\\ALT_SISTEMA_DATA\\facturas\\" . $subDirCuentas . "\\" . $folioCarpeta . "\\";
        if ($rutaCuentas !== $rutaDestino && is_dir($rutaCuentas)) {
            $files = glob($rutaCuentas . '*');
            foreach ($files as $file) { if (is_file($file)) unlink($file); }
            @rmdir($rutaCuentas);
        }

        // 5. ACTUALIZAR BD CON LA RUTA RAÍZ Y LOS 3 ARCHIVOS
        $sql = "UPDATE $tabla SET 
                estado = 'pagada', 
                ruta_carpeta = ?, 
                soporte_pago_path = ?, 
                egreso_path = ?, 
                fecha_pago = NOW() 
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$rutaDestino, $nombreSoporte, $nombreEgreso, $factura_id]);

        $redirect = ($tipo === 'compra') ? 'cuentas_pago.php' : 'cuentas_cobro.php';
        header("Location: " . $redirect . "?success=1");
        exit;

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}