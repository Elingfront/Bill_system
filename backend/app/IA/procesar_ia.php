<?php
/**
 * procesar_ia.php — ALT CONFECCIONES S.A.S.
 * UBICACIÓN: backend/app/IA/procesar_ia.php
 */

use Smalot\PdfParser\Parser;
use App\Core\Database;

// 1. CONTROL DE ERRORES PARA EVITAR EL 500
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla para no dañar el JSON
if (ob_get_length()) ob_end_clean();
ob_start();

header('Content-Type: application/json');

try {
    // 2. RUTAS CORREGIDAS SEGÚN TU WAMP
    // Estamos en: backend/app/IA/
    // vendor está en: / (raíz) -> Subir 3 niveles
    $basePath = dirname(__DIR__, 3); 
    $autoload = $basePath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    
    // Database.php está en: backend/Core/Database.php 
    // Desde aquí (backend/app/IA/) hay que subir 2 niveles y entrar a Core
    $dbPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Database.php';

    if (!file_exists($autoload)) throw new Exception("No se encontró vendor/autoload.php en: $autoload");
    require_once $autoload;

    if (!file_exists($dbPath)) throw new Exception("No se encontró Database.php en: $dbPath");
    require_once $dbPath;

    // Cargar variables de entorno (.env en la raíz)
    $dotenv = Dotenv\Dotenv::createImmutable($basePath);
    $dotenv->safeLoad();

    // --- FUNCIÓN DE LLAMADA A GEMINI ---
    function llamarIA($textoPDF, $tipo) {
        $apiKey = getenv('GEMINI_API_KEY');
        if (!$apiKey) throw new Exception("Falta la API KEY en el archivo .env");

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        $prompt = "Actúa como asistente contable de ALT CONFECCIONES. 
        Analiza este texto de factura y extrae en JSON:
        - 'consecutivo': El número de factura. Omite resoluciones de la DIAN.
        - 'nit': NIT del " . ($tipo == 'Venta' ? 'comprador' : 'vendedor') . " (solo números).
        - 'nombre': Nombre de la empresa.
        Texto: $textoPDF
        Respuesta en JSON puro: {\"consecutivo\":\"\",\"nit\":\"\",\"nombre\":\"\"}";

        $payload = json_encode([
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['response_mime_type' => 'application/json', 'temperature' => 0.1]
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) throw new Exception("Error API (HTTP $httpCode)");

        $resRaw = json_decode($response, true);
        $textoIA = $resRaw['candidates'][0]['content']['parts'][0]['text'];
        return json_decode(trim($textoIA), true);
    }

    // --- PROCESO PRINCIPAL ---
    if (!isset($_FILES['archivo_factura'])) throw new Exception("No subiste ningún archivo.");

    $db = Database::getConnection(); 
    $parser = new Parser();
    $pdf = $parser->parseFile($_FILES['archivo_factura']['tmp_name']);
    $texto = mb_substr(trim($pdf->getText()), 0, 4000);

    $tipo = $_POST['tipo_seleccionado'] ?? 'Compra';
    $datosIA = llamarIA($texto, $tipo);

    // Limpieza de NIT (solo números)
    $nitLimpio = preg_replace('/[^\d]/', '', $datosIA['nit']);

    // Buscar si el tercero ya existe en la base de datos
    $stmt = $db->prepare("SELECT id, nombre FROM entidades WHERE nit_cedula = ? LIMIT 1");
    $stmt->execute([$nitLimpio]);
    $entidad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entidad) {
        // Si no existe, lo creamos
        $rol = ($tipo === 'Venta') ? 'Cliente' : 'Proveedor';
        $nombreFinal = strtoupper($datosIA['nombre']);
        $ins = $db->prepare("INSERT INTO entidades (nombre, nit_cedula, tipo) VALUES (?, ?, ?)");
        $ins->execute([$nombreFinal, $nitLimpio, $rol]);
        $entidadId = $db->lastInsertId();
    } else {
        $entidadId = $entidad['id'];
        $nombreFinal = $entidad['nombre'];
    }

    // RESPUESTA EXITOSA
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'datos' => [
            'consecutivo' => $datosIA['consecutivo'],
            'nit' => $nitLimpio,
            'nombre' => $nombreFinal,
            'entidad_id' => $entidadId
        ]
    ]);

} catch (Exception $e) {
    if (ob_get_length()) ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}