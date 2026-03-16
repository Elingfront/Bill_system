<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

include __DIR__ . '/auth/header.php';
include __DIR__ . '/auth/sidebar.php';

require_once 'C:/wamp/www/sistema_facturas/backend/app/Core/Database.php';
use App\Core\Database;

$db = Database::getConnection();
$sql = "SELECT id, fecha_emision, archivo_path, soporte_pago_path, egreso_path, ruta_carpeta, estado FROM facturas_compra ORDER BY id DESC";
$facturas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content p-4" style="margin-left: 280px; background-color: #f8f9fa;">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold text-dark mb-1">Gestión de Compras</h2>
                <p class="text-muted small mb-0">Listado de facturas de compra y soportes almacenados.</p>
            </div>
            <div class="d-flex gap-2">
                <select id="filtro" class="form-select border-0 shadow-sm" style="width: 180px;" onchange="filtrar()">
                    <option value="todos">Todos los estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="pagada">Pagadas</option>
                </select>
                <a href="cuentas_pago.php" class="btn btn-dark shadow-sm px-4 fw-bold">PAGAR CUENTAS</a>
            </div>
        </div>

        <div class="row g-4" id="gridCompras">
            <?php foreach ($facturas as $f): 
                $esPagada = ($f['estado'] === 'pagada');
                $partes = explode('FACT_', $f['ruta_carpeta']);
                $numReal = isset($partes[1]) ? rtrim($partes[1], DIRECTORY_SEPARATOR . '/') : $f['id'];
            ?>
            <div class="col-md-4 card-v" data-estado="<?= $f['estado'] ?>">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge rounded-pill px-3 py-2 <?= $esPagada ? 'bg-dark text-white' : 'bg-light text-dark border' ?>">
                                <?= strtoupper($f['estado']) ?>
                            </span>
                            <span class="text-muted small fw-medium"><?= $f['fecha_emision'] ?></span>
                        </div>

                        <h5 class="fw-bold mb-4 text-dark">ALT-COMPRA-<?= $numReal ?></h5>
                        
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="file-text" class="text-muted me-2" size="18"></i>
                                <a href="abrir_archivo.php?file=<?= urlencode($f['ruta_carpeta'].$f['archivo_path']) ?>" target="_blank" class="text-decoration-none text-dark small fw-bold">Ver Factura </a>
                            </div>
                            
                            <?php if($f['soporte_pago_path']): ?>
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="image" class="text-muted me-2" size="18"></i>
                                <a href="abrir_archivo.php?file=<?= urlencode($f['ruta_carpeta'].$f['soporte_pago_path']) ?>" target="_blank" class="text-decoration-none text-dark small fw-bold">Ver Soporte de Pago</a>
                            </div>
                            <?php endif; ?>

                            <?php if($f['egreso_path']): ?>
                            <div class="d-flex align-items-center">
                                <i data-lucide="check-square" class="text-muted me-2" size="18"></i>
                                <a href="abrir_archivo.php?file=<?= urlencode($f['ruta_carpeta'].$f['egreso_path']) ?>" target="_blank" class="text-decoration-none text-dark small fw-bold">Ver Comprobante Egreso</a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid mt-auto">
                            <?php if(!$esPagada): ?>
                                <a href="cuentas_pago.php?id=<?= $f['id'] ?>" class="btn btn-dark rounded-pill fw-bold py-2">
                                    <i data-lucide="dollar-sign" size="16" class="me-2"></i> Procesar Pago
                                </a>
                            <?php else: ?>
                                <div class="text-center p-2 bg-light rounded-pill">
                                    <span class="text-muted small fw-bold"><i data-lucide="check-circle" size="14" class="me-1"></i>HAZ CLICK PARA VISUALIZAR</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();

    function filtrar() {
        const val = document.getElementById('filtro').value;
        document.querySelectorAll('.card-v').forEach(c => {
            c.style.display = (val === 'todos' || c.dataset.estado === val) ? 'block' : 'none';
        });
    }
</script>