<?php
namespace App\Controllers;

require_once dirname(__DIR__) . '/Models/Factura.php';
use App\Models\Factura;

class DashboardController {
    
     public static function obtenerResumen() {
        $modelo = new Factura();
        $ultimo = $modelo->ultimoFolio();
        if (!$ultimo) return null;

        return [
            'datos' => $ultimo
        ];
    }

     public static function filtrarDocumentos($nit_cedula, $mes, $tipo) {
        $modelo = new Factura();
         return $modelo->buscarConFiltros($nit_cedula, $mes, $tipo);
    }
}