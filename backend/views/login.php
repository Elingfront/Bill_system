<?php
session_start();
session_unset();
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/sistema_facturas/logosinfondo.png">
    <title>Login | Alt-Confecciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary-color: #633035; }
        body { background-color: #ffffff; height: 100vh; display: flex; flex-direction: column; }
        .top-banner { background-color: var(--primary-color); color: white; padding: 10px; text-align: center; font-size: 1.5rem; margin: 20px auto; width: 80%; border-radius: 5px; }
        .main-container { display: flex; flex: 1; align-items: center; justify-content: center; gap: 100px; }
        .login-box { width: 350px; }
        .logo-text { color: var(--primary-color); font-weight: bold; margin-top: 10px; text-align: center; }
        .btn-vinotinto { background-color: var(--primary-color); color: white; border: none; width: 100%; }
        .btn-vinotinto:hover { background-color: #4a2226; color: white; }
        
        /* Estilo para el contenedor de password y el ojito */
        .password-wrapper { position: relative; }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            border: none;
            background: none;
        }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.25rem rgba(99, 48, 53, 0.25); }
    </style>
</head>
<body>

    <div class="top-banner">Para utilizar este sistema, deberá iniciar sesión</div>

    <div class="container main-container">
        <div class="login-box">
            <form id="formLogin" action="/sistema_facturas/backend/views/dashboard.php" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label fw-bold">Usuario</label>
                    <input type="text" name="usuario" id="user_input" class="form-control" required autocomplete="off">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="pass_input" class="form-control" required autocomplete="new-password">
                        <button type="button" class="toggle-password" onclick="togglePass()">
                            <i id="eye-icon" data-lucide="eye"></i>
                        </button>
                    </div>
                </div>                
                <button type="submit" class="btn btn-vinotinto py-2 fw-bold">Iniciar Sesión</button>
            </form>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="text-danger mt-3 small text-center">Credenciales incorrectas. Intente de nuevo.</div>
            <?php endif; ?>
        </div>

        <div class="logo-box text-center">
            <img src="/sistema_facturas/logo.png" alt="Logo ALT" style="width: 250px;">
            <div class="logo-text">Alt-Confecciones</div>
        </div>
    </div>

    <script>
        // Inicializar iconos
        lucide.createIcons();

        // Función para mostrar/ocultar contraseña
        function togglePass() {
            const passInput = document.getElementById('pass_input');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons(); // Recargar iconos
        }

        // Limpieza forzada de campos al cargar
        window.onload = function() {
            const u = document.getElementById('user_input');
            const p = document.getElementById('pass_input');
            u.value = '';
            p.value = '';
            setTimeout(() => { u.value = ''; p.value = ''; }, 100);
        };
    </script>
</body>
</html>