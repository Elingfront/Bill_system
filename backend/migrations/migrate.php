<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

try {
    $conexion = Database::getConnection();

    $conexion->exec("CREATE TABLE IF NOT EXISTS control_migraciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_archivo VARCHAR(255),
        ejecutado_el TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $lista_sql = glob(__DIR__ . '/migrations/*.sql');
    $ya_ejecutados = $conexion->query("SELECT nombre_archivo FROM control_migraciones")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($lista_sql as $ruta_completa) {
        $archivo_actual = basename($ruta_completa);
        
        if (!in_array($archivo_actual, $ya_ejecutados)) {
            $contenido_sql = file_get_contents($ruta_completa);
            
            $conexion->exec($contenido_sql);
            
            $registro = $conexion->prepare("INSERT INTO control_migraciones (nombre_archivo) VALUES (?)");
            $registro->execute([$archivo_actual]);
            
            echo "✅ Aplicado: $archivo_actual\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}