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
                (sello_alt, entidad_id, fecha_emision, estado, usuario_id, archivo_path, ruta_carpeta, soporte_pago_path, egreso_path, fecha_pago)
                VALUES 
                (:sello_alt, :entidad_id, :fecha_emision, :estado, :usuario_id, :archivo_path, :ruta_carpeta, :soporte_pago_path, :egreso_path, :fecha_pago)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':sello_alt'         => $datos['sello_alt'],
            ':entidad_id'        => $datos['entidad_id'],
            ':fecha_emision'     => $datos['fecha_emision'],
            ':estado'            => $datos['estado'], 
            ':usuario_id'        => $datos['usuario_id'],
            ':archivo_path'      => $datos['archivo_path'],    
            ':ruta_carpeta'      => $datos['ruta_carpeta'],    
            ':soporte_pago_path' => $datos['soporte_pago_path'],
            ':egreso_path'       => $datos['egreso_path'],
            ':fecha_pago'        => $datos['fecha_pago']
        ]) ? $this->db->lastInsertId() : false;
    }

    public function guardarVenta($datos) {
        $sql = "INSERT INTO facturas_venta
                (sello_alt, entidad_id, fecha_emision, estado, usuario_id, archivo_path, ruta_carpeta, soporte_pago_path, egreso_path, fecha_pago)
                VALUES 
                (:sello_alt, :entidad_id, :fecha_emision, :estado, :usuario_id, :archivo_path, :ruta_carpeta, :soporte_pago_path, :egreso_path, :fecha_pago)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':sello_alt'         => $datos['sello_alt'],
            ':entidad_id'        => $datos['entidad_id'],
            ':fecha_emision'     => $datos['fecha_emision'],
            ':estado'            => $datos['estado'], 
            ':usuario_id'        => $datos['usuario_id'],
            ':archivo_path'      => $datos['archivo_path'],    
            ':ruta_carpeta'      => $datos['ruta_carpeta'],    
            ':soporte_pago_path' => $datos['soporte_pago_path'],
            ':egreso_path'       => $datos['egreso_path'],
            ':fecha_pago'        => $datos['fecha_pago']
        ]) ? $this->db->lastInsertId() : false;
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

    public function ultimoFolio() {
        $sql = "SELECT * FROM (
                    SELECT id, sello_alt, entidad_id, fecha_emision, estado, 'compra' as tipo_origen FROM facturas_compra
                    UNION ALL
                    SELECT id, sello_alt, entidad_id, fecha_emision, estado, 'venta' as tipo_origen FROM facturas_venta
                ) AS todas ORDER BY fecha_emision DESC, id DESC LIMIT 1";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarConFiltros($nit, $mes, $tipo) {
        $where = " WHERE 1=1 ";
        $params = [];

        if ($nit) {
            $where .= " AND e.nit_cedula = :nit ";
            $params[':nit'] = $nit;
        }
        if ($mes) {
            $where .= " AND MONTH(f.fecha_emision) = :mes ";
            $params[':mes'] = $mes;
        }

        $sql = "";
        if ($tipo === 'COMPRA' || $tipo === 'todos') {
            $sql .= "(SELECT f.id, f.fecha_emision, 'COMPRA' as tipo, f.estado, f.ruta_carpeta, f.archivo_path, f.soporte_pago_path, f.egreso_path, e.nit_cedula, e.nombre 
                      FROM facturas_compra f 
                      LEFT JOIN entidades e ON f.entidad_id = e.id $where)";
        }
        if ($tipo === 'todos') $sql .= " UNION ";
        if ($tipo === 'VENTA' || $tipo === 'todos') {
            $sql .= "(SELECT f.id, f.fecha_emision, 'VENTA' as tipo, f.estado, f.ruta_carpeta, f.archivo_path, f.soporte_pago_path, f.egreso_path, e.nit_cedula, e.nombre 
                      FROM facturas_venta f 
                      LEFT JOIN entidades e ON f.entidad_id = e.id $where)";
        }

        $sql .= " ORDER BY fecha_emision DESC LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}