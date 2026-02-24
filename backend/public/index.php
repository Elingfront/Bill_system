<?php 
session_start(); 
require_once __DIR__ . '/../../vendor/autoload.php';



$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../'); 
$dotenv->load();


// Limpiamos la URL para obtener la ruta
$public_path = '/sistema_facturas/backend/public';
$route = str_replace($public_path, '', $_SERVER['REQUEST_URI']);
$route = explode('?', $route)[0];
$route = trim($route, '/');

// Ruta por defecto
if ($route === '') { $route = 'login'; }

switch ($route) {
    case '/login':
        require __DIR__ . '/../views/auth/login.php';
        break;

    case '/dashboard':
        // Asegúrate de que la ruta sea exacta a como la tienes en las carpetas
        require __DIR__ . '/../views/dashboard.php'; 
        break;

    case '/cargar_factura':
        require 'backend/views/cargar_factura.php';
        break;
    case '/salir':
        require '../views/auth/login.php';
        break;

    default:
        // Si no encuentra la ruta, lo manda al login por defecto
        require '../views/auth/login.php';
        break;
}
?>