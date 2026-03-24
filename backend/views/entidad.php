<?php 
session_start();
if (!isset($_SESSION['autenticado'])) {
    header("Location: login.php");
    exit();
}

$titulo = "Directorio de Terceros | ALT";
include __DIR__ . '/auth/header.php'; 
include __DIR__ . '/auth/sidebar.php';

require_once 'C:/wamp/www/sistema_facturas/backend/app/Core/Database.php';
use App\Core\Database;

$db = Database::getConnection();
$entidades = $db->query("SELECT * FROM entidades ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
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
    .alt-header-btn {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,0.95); color: #632626;
        padding: 8px 18px; border-radius: 8px; font-size: 12px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.3px; border: none;
        cursor: pointer; transition: all 0.2s; text-decoration: none;
    }
    .alt-header-btn:hover { background: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); color: #632626; }

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
    
    .badge-tipo { 
        font-size: 10px; font-weight: bold; padding: 3px 8px; border-radius: 3px; 
        text-transform: uppercase; border: 1px solid; display: inline-block;
    }
    .tipo-proveedor { color: #632626; border-color: #632626; background: #fdf5f5; }
    .tipo-cliente { color: #2c3e50; border-color: #2c3e50; background: #f0f4f8; }
</style>

<main class="main-content">
    <div class="alt-page-header">
        <div class="alt-header-content">
            <div class="alt-header-icon"><i data-lucide="users" size="24"></i></div>
            <div>
                <h4 class="alt-header-title">Directorio de Terceros</h4>
                <span class="alt-header-subtitle">ALT-CONFECCIONES · Clientes y Proveedores</span>
            </div>
        </div>
        <div class="alt-header-meta">
            <span class="alt-header-badge"><i data-lucide="user" size="12"></i> <?= strtoupper($_SESSION['username'] ?? 'USUARIO') ?></span>
            <button class="alt-header-btn" data-bs-toggle="modal" data-bs-target="#modalNuevaEntidad">
                <i data-lucide="plus-circle" size="14"></i> NUEVO TERCERO
            </button>
        </div>
    </div>

    <div class="filter-section shadow-sm">
        <div class="row align-items-end g-3">
            <div class="col-md-8">
                <div class="label-min">Búsqueda Rápida (NIT / Nombre / Ciudad)</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i data-lucide="search" size="14"></i></span>
                    <input type="text" id="inputBusqueda" class="form-control" placeholder="Escriba para filtrar terceros..." onkeyup="ejecutarFiltro()">
                </div>
            </div>
            <div class="col-md-4">
                <div class="label-min">Filtrar por Tipo</div>
                <select id="selectTipo" class="form-select form-select-sm" onchange="ejecutarFiltro()">
                    <option value="todos">Todos los tipos</option>
                    <option value="PROVEEDOR">PROVEEDORES</option>
                    <option value="CLIENTE">CLIENTES</option>
                </select>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="tabla-alt" id="tablaEntidades">
            <thead>
                <tr>
                    <th style="width: 160px;">NIT / CÉDULA</th>
                    <th>RAZÓN SOCIAL / NOMBRE</th>
                    <th>CONTACTO</th>
                    <th>UBICACIÓN</th>
                    <th class="text-end">TIPO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entidades as $e): ?>
                <tr class="row-entidad" 
                    data-tipo="<?= strtoupper($e['tipo']) ?>"
                    data-search="<?= htmlspecialchars(strtolower(($e['nit_cedula'] ?? '') . ' ' . ($e['nombre'] ?? '') . ' ' . ($e['direccion'] ?? ''))) ?>">
                    <td class="nit-font"><?= htmlspecialchars($e['nit_cedula']) ?: '---' ?></td>
                    <td class="text-uppercase">
                        <strong style="font-size: 13px;"><?= htmlspecialchars($e['nombre']) ?></strong>
                    </td>
                    <td class="text-muted small">
                        <div><i data-lucide="phone" size="10"></i> <?= htmlspecialchars($e['telefono']) ?: '---' ?></div>
                        <div><i data-lucide="mail" size="10"></i> <?= htmlspecialchars($e['correo']) ?: '---' ?></div>
                    </td>
                    <td class="text-muted small">
                        <i data-lucide="map-pin" size="10"></i> <?= htmlspecialchars($e['direccion']) ?: '---' ?>
                    </td>
                    <td class="text-end">
                        <span class="badge-tipo <?= (strtoupper($e['tipo']) == 'PROVEEDOR') ? 'tipo-proveedor' : 'tipo-cliente' ?>">
                            <?= htmlspecialchars($e['tipo']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Registro -->
<div class="modal fade" id="modalNuevaEntidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0 p-4">
                <h5 class="fw-bold text-dark">Registrar Nuevo Tercero</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEntidad" action="../app/Controllers/EntidadController.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="label-min">Razón Social / Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="label-min">NIT / Cédula de Ciudadanía</label>
                        <input type="text" name="nit_cedula" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="label-min">Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="label-min">Tipo</label>
                            <select name="tipo" class="form-select" required>
                                <option value="PROVEEDOR">PROVEEDOR</option>
                                <option value="CLIENTE">CLIENTE</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="label-min">Correo Electrónico</label>
                        <input type="email" name="correo" class="form-control">
                    </div>
                    <div class="mb-0">
                        <label class="label-min">Dirección Física</label>
                        <input type="text" name="direccion" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-dark w-100 fw-bold py-2">GUARDAR TERCERO EN SISTEMA</button>
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

    function ejecutarFiltro() {
        const searchInput = document.getElementById('inputBusqueda');
        const search = searchInput ? searchInput.value.toLowerCase() : '';
        
        const selectTipo = document.getElementById('selectTipo');
        const tipo = selectTipo ? selectTipo.value : 'todos';
        
        const rows = document.querySelectorAll('.row-entidad');

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search') || '';
            const rowTipo = row.getAttribute('data-tipo') || '';
            
            const matchSearch = rowSearch.includes(search);
            const matchTipo = (tipo === 'todos' || rowTipo === tipo);

            if (matchSearch && matchTipo) {
                row.style.setProperty('display', '', 'important');
            } else {
                row.style.setProperty('display', 'none', 'important');
            }
        });
    }

    // Alertas de éxito
    <?php if(isset($_GET['success'])): ?>
    Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: 'La entidad se registró correctamente.',
        confirmButtonColor: '#632626'
    });
    window.history.replaceState({}, document.title, window.location.pathname);
    <?php endif; ?>
</script>
