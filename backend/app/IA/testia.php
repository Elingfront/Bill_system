<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Chequeo de Sistema ALT:</h3>";

$autoload = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
echo "Buscando autoload en: " . $autoload . " <br>";
echo "Estado: " . (file_exists($autoload) ? "✅ EXISTE" : "❌ NO EXISTE") . "<br><br>";

$dbPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Database.php';
echo "Buscando Database en: " . $dbPath . " <br>";
echo "Estado: " . (file_exists($dbPath) ? "✅ EXISTE" : "❌ NO EXISTE") . "<br>";