<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$titulo = "Registro de Externos | ALT";
include __DIR__ . '/auth/header.php'; 
include __DIR__ . '/auth/sidebar.php';
?>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-lg border-0" style="border-radius: 10px;">
                <div class="card-header bg-light border-0 py-3">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <a class="navbar-brand text-vinotinto fw-bold d-flex align-items-center" href="#">
                            <img src="/sistema_facturas/logosinfondo.png" alt="Logo ALT" width="60" height="60" class="d-inline-block align-text-top me-2">
                            ALT-CONFECCIONES
                        </a>
                    </div>
                    <h5 class="text-center text-muted mb-0" style="letter-spacing: 2px; margin-top: 15px;">
                        NUEVO PROVEEDOR // CLIENTE
                    </h5>
                </div>

                <div class="card-body p-5">
                    <form id="formEntidad" action="../app/Controllers/EntidadController.php" method="POST">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-uppercase small">Razón Social </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i data-lucide="building-2" class="text-muted"></i></span>
                                <input type="text" name="nombre" class="form-control border-start-0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-uppercase small">NIT // Cédula</label>
                            <div class="input-group">
                                 <span class="input-group-text bg-white border-end-0"><i data-lucide="fingerprint" class="text-muted"></i></span>
                                <input type="text" name="nit" class="form-control border-start-0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-uppercase small">Teléfono de contacto</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i data-lucide="phone" class="text-muted"></i></span>
                                <input type="text" name="telefono" class="form-control border-start-0">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-uppercase small">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i data-lucide="mail" class="text-muted"></i></span>
                                <input type="email" name="correo" class="form-control border-start-0">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-uppercase small">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i data-lucide="map-pin" class="text-muted"></i></span>
                                <input type="text" name="direccion" class="form-control border-start-0">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-uppercase small">TIPO</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i data-lucide="user-pen" class="text-muted"></i>
                                </span>
                                <select name="tipo" class="form-select border-start-0" required>
                                    <option value="">Seleccione el tipo de entidad</option>
                                    <option value="PROVEEDOR">PROVEEDOR</option>
                                    <option value="CLIENTE">CLIENTE</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-lg text-white" style="background-color: #633035;">
                                <i data-lucide="save" class="me-2"></i>GUARDAR ENTIDAD
                            </button>
                            
                            <div class="text-center">
                                <a href="dashboard.php" class="text-muted text-uppercase small text-decoration-underline">CANCELAR</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .logo-fill-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 20px;
    }
    .logo-bg {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        filter: grayscale(100%);
        opacity: 0.1;
    }
    .logo-fill {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        clip-path: inset(100% 0 0 0); /* Empieza vacío */
        transition: clip-path 0.05s linear;
    }
    .loading-text {
        color: #633035;
        font-weight: bold;
    }
</style>

<?php if(isset($_GET['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: 'La entidad se registró correctamente en el sistema ALT.',
        confirmButtonColor: '#633035'
    });
</script>
<?php endif; ?>

<script>
document.getElementById('formEntidad').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const logoUrl = '/sistema_facturas/logosinfondo.png'; 

    Swal.fire({
        title: '¡ALT-CONFECCIONES!', 
        icon: null, 
        html: `
            <div class="logo-fill-container">
                <img src="${logoUrl}" class="logo-bg">
                <img src="${logoUrl}" id="fill-image" class="logo-fill">
            </div>
            <div id="status-msg" class="loading-text"><span id="percent-text">PROCESO FINALIZADO AL 100%</span></div>
        `,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            let progress = 100;
            const fillImage = document.getElementById('fill-image');
            const percentText = document.getElementById('percent-text');
            const statusMsg = document.getElementById('status-msg');
            
            const interval = setInterval(() => {
                progress -= 2;
                if (progress <= 0) {
                    clearInterval(interval);
                    fillImage.style.clipPath = `inset(0% 0 0 0)`;
                    
                    // Actualizamos la alerta al terminar sin poner el chulo verde
                    Swal.update({
                        title: '¡REGISTRO LISTO!',
                        showConfirmButton: true,
                        confirmButtonText: '¡HECHO!',
                        confirmButtonColor: '#633035'
                    });
                    statusMsg.innerHTML = '<b style="color: #cc2233ff;">PROCESO FINALIZADO</b>';
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
// Inicializamos los iconos de Lucide
lucide.createIcons();
</script>