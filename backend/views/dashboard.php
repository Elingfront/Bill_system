<?php
session_start();
if (!isset($_SESSION['autenticado'])) {
    header("Location: login.php");
    exit();
}

include __DIR__ . '/auth/header.php';
include __DIR__ . '/auth/sidebar.php';

require_once 'C:/wamp/www/sistema_facturas/backend/app/Core/Database.php';
use App\Core\Database;

$db = Database::getConnection();

/**
 * SQL UNIFICADO CON SOPORTES (Y JOIN CON ENTIDADES)
 */
$sql = "(SELECT f.id, f.fecha_emision, 'COMPRA' as tipo, f.estado, f.ruta_carpeta, f.archivo_path, f.soporte_pago_path, f.egreso_path, e.nit_cedula, e.nombre 
         FROM facturas_compra f 
         LEFT JOIN entidades e ON f.entidad_id = e.id)
        UNION 
        (SELECT f.id, f.fecha_emision, 'VENTA' as tipo, f.estado, f.ruta_carpeta, f.archivo_path, f.soporte_pago_path, f.egreso_path, e.nit_cedula, e.nombre 
         FROM facturas_venta f 
         LEFT JOIN entidades e ON f.entidad_id = e.id)
        ORDER BY fecha_emision DESC LIMIT 50";

try {
    $stmt = $db->query($sql);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $registros = [];
    $error_critico = "Error técnico: " . $e->getMessage();
}
?>

<style>
    :root { --alt-vinotinto: #632626; }
    body { background-color: #fff; font-family: 'Segoe UI', sans-serif; }
    .main-content { margin-left: 280px; padding: 20px 40px; }

    /* ===== HEADER PROFESIONAL ALT ===== */
    .alt-page-header {
        background: linear-gradient(135deg, #632626 0%, #8b3a3a 50%, #4a1a1a 100%);
        border-radius: 12px;
        padding: 22px 28px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 15px rgba(99, 38, 38, 0.25);
        position: relative;
        overflow: hidden;
    }
    .alt-page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
        pointer-events: none;
    }
    .alt-header-content {
        display: flex;
        align-items: center;
        gap: 16px;
        z-index: 1;
    }
    .alt-header-icon {
        width: 48px;
        height: 48px;
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255,255,255,0.1);
    }
    .alt-header-title {
        font-size: 20px;
        font-weight: 800;
        color: #fff;
        margin: 0;
        letter-spacing: -0.3px;
    }
    .alt-header-subtitle {
        font-size: 12px;
        color: rgba(255,255,255,0.7);
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    .alt-header-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 1;
    }
    .alt-header-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.9);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255,255,255,0.08);
    }

    /* Estilo de Filtros */
    .filter-section { background-color: #f8f9fa; border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin-bottom: 25px; }
    .label-min { font-size: 11px; font-weight: 800; color: #666; text-transform: uppercase; margin-bottom: 5px; }
    .btn-add { background-color: var(--alt-vinotinto); color: white; border: none; font-weight: bold; padding: 8px 20px; border-radius: 4px; }

    /* Tabla Estilo Hoja Contable Real */
    .tabla-alt { width: 100%; border-collapse: collapse; font-size: 13px; }
    .tabla-alt thead th { background-color: #f2f2f2; color: #000; padding: 12px 10px; border-bottom: 2px solid #333; text-align: left; }
    .tabla-alt tbody tr { border-bottom: 1px solid #eee; }
    .tabla-alt tbody tr:hover { background-color: #fcfcfc; }
    
    .modal-xl-custom { max-width: 98%; }
    .doc-viewer { height: 78vh; border-radius: 12px; border: 2px solid #eee; background: #fff; }
    .doc-label { font-size: 11px; font-weight: 800; color: var(--alt-vinotinto); text-transform: uppercase; margin-bottom: 8px; display: block; }
    .doc-header { background: #f8f9fa; padding: 10px; border-radius: 10px 10px 0 0; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 8px; }

    .nit-font { font-family: monospace; font-weight: bold; font-size: 14px; color: #444; }
    .doc-ref { font-weight: bold; color: var(--alt-vinotinto); font-size: 12px; }
    
    .badge-tipo {
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 900;
        margin-right: 5px;
    }
    .tp-compra { background: #eee; color: #333; }
    .tp-venta { background: var(--alt-vinotinto); color: #fff; }

    .badge-status { 
        font-size: 10px; font-weight: bold; padding: 3px 8px; border-radius: 3px; 
        text-transform: uppercase; border: 1px solid; display: inline-block;
    }
    .st-pagada { color: #198754; border-color: #198754; background: #eefaf3; }
    .st-pendiente { color: #ffc107; border-color: #ffc107; background: #fffdf5; }
</style>

<main class="main-content">
    <div class="alt-page-header">
        <div class="alt-header-content">
            <div class="alt-header-icon"><i data-lucide="layout-dashboard" size="24"></i></div>
            <div>
                <h4 class="alt-header-title">Gestión de Documentos</h4>
                <span class="alt-header-subtitle">ALT-CONFECCIONES · Panel General</span>
            </div>
        </div>
        <div class="alt-header-meta">
            <span class="alt-header-badge"><i data-lucide="calendar" size="12"></i> <?= date('d/m/Y') ?></span>
            <span class="alt-header-badge"><i data-lucide="user" size="12"></i> <?= strtoupper($_SESSION['username'] ?? 'USUARIO') ?></span>
        </div>
    </div>

    <div class="filter-section shadow-sm">
        <div class="row align-items-end g-3">
            <div class="col-md-3">
                <div class="label-min">Búsqueda Rápida (NIT / Nombre)</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i data-lucide="search" size="14"></i></span>
                    <input type="text" id="inputBusqueda" class="form-control" placeholder="Escriba para filtrar..." onkeyup="ejecutarFiltro()">
                </div>
            </div>
            <div class="col-md-3">
                <div class="label-min">Filtrar por Mes</div>
                <select id="selectMes" class="form-select border-0 shadow-sm" onchange="ejecutarFiltro()">
                    <option value="todos">📅 Todos los meses</option>
                    <?php 
                    $meses = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                    $mesActual = (int)date('m');
                    foreach($meses as $num => $nombre): ?>
                        <option value="<?= $num ?>" <?= ($num == $mesActual) ? 'selected' : '' ?>>
                            <?= $nombre ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <div class="label-min">Tipo</div>
                <select id="selectTipo" class="form-select border-0 shadow-sm" onchange="ejecutarFiltro()">
                    <option value="todos">📂 Ver Todo</option>
                    <option value="COMPRA">🧾 Compras</option>
                    <option value="VENTA">📈 Ventas</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="label-min">Estado Actual</div>
                <select id="selectEstado" class="form-select form-select-sm" onchange="ejecutarFiltro()">
                    <option value="todos">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="pagada">Pagada</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <a href="cargar_factura.php" class="btn btn-add w-100">
                    <i data-lucide="plus" size="16"></i> NUEVO
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="tabla-alt" id="tablaMaster">
            <thead>
                <tr>
                    <th style="width: 160px;">CC / NIT</th>
                    <th>PROVEEDOR / CLIENTE</th>
                    <th style="width: 120px;">FECHA</th>
                    <th style="width: 150px;">DOCUMENTO</th>
                    <th class="text-end">ESTADO / ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): 
                    $esC = ($r['tipo'] === 'COMPRA');
                    $parts = explode('FACT_', $r['ruta_carpeta']);
                    $folio = isset($parts[1]) ? rtrim($parts[1], DIRECTORY_SEPARATOR . '/') : $r['id'];
                    
                    $nit = $r['nit_cedula'] ?? '';
                    $nombre = $r['nombre'] ?? 'SIN NOMBRE';
                ?>
                <tr class="row-factura" 
                    data-tipo="<?= $r['tipo'] ?>" 
                    data-estado="<?= $r['estado'] ?>"
                    data-mes="<?= (int)date('m', strtotime($r['fecha_emision'])) ?>"
                    data-search="<?= htmlspecialchars(strtolower($nit . ' ' . $nombre . ' ' . ($esC ? 'FC' : 'FV') . '-' . $folio)) ?>">
                    <td class="nit-font"><?= htmlspecialchars($nit) ?: '---' ?></td>
                    <td class="text-uppercase">
                        <span class="badge-tipo <?= $esC ? 'tp-compra' : 'tp-venta' ?>"><?= $r['tipo'] ?></span>
                        <strong style="font-size: 13px;"><?= htmlspecialchars($nombre) ?></strong>
                    </td>
                    <td class="fw-bold text-muted"><?= ($r['fecha_emision']) ? date('d/m/Y', strtotime($r['fecha_emision'])) : '---' ?></td>
                    <td class="doc-ref"><?= $esC ? 'FC' : 'FV' ?>-<?= $folio ?></td>
                    <td class="text-end">
                        <span class="badge-status <?= $r['estado'] === 'pagada' ? 'st-pagada' : 'st-pendiente' ?>">
                            <?= $r['estado'] ?>
                        </span>
                        
                        <?php 
                        $urlF = "abrir_archivo.php?file=" . urlencode(($r['ruta_carpeta'] ?? '') . ($r['archivo_path'] ?? ''));
                        $urlS = ($r['soporte_pago_path'] ?? '') ? "abrir_archivo.php?file=" . urlencode($r['ruta_carpeta'] . $r['soporte_pago_path']) : "";
                        $urlE = ($r['egreso_path'] ?? '') ? "abrir_archivo.php?file=" . urlencode($r['ruta_carpeta'] . $r['egreso_path']) : "";
                        
                        // Etiquetas dinámicas según el tipo de documento
                        $labelSoporte = "Soporte de Banco";
                        $labelEgreso = ($esC) ? "Comprobante Egreso" : "Comprobante Ingreso";
                        ?>
                        
                        <button type="button" class="btn btn-link p-0 ms-2 text-dark" 
                                onclick="abrirVisorProfesional('<?= addslashes($urlF) ?>', '<?= addslashes($urlS) ?>', '<?= addslashes($urlE) ?>', '<?= $labelSoporte ?>', '<?= $labelEgreso ?>')" 
                                title="Ver Soportes Completos">
                            <i data-lucide="layout-grid" size="16"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Visor Profesional -->
<div class="modal fade" id="modalVisor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-xl-custom modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4" style="background-color: #fcfcfc;">
            <div class="modal-header border-0 pb-0 p-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark"><i data-lucide="files" class="me-2"></i>Visor de Documentos ALT</h5>
                    <p class="text-muted small mb-0">Visualización de factura y soportes de pago</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="doc-label">FACTURA ORIGINAL</span>
                        <iframe id="iframeFactura" class="doc-viewer w-100"></iframe>
                    </div>
                    <div class="col-md-4" id="colSoporte">
                        <span class="doc-label">SOPORTE DE PAGO</span>
                        <iframe id="iframeSoporte" class="doc-viewer w-100"></iframe>
                    </div>
                    <div class="col-md-4" id="colEgreso">
                        <span class="doc-label">COMPROBANTE EGRESO</span>
                        <iframe id="iframeEgreso" class="doc-viewer w-100"></iframe>
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

    function abrirVisorProfesional(f, s, e, labelS, labelE) {
        document.getElementById('iframeFactura').src = f;
        
        const colS = document.getElementById('colSoporte');
        const spanS = colS.querySelector('.doc-label');
        if(s) { 
            colS.classList.remove('d-none'); 
            document.getElementById('iframeSoporte').src = s; 
            spanS.innerHTML = labelS.toUpperCase();
        } else { 
            colS.classList.add('d-none'); 
        }

        const colE = document.getElementById('colEgreso');
        const spanE = colE.querySelector('.doc-label');
        if(e) { 
            colE.classList.remove('d-none'); 
            document.getElementById('iframeEgreso').src = e; 
            spanE.innerHTML = labelE.toUpperCase();
        } else { 
            colE.classList.add('d-none'); 
        }

        visorModal.show();
    }

    document.getElementById('modalVisor').addEventListener('hidden.bs.modal', function () {
        ['iframeFactura', 'iframeSoporte', 'iframeEgreso'].forEach(id => document.getElementById(id).src = '');
    });

    function ejecutarFiltro() {
        const searchInput = document.getElementById('inputBusqueda');
        const search = searchInput ? searchInput.value.toLowerCase() : '';
        
        const selectTipo = document.getElementById('selectTipo');
        const tipo = selectTipo ? selectTipo.value : 'todos';
        
        const selectEstado = document.getElementById('selectEstado');
        const estado = selectEstado ? selectEstado.value : 'todos';

        const selectMes = document.getElementById('selectMes');
        const mes = selectMes ? selectMes.value : 'todos';
        
        const rows = document.querySelectorAll('.row-factura');

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search') || '';
            const rowTipo = row.getAttribute('data-tipo') || '';
            const rowEstado = row.getAttribute('data-estado') || '';
            const rowMes = row.getAttribute('data-mes') || '';
            
            const matchSearch = rowSearch.includes(search);
            const matchTipo = (tipo === 'todos' || rowTipo === tipo);
            const matchEstado = (estado === 'todos' || rowEstado === estado);
            const matchMes = (mes === 'todos' || rowMes === mes);

            if (matchSearch && matchTipo && matchEstado && matchMes) {
                row.style.setProperty('display', '', 'important');
            } else {
                row.style.setProperty('display', 'none', 'important');
            }
        });
    }

    // Asegurar que el evento esté vinculado
    document.addEventListener('DOMContentLoaded', function() {
        const inputBusqueda = document.getElementById('inputBusqueda');
        if (inputBusqueda) {
            inputBusqueda.addEventListener('input', ejecutarFiltro);
            inputBusqueda.addEventListener('keyup', ejecutarFiltro);
        }
    });
</script>
