<?php
session_start();
$titulo = "Nueva Factura con IA | ALT";
require_once __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;

$db = Database::getConnection();
$query = $db->query("SELECT id, nombre, nit_cedula FROM entidades ORDER BY nombre ASC");
$terceros = $query->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../views/auth/header.php';
include __DIR__ . '/../views/auth/sidebar.php';
?>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .text-vinotinto { color: #633035; }
    .btn-vinotinto { background-color: #633035; color: white; border: none; }
    .btn-vinotinto:hover { background-color: #4a2428; color: white; }
    .btn-outline-vinotinto { color: #633035; border-color: #633035; }
    .btn-check:checked + .btn-outline-vinotinto { background-color: #633035; color: white; border-color: #633035; }

    .upload-container {
        border: 2px dashed #633035;
        background-color: #fcf8f8;
        border-radius: 12px;
        padding: 30px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .upload-container:hover { background-color: #f5ebeb; }
    #pdf_ia { cursor: pointer; }
    .loader-ia { display: none; color: #633035; font-weight: bold; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 d-none d-md-block bg-dark min-vh-100 p-0"></div>
        <div class="col-md-10 p-4">
            <h2 class="text-vinotinto fw-bold mb-4"><i data-lucide="sparkles"></i> Registro Inteligente ALT</h2>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <form id="formFactura" enctype="multipart/form-data">

                        <!-- ── 1. Tipo de operación ── -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">1. Seleccione Tipo de Operación</label>
                                <div class="btn-group w-100">
                                    <input type="radio" class="btn-check" name="tipo_factura" id="tipo_compra" value="compra" checked>
                                    <label class="btn btn-outline-vinotinto py-2" for="tipo_compra">
                                        <i data-lucide="shopping-bag"></i> Compra (Proveedor)
                                    </label>

                                    <input type="radio" class="btn-check" name="tipo_factura" id="tipo_venta" value="venta">
                                    <label class="btn btn-outline-vinotinto py-2" for="tipo_venta">
                                        <i data-lucide="trending-up"></i> Venta (Cliente)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- ── 2. Subir PDF ── -->
                        <div class="upload-container text-center mb-4" onclick="document.getElementById('pdf_ia').click()">
                            <i data-lucide="upload-cloud" size="48" class="text-vinotinto mb-2"></i>
                            <h5 class="fw-bold text-vinotinto">2. Subir Factura PDF</h5>
                            <p class="text-muted small">La IA detectará el nombre real y el número de factura</p>
                            <input type="file" id="pdf_ia" name="pdf_factura" class="form-control d-none" accept=".pdf">
                            <div id="file-name-display" class="badge bg-secondary d-none"></div>
                            <div id="loader-ia" class="loader-ia mt-2">
                                <span class="spinner-border spinner-border-sm"></span> Escaneando documento...
                            </div>
                        </div>

                        <!-- ── Campos principales ── -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Número Documento</label>
                                <input type="text" name="numero_factura" id="res_consecutivo"
                                       class="form-control" placeholder="Ej: 113761" required>
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Tercero (Cliente/Proveedor)</label>
                                <select id="select-tercero" name="entidad_id" required>
                                    <option value="">Seleccione o espere a la IA...</option>
                                    <?php foreach ($terceros as $t): ?>
                                        <option value="<?= $t['id'] ?>"
                                                data-nit="<?= htmlspecialchars($t['nit_cedula']) ?>"
                                                data-nombre="<?= htmlspecialchars($t['nombre']) ?>">
                                            <?= htmlspecialchars($t['nombre']) ?> (<?= htmlspecialchars($t['nit_cedula']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha de Emisión</label>
                                <input type="date" name="fecha_emision" id="res_fecha"
                                       class="form-control" required max="<?= date('Y-m-d') ?>">
                            </div>

                            <!-- Campos ocultos: los rellena el JS; los lee factura_guardar.php -->
                            <input type="hidden" name="nit_cedula"     id="hidden_nit"    value="">
                            <input type="hidden" name="nombre_tercero" id="hidden_nombre" value="">

                            <!-- ── Switch de pago ── -->
                            <div class="col-md-12 mt-3">
                                <div class="card bg-light border-0">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="fw-bold mb-0 text-vinotinto">¿Esta factura ya fue pagada?</h6>
                                            <small class="text-muted">Si marca esta opción, podrá subir el soporte de pago de una vez.</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="pagada"
                                                   id="switch_pago" style="width:3em;height:1.5em;cursor:pointer;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Soportes de pago (ocultos por defecto) ── -->
                        <div id="seccion_soportes" class="row mt-4 d-none">
                            <hr class="mb-4">
                            <h5 class="text-vinotinto fw-bold mb-3"><i data-lucide="paperclip"></i> Soportes de Pago</h5>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Soporte de Pago (PDF/Imagen)</label>
                                <input type="file" name="archivo_soporte" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Egreso Contable (Opcional)</label>
                                <input type="file" name="archivo_contable" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-vinotinto btn-lg px-5 shadow">
                                <i data-lucide="save"></i> Guardar en Sistema
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

// ── TomSelect ────────────────────────────────────────────────────────────────
const tom = new TomSelect("#select-tercero", {
    create: false,
    sortField: { field: "text", direction: "asc" },
    labelField: "text",
    valueField: "value",
    onChange(value) {
        // Cuando el usuario cambia el tercero manualmente, sincronizar campos ocultos
        if (!value) return;
        const el = document.querySelector(`#select-tercero option[value="${value}"]`);
        document.getElementById('hidden_nit').value    = el ? (el.dataset.nit    || '') : '';
        document.getElementById('hidden_nombre').value = el ? (el.dataset.nombre || '') : '';
    }
});

// ── Subida de PDF ────────────────────────────────────────────────────────────
document.getElementById('pdf_ia').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const display = document.getElementById('file-name-display');
    display.innerText = file.name;
    display.classList.remove('d-none');
    procesarPDF(file);
});

// ── Validador de fecha ───────────────────────────────────────────────────────
document.getElementById('res_fecha').addEventListener('change', function () {
    const selected = new Date(this.value);
    const today    = new Date();
    today.setHours(0, 0, 0, 0);
    if (selected > today) {
        Swal.fire({ icon: 'error', title: 'Fecha Inválida', text: 'No se permiten fechas futuras.' });
        this.value = '';
    }
});

// ── Switch de pago ───────────────────────────────────────────────────────────
document.getElementById('switch_pago').addEventListener('change', function () {
    document.getElementById('seccion_soportes').classList.toggle('d-none', !this.checked);
});

// ── Procesador de PDF con IA ─────────────────────────────────────────────────
async function procesarPDF(file) {
    if (file.type !== 'application/pdf') {
        await Swal.fire({ icon: 'warning', title: 'Solo PDF', text: 'El archivo debe ser un PDF.' });
        return;
    }

    const loader = document.getElementById('loader-ia');
    loader.style.display = 'block';

    const formData = new FormData();
    formData.append('archivo_factura', file);
    formData.append(
        'tipo_seleccionado',
        document.getElementById('tipo_venta').checked ? 'Cliente' : 'Proveedor'
    );

    const [, projectName] = window.location.pathname.split('/');
    const urlProcesador   = `${window.location.origin}/${projectName}/backend/app/IA/procesar_ia.php`;

    try {
        const response = await fetch(urlProcesador, {
            method : 'POST',
            body   : formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Error en el procesamiento IA');

        // 1. Consecutivo
        if (data.consecutivo) {
            document.getElementById('res_consecutivo').value = data.consecutivo;
        }

        // 2. Fecha  (viene DD/MM/YYYY, el input necesita YYYY-MM-DD)
        if (data.fecha) {
            const [d, m, y] = data.fecha.split('/');
            if (d && m && y) {
                document.getElementById('res_fecha').value = `${y}-${m}-${d}`;
            }
        }

        // 3. Tercero en TomSelect + campos ocultos
        if (data.entidad_id) {
            const strId = String(data.entidad_id);

            if (data.is_nuevo) {
                // Tercero creado en este momento → agregarlo al selector
                tom.addOption({
                    value: strId,
                    text : `${data.entidad_nombre} (${data.nit_cedula})`
                });
                tom.refreshOptions(false);
            }

            // true = silencioso (no dispara onChange para no pisar los hidden)
            tom.setValue(strId, true);

            // Llenar campos ocultos con los datos que devuelve la IA
            document.getElementById('hidden_nit').value    = data.nit_cedula     || '';
            document.getElementById('hidden_nombre').value = data.entidad_nombre  || '';
        }

        // Resumen visual del escaneo
        await Swal.fire({
            icon             : 'success',
            title            : '¡Factura escaneada!',
            html             : `
                <table class="table table-sm text-start mt-2">
                    <tr><th>Factura N°</th><td>${data.consecutivo || '—'}</td></tr>
                    <tr><th>Tercero</th>  <td>${data.entidad_nombre || '—'}</td></tr>
                    <tr><th>NIT</th>      <td>${data.nit_cedula || '—'}</td></tr>
                    <tr><th>Fecha</th>    <td>${data.fecha || '—'}</td></tr>
                </table>
            `,
            timer            : 4000,
            showConfirmButton: false,
            position         : 'center'
        });

    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error de IA', text: err.message });
    } finally {
        loader.style.display = 'none';
    }
}

// ── Guardar factura ──────────────────────────────────────────────────────────
document.getElementById('formFactura').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn             = this.querySelector('button[type="submit"]');
    const originalBtnHTML = btn.innerHTML;

    // Validaciones básicas antes de enviar
    if (!document.getElementById('res_consecutivo').value.trim()) {
        Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'El número de factura es obligatorio.' });
        return;
    }
    if (!tom.getValue()) {
        Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Debe seleccionar un tercero (cliente o proveedor).' });
        return;
    }
    if (!document.getElementById('res_fecha').value) {
        Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'La fecha de emisión es obligatoria.' });
        return;
    }

    try {
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        const formData = new FormData(this);
        // TomSelect a veces no se refleja bien en FormData estándar; forzar el valor
        formData.set('entidad_id', tom.getValue());

        const [, projectName] = window.location.pathname.split('/');
        const urlGuardar      = `${window.location.origin}/${projectName}/backend/public/factura_guardar.php`;

        const response = await fetch(urlGuardar, {
            method : 'POST',
            body   : formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();

        if (data.status === 'success') {
            await Swal.fire({
                icon             : 'success',
                title            : '¡Guardado!',
                text             : `Factura registrada con Sello ALT: ${data.sello}`,
                confirmButtonColor: '#633035'
            });
            window.location.reload();
        } else {
            throw new Error(data.message || 'Error desconocido al guardar');
        }

    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error al Guardar', text: err.message });
    } finally {
        btn.disabled  = false;
        btn.innerHTML = originalBtnHTML;
    }
});
</script>