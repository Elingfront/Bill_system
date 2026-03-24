<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar-wrapper position-fixed d-flex flex-column" style="width: 260px; height: 100vh; background: #221a1bff; color: white; z-index: 1000;">
    <div class="p-4 border-bottom border-secondary mb-3">
        <div class="d-flex align-items-center">
            <img src="/sistema_facturas/logosinfondo.png" alt="ALT" width="35" class="me-2">
            <h5 class="fw-bold mb-0 text-white">ALT SISTEMAS</h5>
        </div>
    </div>

    <div class="px-3 d-flex flex-column flex-grow-1">
        <nav class="nav flex-column gap-1">
            <a href="dashboard.php" class="nav-link text-white d-flex align-items-center py-2 px-3 rounded <?= ($currentPage == 'dashboard.php') ? 'active-link' : 'nav-hover' ?>">
                <i data-lucide="layout-dashboard" class="me-2" size="18"></i> Panel de Administración
            </a>
            <a href="facturas_compra.php" class="nav-link text-white d-flex align-items-center py-2 px-3 rounded <?= ($currentPage == 'facturas_compra.php') ? 'active-link' : 'nav-hover' ?>">
                <i data-lucide="shopping-cart" class="me-2" size="18"></i> Facturas de Compra
            </a>
            <a href="facturas_venta.php" class="nav-link text-white d-flex align-items-center py-2 px-3 rounded <?= ($currentPage == 'facturas_venta.php') ? 'active-link' : 'nav-hover' ?>">
                <i data-lucide="badge-dollar-sign" class="me-2" size="18"></i> Facturas de Venta
            </a>
            <a href="egresos.php" class="nav-link text-white d-flex align-items-center py-2 px-3 rounded <?= ($currentPage == 'egresos.php') ? 'active-link' : 'nav-hover' ?>">
                <i data-lucide="file-minus" class="me-2" size="18"></i> Comprobantes Egreso
            </a>
            <a href="recibos.php" class="nav-link text-white d-flex align-items-center py-2 px-3 rounded <?= ($currentPage == 'recibos.php') ? 'active-link' : 'nav-hover' ?>">
                <i data-lucide="file-plus" class="me-2" size="18"></i> Recibos de Caja
            </a>
        </nav>

        <hr class="border-secondary my-3">

        <p class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1px;">Pendientes Anuales</p>
        <nav class="nav flex-column gap-1">
            <a href="cuentas_pago.php" class="nav-link text-white d-flex align-items-center justify-content-between py-2 px-3 rounded <?= ($currentPage == 'cuentas_pago.php') ? 'active-link' : 'nav-hover' ?>">
                <div class="d-flex align-items-center">
                    <i data-lucide="clock-4" class="me-2" size="18"></i> Cuentas por Pagar
                </div>
                <span class="badge bg-danger rounded-pill shadow-sm" style="font-size: 10px;">!</span>
            </a>
            <a href="cuentas_cobro.php" class="nav-link text-white d-flex align-items-center justify-content-between py-2 px-3 rounded <?= ($currentPage == 'cuentas_cobro.php') ? 'active-link' : 'nav-hover' ?>">
                <div class="d-flex align-items-center">
                    <i data-lucide="trending-up" class="me-2" size="18"></i> Cuentas por Cobrar
                </div>
                <span class="badge bg-danger rounded-pill shadow-sm" style="font-size: 10px;">!</span>
            </a>
        </nav>

        <hr class="border-secondary my-3">

        <nav class="nav flex-column gap-1 mb-4">
            <a href="entidad.php" class="nav-link text-white d-flex align-items-center py-2 px-3 rounded <?= ($currentPage == 'entidad.php') ? 'active-link' : 'nav-hover' ?>">
                <i data-lucide="users" class="me-2" size="18"></i> Directorio (NITs)
            </a>
            <a href="reportes.php" class="nav-link text-white d-flex align-items-center py-2 px-3 rounded <?= ($currentPage == 'reportes.php') ? 'active-link' : 'nav-hover' ?>">
                <i data-lucide="bar-chart-3" class="me-2" size="18"></i> Reportes Generales
            </a>
        </nav>

        <!-- Información Corporativa -->
        <div class="mt-auto pt-4 pb-4 border-top border-secondary">
            <div class="px-3 text-start" style="line-height: 1.8;">
                <p class="mb-2 fw-bold text-white text-center" style="font-size: 16px; letter-spacing: 0.8px;">ALT CONFECCIONES S.A.S.</p>
                
                <div class="d-flex align-items-center mb-1 text-white-50" style="font-size: 15px;">
                    <i data-lucide="hash" class="me-2" size="14"></i> NIT 901235934
                </div>
                
                <div class="d-flex align-items-start mb-1 text-white-50" style="font-size: 14px;">
                    <i data-lucide="map-pin" class="me-2 mt-1" size="14"></i> 
                    <span>CR 43 G # 27 - 60 PISO 2<br>Medellín - Colombia</span>
                </div>
                <div class="d-flex align-items-center text-white-50" style="font-size: 14px;">
                    <i data-lucide="mail" class="me-2" size="13"></i> 
                    <span style="word-break: break-all;">altconfeccionessas@gmail.com</span>
                </div>

                <div class="d-flex align-items-center mb-1 text-white-50" style="font-size: 15px;">
                    <i data-lucide="phone" class="me-2" size="14"></i> Tel: 604 22 56
                </div>
                
            </div>
        </div>
    </div>
</aside>

<style>
    .nav-hover:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff !important;
        transition: 0.3s;
    }
    .active-link {
        background: #633035 !important; /* El color vinotinto que elegiste */
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .nav-link {
        font-size: 14px;
        font-weight: 500;
        color: #d1d1d1 !important;
    }
</style>

<script>
    // Asegurar que Lucide cargue los iconos en el Sidebar
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>