<?php

$envPath = __DIR__ . '\.env';

if (!file_exists($envPath)) {
    die("❌ El archivo .env no existe en: " . $envPath);
}

$contenido = file_get_contents($envPath);
if ($contenido === false || empty(trim($contenido))) {
    die("❌ El archivo .env existe pero ESTÁ VACÍO o no se puede leer.");
}

echo "✅ Contenido del .env detectado correctamente.<br>";

// Ahora intenta cargar Dotenv manualmente
require_once __DIR__ . '/../vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✅ Librería Dotenv cargada con éxito.<br>";
    echo "Ruta configurada: " . ($_ENV['UPLOAD_PATH'] ?? 'No definida');
} catch (Exception $e) {
    echo "❌ Error de Dotenv: " . $e->getMessage();
}