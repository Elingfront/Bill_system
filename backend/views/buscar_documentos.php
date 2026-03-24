<?php
// Asegúrate de que las rutas de los require sean las correctas según tu carpeta
require_once dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/app/Controllers/DashboardController.php';

use App\Controllers\DashboardController;

// Recibimos los datos del JS (usando GET por simplicidad)
$nit  = $_GET['nit_cedula'] ?? '';
$mes  = $_GET['mes'] ?? '';
$tipo = $_GET['tipo'] ?? '';

$resultados = DashboardController::filtrarDocumentos($nit_cedula, $mes, $tipo);

if ($resultados) {
    foreach ($resultados as $res) {
        // Formateamos el ID para que se vea pro
        $id = str_pad($res['id'], 3, "0", STR_PAD_LEFT);
        $mesNombre = date('M', strtotime($res['fecha_emision']));
        ?>
        <div class="row g-0 p-3 border-bottom align-items-center doc-row bg-white">
            <div class="col-md-1 fw-bold">#<?= $id ?></div>
            <div class="col-md-4">
                <span class="d-block fw-bold text-uppercase"><?= $res['nombre'] ?></span>
                <small class="text-muted">NIT: <?= $res['nit_cedula'] ?></small>
            </div>
            <div class="col-md-1 text-center">
                <span class="badge bg-light text-dark border"><?= strtoupper($mesNombre) ?></span>
            </div>
            <div class="col-md-6 d-flex gap-2 justify-content-center">
                <?php if (!empty($res['archivo_path'])): ?>
                    <a href="<?= $res['archivo_path'] ?>" target="_blank" class="btn btn-sm btn-success fw-bold">FACTURA</a>
                <?php endif; ?>

                <?php if (!empty($res['soporte_pago_path'])): ?>
                    <a href="<?= $res['soporte_pago_path'] ?>" target="_blank" class="btn btn-sm btn-primary fw-bold">SOPORTE</a>
                <?php endif; ?>

                <?php if (!empty($res['egreso_path'])): ?>
                    <a href="<?= $res['egreso_path'] ?>" target="_blank" class="btn btn-sm btn-danger fw-bold">EGRESO</a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
} else {
    echo '<div class="p-5 text-center text-muted">
            <i data-lucide="search-x" size="40"></i>
            <p class="mt-2">No se encontró ni una monda con ese NIT o filtro.</p>
          </div>';
}