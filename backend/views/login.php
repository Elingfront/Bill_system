<?php
session_start();
require_once 'C:/wamp/www/sistema_facturas/backend/app/Core/Database.php';
use App\Core\Database;

$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usamos nombres raros para que el navegador no los guarde
    $u = $_POST['txt_user_id_alt'] ?? '';
    $p = $_POST['txt_pass_val_alt'] ?? '';
    
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? LIMIT 1");
    $stmt->execute([$u]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($p, $user['password_hash'])) {
        $_SESSION['autenticado'] = true;
        $_SESSION['username'] = $user['username'];
        header("Location: /sistema_facturas/backend/views/dashboard.php");
        exit();
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | Alt-Confecciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary-color: #633035; }
        body { background-color: #ffffff; height: 100vh; display: flex; flex-direction: column; }
        .top-banner { background-color: var(--primary-color); color: white; padding: 10px; text-align: center; font-size: 1.5rem; margin: 20px auto; width: 80%; border-radius: 5px; }
        .main-container { display: flex; flex: 1; align-items: center; justify-content: center; gap: 100px; }
        .login-box { width: 350px; }
        .btn-vinotinto { background-color: var(--primary-color); color: white; border: none; width: 100%; }
        .logo-text { color: var(--primary-color); font-weight: bold; margin-top: 10px; text-align: center; font-size: 1.2rem; }
    </style>
</head>
<body onload="limpiarTodo()">

    <div class="top-banner">Para utilizar este sistema, deberá iniciar sesión</div>

    <div class="container main-container">
        <div class="login-box">
            <form id="loginForm" action="login.php" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label fw-bold">Usuario</label>
                    <input type="text" name="txt_user_id_alt" id="u_field" class="form-control" required autocomplete="off">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Contraseña</label>
                    <input type="password" name="txt_pass_val_alt" id="p_field" class="form-control" required autocomplete="new-password">
                </div>                
                <button type="submit" class="btn btn-vinotinto py-2 fw-bold mb-3">Iniciar Sesión</button>
                
                <div class="text-center">
                    <a href="registro_usuario.php" class="text-decoration-none small fw-bold" style="color: var(--primary-color);">Registrar nuevo usuario</a>
                </div>
            </form>
            
            <?php if($error): ?>
                <div class="alert alert-danger mt-3 small py-2 text-center">Datos incorrectos.</div>
            <?php endif; ?>
        </div>

        <div class="logo-box text-center">
            <img src="/sistema_facturas/logo.png" alt="Logo" style="width: 250px;">
            <div class="logo-text">Alt-Confecciones</div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Esta función es la que BORRA TODO de la pantalla
        function limpiarTodo() {
            const u = document.getElementById('u_field');
            const p = document.getElementById('p_field');
            
            // Limpia 3 veces con delay para ganarle al autocompletado del navegador
            u.value = ''; p.value = '';
            setTimeout(() => { u.value = ''; p.value = ''; }, 10);
            setTimeout(() => { u.value = ''; p.value = ''; }, 100);
            setTimeout(() => { u.value = ''; p.value = ''; }, 500);
        }
    </script>
</body>
</html>