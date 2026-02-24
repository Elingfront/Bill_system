<?php
namespace App\Controllers;

require_once dirname(__DIR__) . '/Models/Factura.php';
use App\Models\Factura;

class DashboardController {
    
    public static function obtenerResumen() {
        $modelo = new Factura();
        $ultimo = $modelo->ultimoFolio(); // El que le faltaba

        if (!$ultimo) return null;

        return [
            'datos' => $ultimo,
            'archivos' => [
                'factura' => $ultimo['archivo_path'],
                'soporte' => $ultimo['soporte_pago_path'],
                'egreso'  => $ultimo['egreso_path']
            ]
        ];
    }
}