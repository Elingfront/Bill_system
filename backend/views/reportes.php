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
$anioActual = date('Y');

// 1. Estadísticas Generales (Conteo del Año Actual)
$kpis = [
    'compras_total' => $db->query("SELECT COUNT(*) FROM facturas_compra WHERE YEAR(fecha_emision) = $anioActual")->fetchColumn(),
    'ventas_total' => $db->query("SELECT COUNT(*) FROM facturas_venta WHERE YEAR(fecha_emision) = $anioActual")->fetchColumn(),
    'compras_pendientes' => $db->query("SELECT COUNT(*) FROM facturas_compra WHERE estado = 'pendiente' AND YEAR(fecha_emision) = $anioActual")->fetchColumn(),
    'ventas_pendientes' => $db->query("SELECT COUNT(*) FROM facturas_venta WHERE estado = 'pendiente' AND YEAR(fecha_emision) = $anioActual")->fetchColumn(),
];

// 2. Datos para la Gráfica Mensual (Compras vs Ventas)
$mesesNombres = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
$dataCompras = array_fill(0, 12, 0);
$dataVentas = array_fill(0, 12, 0);

$sqlC = "SELECT MONTH(fecha_emision) as mes, COUNT(*) as total FROM facturas_compra WHERE YEAR(fecha_emision) = $anioActual GROUP BY mes";
$resC = $db->query($sqlC)->fetchAll();
foreach($resC as $r) { $dataCompras[$r['mes']-1] = (int)$r['total']; }

$sqlV = "SELECT MONTH(fecha_emision) as mes, COUNT(*) as total FROM facturas_venta WHERE YEAR(fecha_emision) = $anioActual GROUP BY mes";
$resV = $db->query($sqlV)->fetchAll();
foreach($resV as $r) { $dataVentas[$r['mes']-1] = (int)$r['total']; }

// 3. Top Proveedores y Clientes
$topProveedores = $db->query("SELECT e.nombre, COUNT(f.id) as total 
                             FROM facturas_compra f 
                             JOIN entidades e ON f.entidad_id = e.id 
                             GROUP BY e.id ORDER BY total DESC LIMIT 5")->fetchAll();

$topClientes = $db->query("SELECT e.nombre, COUNT(f.id) as total 
                          FROM facturas_venta f 
                          JOIN entidades e ON f.entidad_id = e.id 
                          GROUP BY e.id ORDER BY total DESC LIMIT 5")->fetchAll();
?>

<style>
    :root { --alt-vinotinto: #632626; --alt-navy: #2c3e50; }
    body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
    .main-content { margin-left: 280px; padding: 20px 40px; }

    .kpi-card { background: white; border-radius: 12px; padding: 20px; border: 1px solid #eee; transition: 0.3s; }
    .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    .kpi-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .kpi-val { font-size: 24px; font-weight: 800; color: #333; display: block; }
    .kpi-label { font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }

    .chart-container { background: white; border-radius: 15px; padding: 25px; border: 1px solid #eee; margin-bottom: 30px; position: relative; }
    .chart-wrapper { position: relative; height: 350px; width: 100%; }
    .table-resumen { width: 100%; font-size: 13px; }
    .table-resumen th { font-weight: 800; color: #666; text-transform: uppercase; font-size: 11px; padding-bottom: 10px; border-bottom: 2px solid #f2f2f2; }
    .table-resumen td { padding: 12px 0; border-bottom: 1px solid #f9f9f9; }
</style>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h4 class="fw-bold m-0" style="color: var(--alt-vinotinto);">Reportes Generales</h4>
            <small class="text-muted">ALT-CONFECCIONES | Análisis de Operaciones <?= $anioActual ?></small>
        </div>
        <div class="text-end">
            <span class="text-muted small">Generado por: <strong><?= strtoupper($_SESSION['username'] ?? 'SISTEMA') ?></strong></span>
        </div>
    </div>

    <!-- KPIs Superiores -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon bg-primary-subtle text-primary"><i data-lucide="shopping-cart"></i></div>
                <span class="kpi-val"><?= $kpis['compras_total'] ?></span>
                <span class="kpi-label">Total Compras</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon bg-success-subtle text-success"><i data-lucide="trending-up"></i></div>
                <span class="kpi-val"><?= $kpis['ventas_total'] ?></span>
                <span class="kpi-label">Total Ventas</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon bg-warning-subtle text-warning"><i data-lucide="clock"></i></div>
                <span class="kpi-val"><?= $kpis['compras_pendientes'] ?></span>
                <span class="kpi-label">Por Pagar</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon bg-danger-subtle text-danger"><i data-lucide="alert-circle"></i></div>
                <span class="kpi-val"><?= $kpis['ventas_pendientes'] ?></span>
                <span class="kpi-label">Por Cobrar</span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfica Principal -->
        <div class="col-md-8">
            <div class="chart-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold m-0">Movimiento Mensual de Documentos</h6>
                    <div class="badge bg-light text-dark border">Año <?= $anioActual ?></div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Resúmenes Laterales -->
        <div class="col-md-4">
            <div class="chart-container shadow-sm mb-4">
                <h6 class="fw-bold mb-4">Top 5 Proveedores</h6>
                <table class="table-resumen">
                    <thead>
                        <tr><th>Nombre</th><th class="text-end">Docs</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($topProveedores as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars(substr($p['nombre'], 0, 25)) ?>...</td>
                            <td class="text-end fw-bold"><?= $p['total'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="chart-container shadow-sm">
                <h6 class="fw-bold mb-4">Top 5 Clientes</h6>
                <table class="table-resumen">
                    <thead>
                        <tr><th>Nombre</th><th class="text-end">Docs</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($topClientes as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars(substr($c['nombre'], 0, 25)) ?>...</td>
                            <td class="text-end fw-bold"><?= $c['total'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    lucide.createIcons();

    // Configuración de Chart.js
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    // Destruir instancia previa si existe (evita errores de redibujado)
    let chartStatus = Chart.getChart("mainChart");
    if (chartStatus != undefined) {
        chartStatus.destroy();
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($mesesNombres) ?>,
            datasets: [
                {
                    label: 'Facturas de Compra',
                    data: <?= json_encode($dataCompras) ?>,
                    backgroundColor: '#632626',
                    borderRadius: 5,
                },
                {
                    label: 'Facturas de Venta',
                    data: <?= json_encode($dataVentas) ?>,
                    backgroundColor: '#2c3e50',
                    borderRadius: 5,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
