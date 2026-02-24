<?php
// 1. Incluimos los archivos de autenticación y diseño
include __DIR__ . '/auth/header.php';
include __DIR__ . '/auth/sidebar.php';

// 2. Conexión a la base de datos
require_once __DIR__ . '/../app/Core/Database.php'; 
use App\Core\Database;

$db = Database::getConnection();

// 3. Lógica de filtrado
$filtro = isset($_GET['modulo']) ? $_GET['modulo'] : 'todos';

// Traemos todos los campos necesarios para los 3 archivos
$queryCompra = "SELECT id, entidad_id, fecha_emision, 'compra' as tipo, archivo_path, soporte_pago_path, egreso_path, ruta_carpeta, estado FROM facturas_compra WHERE estado = 'pagada'";
$queryVenta = "SELECT id, entidad_id, fecha_emision, 'venta' as tipo, archivo_path, soporte_pago_path, egreso_path, ruta_carpeta, estado FROM facturas_venta WHERE estado = 'pagada'";

if ($filtro === 'compra') { 
    $sql = $queryCompra . " ORDER BY id DESC"; 
} elseif ($filtro === 'venta') { 
    $sql = $queryVenta . " ORDER BY id DESC"; 
} else { 
    $sql = "($queryCompra) UNION ($queryVenta) ORDER BY id DESC"; 
}

try {
    $facturas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error en la consulta: " . $e->getMessage();
    $facturas = [];
}
?>

<style>
    .btn-folder {
        background-color: white;
        transition: all 0.3s ease;
    }
    .btn-folder:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .doc-link {
        transition: all 0.2s ease;
        border: 1px solid #eee !important;
    }
    .doc-link:hover {
        background-color: #f8f9fa !important;
        padding-left: 15px !important;
    }
    .card {
        border-radius: 12px;
        overflow: hidden;
    }
</style>

<main class="main-content p-4" style="margin-left: 280px; margin-top: 20px;">
    <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h2 class="fw-bold mb-0" style="color: #632626;">Facturación Pagada</h2>
            <form method="GET" action="facturas_pagadas.php">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i data-lucide="filter" size="18"></i>
                    </span>
                    <select name="modulo" class="form-select border-start-0 shadow-none" onchange="this.form.submit()" style="width: 200px; cursor: pointer;">
                        <option value="todos" <?= $filtro == 'todos' ? 'selected' : '' ?>>Gestión de Folios</option>
                        <option value="compra" <?= $filtro == 'compra' ? 'selected' : '' ?>>Gestión de Compras</option>
                        <option value="venta" <?= $filtro == 'venta' ? 'selected' : '' ?>>Gestión de Ventas</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="row">
            <?php if (empty($facturas)): ?>
                <div class="col-12 text-center py-5">
                    <i data-lucide="search-x" size="48" class="text-muted mb-3"></i>
                    <p class="text-muted fs-5">No se encontraron facturas pagadas.</p>
                </div>
            <?php else: ?>
                <?php foreach ($facturas as $f): 
                    $esCompra = ($f['tipo'] === 'compra');
                    $colorHex = $esCompra ? '#632626' : '#2c3e50'; 
                    $bgLigero = $esCompra ? 'rgba(99, 38, 38, 0.03)' : 'rgba(44, 62, 80, 0.03)';
                    // ID único para el colapso (Evita que se abran todas a la vez)
                    $collapseId = "docs_" . $f['tipo'] . "_" . $f['id'];
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0" 
                         style="border-top: 5px solid <?= $colorHex ?> !important; background-color: <?= $bgLigero ?>;">
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge px-3 py-2" style="background-color: <?= $colorHex ?>;">
                                    <i data-lucide="<?= $esCompra ? 'shopping-cart' : 'trending-up' ?>" class="me-1" style="width: 14px;"></i>
                                    <?= strtoupper($f['tipo']) ?>
                                </span>
                                <span class="text-muted small fw-bold">
                                    <i data-lucide="calendar" size="14" class="me-1"></i><?= $f['fecha_emision'] ?>
                                </span>
                            </div>
                            
                            <h5 class="fw-bold mb-1" style="color: <?= $colorHex ?>;">
                                ALT-<?= strtoupper($f['tipo']) ?>-F.(<?= str_pad($f['id'], 3, "0", STR_PAD_LEFT) ?>)
                            </h5>
                            
                            <div class="bg-white p-2 rounded border mb-3 mt-2">
                                <p class="small text-secondary mb-0 text-truncate" title="<?= $f['ruta_carpeta'] ?>">
                                    <i data-lucide="folder" size="14" class="me-1"></i><?= $f['ruta_carpeta'] ?>
                                </p>
                            </div>

                            <button class="btn btn-sm w-100 d-flex align-items-center justify-content-center fw-bold btn-folder"
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#<?= $collapseId ?>" 
                                    style="border: 2px solid <?= $colorHex ?>; color: <?= $colorHex ?>;">
                                <i data-lucide="more-horizontal" size="16" class="me-2"></i> Ver Documentos
                            </button>

                            <div class="collapse mt-3" id="<?= $collapseId ?>">
                                <div class="d-flex flex-column gap-2 p-2 bg-white rounded border shadow-sm">
                                    <?php 
                                    $docs = [
                                        ['p' => $f['archivo_path'], 'l' => 'Factura PDF', 'i' => 'file-text'],
                                        ['p' => $f['soporte_pago_path'], 'l' => 'Soporte Pago', 'i' => 'credit-card'],
                                        ['p' => $f['egreso_path'], 'l' => ($esCompra ? 'Comprobante Egreso' : 'Recibo Caja'), 'i' => 'check-square']
                                    ];

                                    foreach ($docs as $d): 
                                        if (!empty($d['p'])): 
                                            $fullPath = $f['ruta_carpeta'] . $d['p'];
                                    ?>
                                        <a href="abrir_archivo.php?file=<?= urlencode($fullPath) ?>" 
                                           target="_blank" 
                                           class="btn btn-sm d-flex align-items-center justify-content-between px-3 py-2 doc-link"
                                           style="color: #444; text-decoration: none; border-radius: 8px;">
                                            <span class="small fw-bold">
                                                <i data-lucide="<?= $d['i'] ?>" size="15" class="me-2 text-muted"></i> <?= $d['l'] ?>
                                            </span>
                                            <i data-lucide="external-link" size="12" class="text-muted"></i>
                                        </a>
                                    <?php endif; endforeach; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Inicializar iconos de Lucide
    lucide.createIcons();
</script>