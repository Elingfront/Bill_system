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
require_once __DIR__ . '/../app/Core/Database.php'; 
use App\Core\Database;

$db = Database::getConnection();

// Traemos solo las COMPRAS que están PENDIENTES
$sql = "SELECT f.id, f.fecha_emision, f.archivo_path, f.ruta_carpeta, e.nit_cedula, e.nombre 
        FROM facturas_compra f
        LEFT JOIN entidades e ON f.entidad_id = e.id
        WHERE f.estado = 'pendiente' 
        ORDER BY f.id DESC";

$cuentas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Color guinda/vinotinto para Compras
$colorCompra = '#632626'; 
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

    /* Estilo de Filtros */
    .filter-section { background-color: #f8f9fa; border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin-bottom: 25px; }
    .label-min { font-size: 11px; font-weight: 800; color: #666; text-transform: uppercase; margin-bottom: 5px; }

    /* Tabla Estilo Hoja Contable Real */
    .tabla-alt { width: 100%; border-collapse: collapse; font-size: 13px; }
    .tabla-alt thead th { background-color: #f2f2f2; color: #000; padding: 12px 10px; border-bottom: 2px solid #333; text-align: left; }
    .tabla-alt tbody tr { border-bottom: 1px solid #eee; }
    .tabla-alt tbody tr:hover { background-color: #fcfcfc; }
    .tabla-alt td { padding: 12px 10px; color: #333; vertical-align: middle; }

    .nit-font { font-family: monospace; font-weight: bold; font-size: 14px; color: #444; }
    .doc-ref { font-weight: bold; color: var(--alt-vinotinto); font-size: 12px; }

    .btn-vinotinto-action {
        background: var(--alt-vinotinto);
        color: white; border: none; padding: 5px 15px;
        border-radius: 4px; font-weight: bold; font-size: 11px;
        text-transform: uppercase; transition: 0.3s;
    }
    .btn-vinotinto-action:hover { background: #4a1a1a; color: white; transform: scale(1.05); }
</style>

<main class="main-content">
    <div class="alt-page-header">
        <div class="alt-header-content">
            <div class="alt-header-icon"><i data-lucide="credit-card" size="24"></i></div>
            <div>
                <h4 class="alt-header-title">Cuentas de Pago</h4>
                <span class="alt-header-subtitle">ALT-CONFECCIONES · Facturas Pendientes de Pago</span>
            </div>
        </div>
        <div class="alt-header-meta">
            <span class="alt-header-badge"><i data-lucide="calendar" size="12"></i> <?= date('d/m/Y') ?></span>
            <span class="alt-header-badge"><i data-lucide="user" size="12"></i> <?= strtoupper($_SESSION['username'] ?? 'USUARIO') ?></span>
        </div>
    </div>

    <div class="filter-section shadow-sm">
        <div class="row align-items-end g-3">
            <div class="col-md-9">
                <div class="label-min">Búsqueda Rápida (NIT / Proveedor / Factura)</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i data-lucide="search" size="14"></i></span>
                    <input type="text" id="inputBusqueda" class="form-control" placeholder="Escriba para filtrar..." onkeyup="ejecutarFiltro()">
                </div>
            </div>
            <div class="col-md-3">
                <button class="btn btn-dark btn-sm w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#modalPagarCuentas">
                    <i data-lucide="wallet" size="14"></i> FORMALIZAR PAGO
                </button>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="tabla-alt" id="tablaCuentasPago">
            <thead>
                <tr>
                    <th style="width: 160px;">CC / NIT</th>
                    <th>PROVEEDOR</th>
                    <th style="width: 120px;">FECHA EMISIÓN</th>
                    <th style="width: 150px;">DOCUMENTO</th>
                    <th class="text-end">ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cuentas as $c): 
                    $parts = explode('FACT_', $c['ruta_carpeta']);
                    $folio = isset($parts[1]) ? rtrim($parts[1], DIRECTORY_SEPARATOR . '/') : $c['id'];
                    $nit = $c['nit_cedula'] ?? '';
                    $nombre = $c['nombre'] ?? 'SIN NOMBRE';
                ?>
                <tr class="row-cuenta" data-search="<?= htmlspecialchars(strtolower($nit . ' ' . $nombre . ' fact-' . $folio)) ?>">
                    <td class="nit-font"><?= htmlspecialchars($nit) ?: '---' ?></td>
                    <td class="text-uppercase">
                        <strong style="font-size: 13px;"><?= htmlspecialchars($nombre) ?></strong>
                    </td>
                    <td class="fw-bold text-muted"><?= date('d/m/Y', strtotime($c['fecha_emision'])) ?></td>
                    <td class="doc-ref">FACT-<?= $folio ?></td>
                    <td class="text-end">
                        <button class="btn btn-vinotinto-action" onclick="abrirModalPago(<?= $c['id'] ?>, 'FACT-<?= $folio ?>')">
                            PAGAR AHORA
                        </button>
                        <a href="abrir_archivo.php?file=<?= urlencode($c['ruta_carpeta'] . $c['archivo_path']) ?>" 
                           target="_blank" class="btn btn-link p-0 ms-2 text-dark" title="Ver Factura">
                            <i data-lucide="eye" size="16"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($cuentas)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No hay facturas pendientes de pago.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Formalizar Pago -->
<div class="modal fade" id="modalPagarCuentas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0 p-4">
                <h5 class="fw-bold text-dark">Formalizar Pago de Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesar_pago.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tipo_proceso" value="compra"> 
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="label-min">Seleccione Factura de Compra</label>
                        <select name="factura_id" id="selectFacturaModal" class="form-select border-2" required>
                            <option value="" disabled selected>Seleccione folio...</option>
                            <?php foreach ($cuentas as $c): 
                                $parts = explode('FACT_', $c['ruta_carpeta']);
                                $folio = isset($parts[1]) ? rtrim($parts[1], DIRECTORY_SEPARATOR . '/') : $c['id'];
                            ?>
                                <option value="<?= $c['id'] ?>">FACT-<?= $folio ?> - <?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="p-3 rounded-3" style="background-color: #f8f9fa; border: 1px dashed #ddd;">
                        <div class="mb-3">
                            <label class="label-min text-dark">Soporte de Transferencia (Banco)</label>
                            <input type="file" name="soporte_pago" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-0">
                            <label class="label-min text-dark">Comprobante de Egreso (Contable)</label>
                            <input type="file" name="egreso" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-dark w-100 fw-bold py-2">REGISTRAR PAGO AHORA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    lucide.createIcons();

    const modalPago = new bootstrap.Modal(document.getElementById('modalPagarCuentas'));

    function abrirModalPago(id, folio) {
        const select = document.getElementById('selectFacturaModal');
        select.value = id;
        modalPago.show();
    }

    function ejecutarFiltro() {
        const searchInput = document.getElementById('inputBusqueda');
        const search = searchInput ? searchInput.value.toLowerCase() : '';
        const rows = document.querySelectorAll('.row-cuenta');

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search') || '';
            row.style.display = rowSearch.includes(search) ? '' : 'none';
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        Swal.fire({
            title: "¡Pago Registrado!",
            text: "La factura se ha marcado como pagada correctamente.",
            icon: "success",
            confirmButtonColor: "<?= $colorCompra ?>",
            confirmButtonText: "¡Excelente!"
        });
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
