<?php
namespace App\Models;

require_once dirname(__DIR__) . '/Core/Database.php'; 
use App\Core\Database;
use PDO;

class Factura {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function guardar($datos) {
        $sql = "INSERT INTO facturas_compra
                (entidad_id, fecha_emision, estado, usuario_id, archivo_path, ruta_carpeta, soporte_pago_path, egreso_path, fecha_pago)
                VALUES 
                (:entidad_id, :fecha_emision, :estado, :usuario_id, :archivo_path, :ruta_carpeta, :soporte_pago_path, :egreso_path, :fecha_pago)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':entidad_id'        => $datos['entidad_id'],
            ':fecha_emision'     => $datos['fecha_emision'],
            ':estado'            => $datos['estado'], 
            ':usuario_id'        => $datos['usuario_id'],
            ':archivo_path'      => $datos['archivo_path'],    
            ':ruta_carpeta'      => $datos['ruta_carpeta'],    
            ':soporte_pago_path' => $datos['soporte_pago_path'],
            ':egreso_path'       => $datos['egreso_path'],
            ':fecha_pago'        => $datos['fecha_pago']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function guardarVenta($datos) {
        $sql = "INSERT INTO facturas_venta
                (entidad_id, fecha_emision, estado, usuario_id, archivo_path, ruta_carpeta, soporte_pago_path, egreso_path, fecha_pago)
                VALUES 
                (:entidad_id, :fecha_emision, :estado, :usuario_id, :archivo_path, :ruta_carpeta, :soporte_pago_path, :egreso_path, :fecha_pago)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':entidad_id'        => $datos['entidad_id'],
            ':fecha_emision'     => $datos['fecha_emision'],
            ':estado'            => $datos['estado'], 
            ':usuario_id'        => $datos['usuario_id'],
            ':archivo_path'      => $datos['archivo_path'],    
            ':ruta_carpeta'      => $datos['ruta_carpeta'],    
            ':soporte_pago_path' => $datos['soporte_pago_path'],
            ':egreso_path'       => $datos['egreso_path'],
            ':fecha_pago'        => $datos['fecha_pago']
        ]);

        return $this->db->lastInsertId();
    }

    public function actualizarRutas($id, $archivo, $carpeta, $tipo, $soporte = null, $egreso = null) {
        $tabla = ($tipo === 'compra') ? 'facturas_compra' : 'facturas_venta';
        $sql = "UPDATE $tabla SET 
                archivo_path = :archivo, 
                ruta_carpeta = :carpeta, 
                soporte_pago_path = :soporte, 
                egreso_path = :egreso 
                WHERE id = :id"; 
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':archivo' => $archivo,
            ':carpeta' => $carpeta,
            ':soporte' => $soporte,
            ':egreso'  => $egreso,
            ':id'      => $id
        ]);
    }

public function obtenerCarteraPendiente() {
     $sql = "SELECT id, entidad_id, fecha_emision, 'cobro' as tipo_cartera, archivo_path, ruta_carpeta 
            FROM facturas_venta WHERE estado = 'pendiente'
            UNION ALL
            SELECT id, entidad_id, fecha_emision, 'pago' as tipo_cartera, archivo_path, ruta_carpeta 
            FROM facturas_compra WHERE estado = 'pendiente'
            ORDER BY fecha_emision ASC";  
            
    return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
}

public function ultimoFolio() {
$sql = "SELECT * FROM (
                SELECT id, entidad_id, fecha_emision, estado, archivo_path, ruta_carpeta, soporte_pago_path, egreso_path, 'compra' as tipo_origen 
                FROM facturas_compra
                UNION ALL
                SELECT id, entidad_id, fecha_emision, estado, archivo_path, ruta_carpeta, soporte_pago_path, egreso_path, 'venta' as tipo_origen 
                FROM facturas_venta
            ) AS todas
            ORDER BY fecha_emision DESC, id DESC 
            LIMIT 1";
            
    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return null;
    }

    



    }
}
