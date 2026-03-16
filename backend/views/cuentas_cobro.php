
<?php
///////////////////////////////
////////FACTURAS_VENTA/////////
///////////////////////////////
include __DIR__ . '/auth/header.php';
include __DIR__ . '/auth/sidebar.php';
require_once __DIR__ . '/../app/Core/Database.php'; 
use App\Core\Database;

$db = Database::getConnection();

// Traemos solo las VENTAS que están PENDIENTES
$sql = "SELECT id, entidad_id, fecha_emision, archivo_path, ruta_carpeta 
        FROM facturas_venta 
        WHERE estado = 'pendiente' 
        ORDER BY id DESC";

$cuentas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$colorVenta = '#2c3e50'; 


?>

<style>
    .action-btn-cobro {
        background: linear-gradient(135deg, #2c3e50 0%, #455a64 100%);
        border: none;
        transition: all 0.3s ease;
    }
    .action-btn-cobro:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(44, 62, 80, 0.4) !important;
        filter: brightness(1.1);
    }
</style>

<main class="main-content p-4" style="margin-left: 280px; margin-top: 20px;">
    <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded shadow-sm border-bottom border-4" style="border-color: <?= $colorVenta ?> !important;">
            <div>
                <h2 class="fw-bold mb-0" style="color: <?= $colorVenta ?>;">Cuentas de Cobro</h2>
                <p class="text-muted mb-0 small">Facturas emitidas pendientes por recibir pago.</p>
            </div>
            
            <button class="btn btn-lg text-white fw-bold shadow action-btn-cobro d-flex align-items-center px-4 py-2" 
                    data-bs-toggle="modal" data-bs-target="#modalCobrarCuentas">
                <i data-lucide="hand-coins" class="me-2" style="width: 20px;"></i> 
                REGISTRAR COBRO
            </button>
        </div>

        <div class="row">
            <?php if (empty($cuentas)): ?>
                <div class="col-12 text-center py-5">
                    <i data-lucide="badge-check" size="48" class="text-success mb-3"></i>
                    <p class="text-muted fs-5">Todos tus clientes están al día.</p>
                </div>
            <?php else: ?>
                <?php foreach ($cuentas as $c): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 15px;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-muted small">ALT-VENTA-<?= str_pad($c['id'], 3, "0", STR_PAD_LEFT) ?></span>
                                    <span class="badge bg-dark text-white px-3 py-2 rounded-pill small fw-bold">POR COBRAR</span>
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

<div class="modal fade" id="modalCobrarCuentas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold" style="color: <?= $colorVenta ?>;">Formalizar Ingreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesar_pago.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tipo_proceso" value="venta"> <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Seleccione Factura del Cliente</label>
                        <select name="factura_id" class="form-select border-2" required>
                            <option value="" disabled selected>Seleccione folio...</option>
                            <?php foreach ($cuentas as $c): ?>
                                <option value="<?= $c['id'] ?>">ALT-VENTA-<?= str_pad($c['id'], 3, "0", STR_PAD_LEFT) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="p-3 rounded-4" style="background-color: #f0f4f8; border: 1px dashed <?= $colorVenta ?>;">
                        <div class="mb-3">
                            <label class="small fw-bold text-dark">Soporte de Transferencia / Recibo</label>
                            <input type="file" name="soporte_pago" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-0">
                            <label class="small fw-bold text-dark">Recibo de Caja</label>
                            <input type="file" name="egreso" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn text-white w-100 rounded-pill fw-bold action-btn-cobro">Confirmar Cobro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Inicializar iconos
    lucide.createIcons();

    // Lógica de la animación de éxito
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        Swal.fire({
            title: "¡Cobro Registrado!",
            text: "Se ha movido a la sección de facturación pagada.",
            icon: "success",
            confirmButtonColor: "<?= $colorVenta ?>",
            confirmButtonText: "¡OK!"
        });
        // Limpia la URL para que no repita la animación al recargar
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    header("Location: facturas_venta.php?success=1"); 
exit();
</script>