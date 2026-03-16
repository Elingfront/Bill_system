<?php
session_start();
// SEGURIDAD: Si no está logueado, no puede registrar a nadie
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'C:/wamp/www/sistema_facturas/backend/app/Core/Database.php';
use App\Core\Database;

$mensaje = "";
$error_reg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['reg_user_alt'] ?? '';
    $n = $_POST['reg_nombre_alt'] ?? '';
    $r = $_POST['reg_rol_alt'] ?? 'contabilidad';
    $p = $_POST['reg_pass_alt'] ?? '';

    if (!empty($u) && !empty($p)) {
        $db = Database::getConnection();
        $hash = password_hash($p, PASSWORD_BCRYPT);
        
        try {
            $sql = "INSERT INTO usuarios (username, nombre_completo, password_hash, rol) VALUES (?, ?, ?, ?)";
            $db->prepare($sql)->execute([$u, $n, $hash, $r]);
            $mensaje = "Usuario creado con éxito.";
        } catch (Exception $e) {
            $error_reg = "Error: Ese usuario ya existe.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/sistema_facturas/logosinfondo.png">
    <title>Registro | Alt-Confecciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary-color: #633035; }
        body { background-color: #ffffff; height: 100vh; display: flex; flex-direction: column; }
        .top-banner { background-color: var(--primary-color); color: white; padding: 10px; text-align: center; font-size: 1.5rem; margin: 20px auto; width: 80%; border-radius: 5px; }
        .main-container { display: flex; flex: 1; align-items: center; justify-content: center; gap: 80px; }
        .login-box { width: 400px; }
        .btn-vinotinto { background-color: var(--primary-color); color: white; border: none; width: 100%; transition: 0.3s; }
        .btn-vinotinto:hover { background-color: #4a2226; color: white; }
        .logo-text { color: var(--primary-color); font-weight: bold; margin-top: 10px; text-align: center; font-size: 1.2rem; }
        .visually-hidden-trap { opacity: 0; position: absolute; height: 0; width: 0; z-index: -1; }
    </style>
</head>
<body onload="document.getElementById('form_reg').reset();">

    <div class="top-banner">Panel de Registro</div>

    <div class="container main-container">
        <div class="login-box">
            <?php if($mensaje): ?> <div class="alert alert-success py-2 small text-center"><?= $mensaje ?></div> <?php endif; ?>
            <?php if($error_reg): ?> <div class="alert alert-danger py-2 small text-center"><?= $error_reg ?></div> <?php endif; ?>

            <form id="form_reg" action="registro_usuario.php" method="POST" autocomplete="off">
                <input class="visually-hidden-trap" type="text" name="fake_u">
                <input class="visually-hidden-trap" type="password" name="fake_p">

                <div class="mb-3">
                    <label class="form-label fw-bold small">Nombre Completo</label>
                    <input type="text" name="reg_nombre_alt" class="form-control" placeholder="Ej: Juan Perez" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold small">Username (Login)</label>
                    <input type="text" name="reg_user_alt" class="form-control" placeholder="Ej: jperez" required autocomplete="off">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Rol del Usuario</label>
                    <select name="reg_rol_alt" class="form-select" required>
                        <option value="predeterminado">Seleccione un rol...</option>
                        <option value="admin">Administrador (Admin)</option>
                        <option value="contabilidad">Contabilidad</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Contraseña Nueva</label>
                    <input type="password" name="reg_pass_alt" class="form-control" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-vinotinto py-2 fw-bold mb-3">GUARDAR USUARIO</button>
                
                <div class="text-center">
                    <a href="/sistema_facturas/backend/views/login.php" class="text-decoration-none small fw-bold text-muted">Volver</a>
                </div>
            </form>
        </div>

        <div class="logo-box text-center d-none d-md-block">
            <img src="/sistema_facturas/logo.png" alt="Logo" style="width: 220px;">
            <div class="logo-text">Alt-Confecciones</div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>