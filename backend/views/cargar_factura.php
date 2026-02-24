<?php
session_start();
$titulo = "Ingreso Facturas | ALT";


require_once __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;

// Consulta rápida para traer ID y Nombre de los terceros registrados
$db = Database::getConnection();
$query = $db->query("SELECT id, nombre FROM entidades ORDER BY nombre ASC");
$terceros = $query->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../views/auth/header.php';
include __DIR__ . '/../views/auth/sidebar.php';
?>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 bg-dark min-vh-100 p-0 d-none d-md-block"></div>

        <div class="col-md-10 p-4">
            <h2 class="text-vinotinto mb-4">
                <i data-lucide="file-plus"></i> Nueva Factura 
            </h2>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form id="formFactura" action="../app/Controllers/FacturaController.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold d-block">Selecciona el Tipo de FACTURA:</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="tipo_factura" id="tipo_compra" value="compra" checked>
                                    <label class="btn btn-outline-vinotinto py-3 d-flex align-items-center justify-content-center" for="tipo_compra">
                                        <i data-lucide="shopping-cart" class="me-2"></i> Compra
                                    </label>

                                    <input type="radio" class="btn-check" name="tipo_factura" id="tipo_venta" value="venta">
                                    <label class="btn btn-outline-vinotinto py-3 d-flex align-items-center justify-content-center" for="tipo_venta">
                                        <i data-lucide="trending-up" class="me-2"></i> Venta
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Folio del Sistema</label>
                                <input type="text" class="form-control bg-light" placeholder="ALT GENERA AUTOMATICAMENTE TU FOLIO" readonly>
                            </div>
                          
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Tercero (Proveedor / Cliente)</label>
                                <select name="entidad_id" id="select-tercero" required>
                                    <option value="">Seleccione un tercero...</option>
                                    <?php foreach($terceros as $t): ?>
                                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha de Emisión</label>
                                <input type="date" name="fecha_emision" class="form-control" required>
                            </div>
                             <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Archivo PDF</label>
                                <input type="file" name="pdf_factura" class="form-control" accept="application/pdf" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch p-3 border rounded bg-light d-flex align-items-center justify-content-between">
                                    <label class="form-check-label fw-bold mb-0" for="factura_pagada">
                                        <i data-lucide="check-circle" class="text-success me-2"></i> ¿Esta factura ya está pagada?
                                    </label>
                                    <input class="form-check-input" type="checkbox" name="pagada" id="factura_pagada" 
                                        onchange="toggleCamposPago()" style="width: 3em; height: 1.5em; cursor: pointer;">
                                </div>
                            </div>
                        </div>

 
                        <div id="seccion_pago" class="row mt-3 mx-0 p-3 border border-2 rounded bg-white shadow-sm" style="display: none; border-color: #ffffffff !important;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold" id="label_contable">Comprobante de Egreso</label>
                                <input type="file" name="archivo_contable" id="archivo_contable" class="form-control" accept="application/pdf,image/*">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Soporte de Pago (Banco)</label>
                                <input type="file" name="archivo_soporte" id="archivo_soporte" class="form-control" accept="application/pdf,image/*">
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info py-2 mb-0 d-flex align-items-center" style="font-size: 0.85rem;">
                                    <i data-lucide="info" class="me-2" style="width: 18px;"></i>
                                    Para facturas pagadas, adjunte el soporte y el documento contable para cumplir el flujo.
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-vinotinto btn-lg px-5">
                                <i data-lucide="upload-cloud" class="me-2"></i> Procesar Registro
                            </button>
                        </div>                 
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

.btn-outline-vinotinto { color: #633035; border-color: #633035; }
    .btn-outline-vinotinto:hover, .btn-check:checked + .btn-outline-vinotinto { background-color: #633035; color: white; border-color: #633035; }
    .btn-vinotinto { background-color: #633035; color: white; }
    .text-vinotinto { color: #633035; }


    .logo-fill-container { position: relative; width: 120px; height: 120px; margin: 0 auto 15px; }
    .logo-bg { position: absolute; top: 0; left: 0; width: 100%; filter: grayscale(100%); opacity: 0.1; }
    .logo-fill { position: absolute; top: 0; left: 0; width: 100%; clip-path: inset(100% 0 0 0); transition: clip-path 0.05s linear; }
    .loading-text { color: #633035; font-weight: bold; }
</style>

<script>


function toggleCamposPago() {
    const checkPago = document.getElementById('factura_pagada');
    const seccionPago = document.getElementById('seccion_pago');
    
    // Inputs para validación
    const inputContable = document.getElementById('archivo_contable');
    const inputSoporte = document.getElementById('archivo_soporte');
    
    // Labels dinámicos según el diagrama
    const labelContable = document.getElementById('label_contable');
    const esCompra = document.getElementById('tipo_compra').checked;

    if (checkPago.checked) {
         seccionPago.style.display = 'flex';
        inputContable.required = true;
        inputSoporte.required = true;
        
         labelContable.innerText = esCompra ? "Comprobante de Egreso (PDF/IMG)" : "Recibo de Caja (PDF/IMG)";
    } else {
         seccionPago.style.display = 'none';
        inputContable.required = false;
        inputSoporte.required = false;
        inputContable.value = '';
        inputSoporte.value = '';
    }
    
     lucide.createIcons();
}

 document.getElementById('tipo_compra').addEventListener('change', toggleCamposPago);
document.getElementById('tipo_venta').addEventListener('change', toggleCamposPago);
document.querySelectorAll('input[name="tipo_factura"]').forEach(radio =>{
    radio.addEventListener('change', toggleCamposPago);
});
new TomSelect("#select-tercero", {
    create: false,
    sortField: { field: "text", direction: "asc" },
     render: {
        no_results: function(data, escape) {
            return '<div class="no-results">¡Rayos! No pudimos encontrar esta entidad 😔💔</div>';
        },
    }
});
 document.getElementById('formFactura').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const logoUrl = '/sistema_facturas/logosinfondo.png'; 

    Swal.fire({
        title: '¡ALT-CONFECCIONES!',
        html: `
            <div class="logo-fill-container">
                <img src="${logoUrl}" class="logo-bg">
                <img src="${logoUrl}" id="fill-image" class="logo-fill">
            </div>
            <div id="status-msg" class="loading-text"><span id="percent-text">0%</span></div>
        `,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            let progress = 100;
            const fillImage = document.getElementById('fill-image');
            const percentText = document.getElementById('percent-text');
            
            const interval = setInterval(() => {
                progress -= 2;
                if (progress <= 0) {
                    clearInterval(interval);
                    fillImage.style.clipPath = `inset(0% 0 0 0)`;
                    Swal.update({
                        title: '¡CARGA LISTA!',
                        showConfirmButton: true,
                        confirmButtonText: '¡HECHO!',
                        confirmButtonColor: '#633035'
                    });
                    document.getElementById('status-msg').innerHTML = 'PROCESO FINALIZADO';
                } else {
                    fillImage.style.clipPath = `inset(${progress}% 0 0 0)`;
                    percentText.innerText = (100 - progress) + "%";
                }
            }, 30);
        }
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});

lucide.createIcons();
</script>