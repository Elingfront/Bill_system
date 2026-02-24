<?php
namespace App\Controllers;

use App\Core\Database;

class AuthController {

    public function login() {
        // Asegurar que la sesión esté activa antes de cualquier operación
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Limpiar entradas para evitar espacios accidentales
            $user = trim($_POST['usuario']);
            $pass = trim($_POST['password']);

            try {
                $db = Database::getConnection();
                // Buscamos al usuario por su username
                $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ?");
                $stmt->execute([$user]);
                $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

                // Verificar existencia y validar el hash de la contraseña
                if ($usuario && password_verify($pass, $usuario['password_hash'])) {
                    
                    // Guardar datos en la sesión
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['rol']     = $usuario['rol'];
                    
                    // Usamos el nombre exacto de la columna de tu DB
                    $_SESSION['nombre']  = $usuario['nombre_completo'];

                    // Forzar el guardado de la sesión antes de redireccionar
                    session_write_close();

                    header("Location: /sistema_facturas/backend/views/dashboard");
                    exit;
                } else {
                    // Si falla, enviamos de vuelta al login con un error
                    header("Location: login?error=1");
                    exit;
                }
            } catch (\PDOException $e) {
                // En caso de error de base de datos
                die("Error de conexión: " . $e->getMessage());
            }
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: /sistema_facturas/backend/views/auth/login");
        exit;
    }
}