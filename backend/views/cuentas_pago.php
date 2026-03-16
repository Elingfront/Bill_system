<?php
///////////////////////////////
////////FACTURAS_COMPRA////////
///////////////////////////////
include __DIR__ . '/auth/header.php';
include __DIR__ . '/auth/sidebar.php';
require_once __DIR__ . '/../app/Core/Database.php'; 
use App\Core\Database;

$db = Database::getConnection();

// Traemos solo las COMPRAS que están PENDIENTES
$sql = "SELECT id, fecha_emision, archivo_path, ruta_carpeta 
        FROM facturas_compra 
        WHERE estado = 'pendiente' 
        ORDER BY id DESC";

$cuentas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Color guinda/vinotinto para Compras
$colorCompra = '#632626'; 
?>

<style>
    .action-btn-pago {
        background: linear-gradient(135deg, <?= $colorCompra ?> 0%, #8b2e2e 100%);
        border: none;
        transition: all 0.3s ease;
    }
    .action-btn-pago:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(99, 38, 38, 0.4) !important;
        filter: brightness(1.1);
    }
</style>

<main class="main-content p-4" style="margin-left: 280px; margin-top: 20px;">
    <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded shadow-sm border-bottom border-4" style="border-color: <?= $colorCompra ?> !important;">
            <div>
                <h2 class="fw-bold mb-0" style="color: <?= $colorCompra ?>;">Cuentas de Pago</h2>
                <p class="text-muted mb-0 small">Listado de facturas de compra pendientes.</p>
            </div>
            
            <button class="btn btn-lg text-white fw-bold shadow action-btn-pago d-flex align-items-center px-4 py-2" 
                    data-bs-toggle="modal" data-bs-target="#modalPagarCuentas">
                <i data-lucide="wallet" class="me-2" style="width: 20px;"></i> 
                PAGAR CUENTAS
            </button>
        </div>

        <div class="row">
            <?php if (empty($cuentas)): ?>
                <div class="col-12 text-center py-5">
                    <i data-lucide="badge-check" size="48" class="text-success mb-3"></i>
                    <p class="text-muted fs-5">No tienes facturas pendientes de pago. </p>
                </div>
            <?php else: ?>
                <?php foreach ($cuentas as $c): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 15px;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-muted small">ALT-COMPRA-<?= str_pad($c['id'], 3, "0", STR_PAD_LEFT) ?></span>
                                    <span class="badge bg-dark text-white px-3 py-2 rounded-pill small fw-bold">PENDIENTE</span>
                                </div>
                                <div class="d-flex align-items-center text-secondary small mb-3">
                                    <i data-lucide="calendar" size="14" class="me-2"></i> Emisión: <?= $c['fecha_emision'] ?>
                                </div>
                                <a href="abrir_archivo.php?file=<?= urlencode($c['ruta_carpeta'] . $c['archivo_path']) ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-dark w-100 rounded-pill fw-bold">
                                    <i data-lucide="eye" size="14" class="me-1"></i> Ver Factura
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<div class="modal fade" id="modalPagarCuentas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold" style="color: <?= $colorCompra ?>;">Formalizar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesar_pago.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tipo_proceso" value="compra"> 
                
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Seleccione Factura de Compra</label>
                        <select name="factura_id" class="form-select border-2" required>
                            <option value="" disabled selected>Seleccione folio...</option>
                            <?php foreach ($cuentas as $c): ?>
                                <option value="<?= $c['id'] ?>">ALT-COMPRA-<?= str_pad($c['id'], 3, "0", STR_PAD_LEFT) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="p-3 rounded-4" style="background-color: #f8f0f0; border: 1px dashed <?= $colorCompra ?>;">
                        <div class="mb-3">
                            <label class="small fw-bold text-dark">Soporte de Transferencia </label>
                            <input type="file" name="soporte_pago" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-0">
                            <label class="small fw-bold text-dark">Comprobante Egreso</label>
                            <input type="file" name="egreso" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn text-white w-100 rounded-pill fw-bold action-btn-pago">Confirmar Pago</button>
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