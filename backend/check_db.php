<?php
require_once 'C:/wamp/www/sistema_facturas/backend/app/Core/Database.php';
use App\Core\Database;

try {
    $db = Database::getConnection();
    echo "--- Columnas de facturas_compra ---\n";
    $stmt = $db->query("DESCRIBE facturas_compra");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\n--- Columnas de entidades ---\n";
    $stmt = $db->query("DESCRIBE entidades");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
