<?php
session_start();
if (!isset($_SESSION['autenticado'])) {
    header("Location: login.php");
    exit();
}

error_reporting(0);
ini_set('display_errors', 0);

include __DIR__ . '/auth/header.php';
include __DIR__ . '/auth/sidebar.php';

require_once 'C:/wamp/www/sistema_facturas/backend/app/Core/Database.php';
use App\Core\Database;

$db = Database::getConnection();

/**
 * Consulta para Egresos: Solo facturas de compra que tengan egreso_path
 * Usamos el formato del Dashboard (JOIN con entidades)
 */
$sql = "SELECT f.id, f.fecha_emision, f.archivo_path, f.egreso_path, f.soporte_pago_path, f.ruta_carpeta, e.nit_cedula, e.nombre, f.estado, f.fecha_pago
        FROM facturas_compra f
        LEFT JOIN entidades e ON f.entidad_id = e.id
        WHERE f.egreso_path IS NOT NULL AND f.egreso_path != ''
        ORDER BY f.fecha_pago DESC";

try {
    $egresos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $egresos = [];
}
?>

<style>
    :root { --alt-vinotinto: #632626; }
    body { background-color: #fff; font-family: 'Segoe UI', sans-serif; }
    .main-content { margin-left: 280px; padding: 20px 40px; }

    /* ===== HEADER PROFESIONAL ALT ===== */
    .alt-page-header {
        background: linear-gradient(135deg, #632626 0%, #8b3a3a 50%, #4a1a1a 100%);
        border-radius: 12px; padding: 22px 28px; margin-bottom: 25px;
        display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 4px 15px rgba(99, 38, 38, 0.25);
        position: relative; overflow: hidden;
    }
    .alt-page-header::before {
        content: ''; position: absolute; top: -50%; right: -20%;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
        pointer-events: none;
    }
    .alt-header-content { display: flex; align-items: center; gap: 16px; z-index: 1; }
    .alt-header-icon {
        width: 48px; height: 48px; background: rgba(255,255,255,0.15);
        border-radius: 12px; display: flex; align-items: center; justify-content: center;
        color: #fff; backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.1);
    }
    .alt-header-title { font-size: 20px; font-weight: 800; color: #fff; margin: 0; letter-spacing: -0.3px; }
    .alt-header-subtitle { font-size: 12px; color: rgba(255,255,255,0.7); font-weight: 500; letter-spacing: 0.5px; }
    .alt-header-meta { display: flex; align-items: center; gap: 10px; z-index: 1; }
    .alt-header-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.9);
        padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px;
        backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.08);
    }

    /* Estilo de Filtros del Dashboard */
    .filter-section { background-color: #f8f9fa; border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin-bottom: 25px; }
    .label-min { font-size: 11px; font-weight: 800; color: #666; text-transform: uppercase; margin-bottom: 5px; }

    /* Tabla Estilo Dashboard */
    .tabla-alt { width: 100%; border-collapse: collapse; font-size: 13px; }
    .tabla-alt thead th { background-color: #f2f2f2; color: #000; padding: 12px 10px; border-bottom: 2px solid #333; text-align: left; }
    .tabla-alt tbody tr { border-bottom: 1px solid #eee; }
    .tabla-alt tbody tr:hover { background-color: #fcfcfc; }
    .tabla-alt td { padding: 12px 10px; color: #333; vertical-align: middle; }

    .nit-font { font-family: monospace; font-weight: bold; font-size: 14px; color: #444; }
    .doc-ref { font-weight: bold; color: var(--alt-vinotinto); font-size: 12px; }
    
    .modal-xl-custom { max-width: 98%; }
    .doc-viewer { height: 78vh; border-radius: 12px; border: 2px solid #eee; background: #fff; }
    .doc-label { font-size: 11px; font-weight: 800; color: var(--alt-vinotinto); text-transform: uppercase; margin-bottom: 8px; display: block; }
    .doc-header { background: #f8f9fa; padding: 10px; border-radius: 10px 10px 0 0; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 8px; }

    .badge-status { 
        font-size: 10px; font-weight: bold; padding: 3px 8px; border-radius: 3px; 
        text-transform: uppercase; border: 1px solid; display: inline-block;
    }
    .st-pagada { color: #198754; border-color: #198754; background: #eefaf3; }
</style>

<main class="main-content">
    <div class="alt-page-header">
        <div class="alt-header-content">
            <div class="alt-header-icon"><i data-lucide="arrow-down-circle" size="24"></i></div>
            <div>
                <h4 class="alt-header-title">Comprobantes de Egreso</h4>
                <span class="alt-header-subtitle">ALT-CONFECCIONES · Registro Contable</span>
            </div>
        </div>
        <div class="alt-header-meta">
            <span class="alt-header-badge"><i data-lucide="calendar" size="12"></i> <?= date('d/m/Y') ?></span>
            <span class="alt-header-badge"><i data-lucide="user" size="12"></i> <?= strtoupper($_SESSION['username'] ?? 'USUARIO') ?></span>
        </div>
    </div>

    <!-- Motor de Búsqueda del Dashboard -->
    <div class="filter-section shadow-sm">
        <div class="row align-items-end g-3">
            <div class="col-md-12">
                <div class="label-min">Búsqueda Rápida (NIT / Nombre / Egreso)</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i data-lucide="search" size="14"></i></span>
                    <input type="text" id="inputBusqueda" class="form-control" placeholder="Escriba para filtrar egresos..." onkeyup="ejecutarFiltro()">
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="tabla-alt" id="tablaEgresos">
            <thead>
                <tr>
                    <th style="width: 160px;">CC / NIT</th>
                    <th>PROVEEDOR</th>
                    <th style="width: 120px;">FECHA PAGO</th>
                    <th style="width: 150px;">REFERENCIA</th>
                    <th class="text-end">ESTADO / ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($egresos as $r): 
                    $parts = explode('FACT_', $r['ruta_carpeta']);
                    $folio = isset($parts[1]) ? rtrim($parts[1], DIRECTORY_SEPARATOR . '/') : $r['id'];
                    
                    $nit = $r['nit_cedula'] ?? '';
                    $nombre = $r['nombre'] ?? 'SIN NOMBRE';
                    $fecha = $r['fecha_pago'] ?: $r['fecha_emision'];
                ?>
                <tr class="row-egreso" 
                    data-search="<?= htmlspecialchars(strtolower($nit . ' ' . $nombre . ' egreso-' . $folio)) ?>">
                    <td class="nit-font"><?= htmlspecialchars($nit) ?: '---' ?></td>
                    <td class="text-uppercase">
                        <strong style="font-size: 13px;"><?= htmlspecialchars($nombre) ?></strong>
                    </td>
                    <td class="fw-bold text-muted"><?= ($fecha) ? date('d/m/Y', strtotime($fecha)) : '---' ?></td>
                    <td class="doc-ref">EGRESO-<?= $folio ?></td>
                    <td class="text-end">
                        <span class="badge-status st-pagada">PAGADA</span>
                        
                        <?php 
                        // URLs para el visor dual (Egreso + Banco)
                        $urlE = "abrir_archivo.php?file=" . urlencode(($r['ruta_carpeta'] ?? '') . ($r['egreso_path'] ?? ''));
                        $urlS = ($r['soporte_pago_path'] ?? '') ? "abrir_archivo.php?file=" . urlencode($r['ruta_carpeta'] . $r['soporte_pago_path']) : "";
                        ?>
                        
                        <button type="button" class="btn btn-link p-0 ms-2 text-dark" 
                                onclick="abrirVisorDual('<?= addslashes($urlE) ?>', '<?= addslashes($urlS) ?>')" 
                                title="Ver Comprobantes">
                            <i data-lucide="layout-grid" size="16"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Visor Dual (Igual al Dashboard) -->
<div class="modal fade" id="modalVisor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-xl-custom modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4" style="background-color: #fcfcfc;">
            <div class="modal-header border-0 pb-0 p-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark"><i data-lucide="files" class="me-2"></i>Visor de Egresos ALT</h5>
                    <p class="text-muted small mb-0">Comprobante de Egreso vs Soporte de Banco</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="doc-label">COMPROBANTE DE EGRESO</span>
                        <iframe id="iframeEgreso" class="doc-viewer w-100"></iframe>
                    </div>
                    <div class="col-md-6" id="colBanco">
                        <span class="doc-label">SOPORTE DE BANCO</span>
                        <iframe id="iframeBanco" class="doc-viewer w-100"></iframe>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-dark px-4 fw-bold" data-bs-dismiss="modal">Cerrar Visor</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    lucide.createIcons();

    const visorModal = new bootstrap.Modal(document.getElementById('modalVisor'));

    function abrirVisorDual(e, b) {
        document.getElementById('iframeEgreso').src = e;
        
        const colB = document.getElementById('colBanco');
        if(b) { 
            colB.classList.remove('d-none'); 
            document.getElementById('iframeBanco').src = b; 
        } else { 
            colB.classList.add('d-none'); 
        }

        visorModal.show();
    }

    document.getElementById('modalVisor').addEventListener('hidden.bs.modal', function () {
        document.getElementById('iframeEgreso').src = '';
        document.getElementById('iframeBanco').src = '';
    });

    function ejecutarFiltro() {
        const search = document.getElementById('inputBusqueda').value.toLowerCase();
        const rows = document.querySelectorAll('.row-egreso');

        rows.forEach(row => {
            const rowSearch = row.dataset.search;
            row.style.display = rowSearch.includes(search) ? '' : 'none';
        });
    }
</script>
