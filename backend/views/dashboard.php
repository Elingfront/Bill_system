<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$titulo = "Sistema Contable | ALT ";

include __DIR__ . '/auth/header.php';
include_once __DIR__ . '/../app/Controllers/DashboardController.php'; 
include __DIR__ . '/auth/sidebar.php';
require_once dirname(__DIR__) . '/../vendor/autoload.php';

use App\Controllers\DashboardController;
$resumen = DashboardController::obtenerResumen();
?>

<div class="main-wrapper bg-white" style="min-height: 100vh; margin-left: 260px;"> 
    <nav class="navbar navbar-expand-lg border-bottom p-3 bg-white shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="/sistema_facturas/logosinfondo.png" alt="ALT" width="40" class="me-3">
                <span class="fw-bold fs-4" style="color: #633035;">ALT-CONFECCIONES</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end me-2">
                    <h4 class="text-muted d-block">Bienvenido,</h4>
                    <span class="fw-bold" style="color: #633035;"> al Servicio Contable ALT</span>
                </div>
                <a href="auth/logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm">
                    <i data-lucide="log-out" class="me-1" style="width: 16px;"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-5">
        <h2 class="fw-bold mb-1" style="color: #633035;">Resumen de Actividad</h2><br>
        <p class="text-muted small mb-5">Haga clic en la carpeta para desplegar los documentos registrados.</p>

        <?php if ($resumen && isset($resumen['datos'])): 
            $d = $resumen['datos'];
            $id = str_pad($d['id'], 3, "0", STR_PAD_LEFT);
            $ruta = $d['ruta_carpeta'];

            // Arreglo de documentos para el bucle
            $docsArr = [
                ['f' => $d['archivo_path'], 'l' => 'FACTURA', 'c' => 'text-success', 'i' => 'file-check'],
                ['f' => $d['soporte_pago_path'], 'l' => 'SOPORTE', 'c' => 'text-primary', 'i' => 'file-text'],
                ['f' => $d['egreso_path'], 'l' => 'DOC. CONTABLE', 'c' => 'text-danger', 'i' => 'file-minus']
            ];
        ?>
            <div class="d-flex align-items-start mt-4">
                
                <div class="folder-action" onclick="toggleArchivos()" style="cursor: pointer; width: 280px;">
                    <div class="animated-folder-container shadow-sm">
                        <div class="folder-back"></div>
                        <div class="folder-front">
                            <img src="/sistema_facturas/logosinfondo.png" alt="ALT" width="90">
                        </div>
                    </div>
                        <div class="mt-4 text-start">
                            <?php 
                                // Si la ruta contiene 'ventas', es venta, si no, es compra
                                $esVenta = (strpos($ruta, 'ventas') !== false);
                                $tipoTexto = $esVenta ? 'VENTA' : 'COMPRA';
                            ?>
                            <h4 class="fw-bold mb-0" style="color: #633035;"><?= $tipoTexto ?>: FACT_<?= $id ?></h4>
                            <span class="text-danger d-block mt-1" style="font-size: 11px; font-family: monospace;"><?= $ruta ?></span>
                        </div>                
                    </div>

                <div id="stack-archivos" class="d-none animate-pop-in ms-5">
                    <div class="d-flex flex-column gap-3 ps-4" style="border-left: 4px solid #633035;">
                        <?php 
                        foreach ($docsArr as $doc): 
                            // CORRECCIÓN LÍNEA 47: Validar que no sea NULL antes de procesar
                            if (!empty($doc['f'])): 
                                $fullPath = $ruta . $doc['f'];
                                if (file_exists($fullPath)): 
                                    // Determinar etiqueta si es recibo o egreso
                                    $label = $doc['l'];
                                    if ($doc['l'] == 'DOC. CONTABLE') {
                                        $label = (str_contains($doc['f'], 'recibo')) ? 'RECIBO CAJA' : 'EGRESO';
                                    }
                        ?>
                            <div class="file-item bg-white border shadow-sm p-3 d-flex align-items-center" style="width: 480px; border-radius: 12px;">
                                <i data-lucide="<?= $doc['i'] ?>" class="<?= $doc['c'] ?> me-3" style="width: 28px; height: 28px;"></i>
                                <div class="flex-grow-1">
                                    <span class="fw-bold d-block"><?= $label ?> - <?= $id ?></span>
                                    <small class="text-muted"><?= $doc['f'] ?></small>
                                </div>
                                <a href="abrir_archivo.php?file=<?= urlencode($fullPath) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i data-lucide="eye" class="me-1" style="width: 14px;"></i> Ver
                                </a>
                            </div>
                        <?php endif; endif; endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-5 pt-4">
            <a href="cargar_factura.php" class="btn btn-vinotinto-outline px-5 py-2 rounded-pill shadow-sm">
                <i data-lucide="upload-cloud" class="me-2"></i> Cargar Nueva Factura
            </a>
        </div>
    </div>
</div>

<style>
    .btn-vinotinto-outline { border: 2px solid #633035; color: #633035; font-weight: bold; background: white; text-decoration: none; transition: 0.3s; }
    .btn-vinotinto-outline:hover { background: #633035; color: white; }

    /* Estilos Carpeta */
    .animated-folder-container { position: relative; width: 230px; height: 160px; transition: 0.3s; }
    .folder-back { position: absolute; width: 100%; height: 100%; background: #ffc107; border-radius: 12px; top: -10px; }
    .folder-front { 
        position: absolute; width: 100%; height: 92%; background: #ffca28; 
        bottom: 0; border-radius: 12px; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1); z-index: 5; 
    }
    .folder-action:hover .animated-folder-container { transform: scale(1.02); }

    .file-item { transition: 0.3s; }
    .file-item:hover { transform: translateX(10px); border-color: #633035 !important; }

    .animate-pop-in { animation: popIn 0.4s ease-out; }
    @keyframes popIn { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
</style>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
    function toggleArchivos() {
        const stack = document.getElementById('stack-archivos');
        stack.classList.toggle('d-none');
    }
</script>