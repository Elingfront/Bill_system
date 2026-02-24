<?php
namespace App\Models;
use App\Core\Database;
 

class entidadM {
public function guardar($data) {
    $db = Database::getConnection();
    $sql = "INSERT INTO entidades(nombre, nit_cedula, tipo, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    
        return $stmt->execute([
            $data['nombre']    ?? 'Sin Nombre',
            $data['nit']       ?? '0',
            $data['tipo']      ?? 'PROVEEDOR',
            $data['telefono']  ?? '0',
            $data['correo']    ?? 'sin@correo.com', 
            $data['direccion'] ?? 'Sin Direccion',
        ]);  
}
}
?>