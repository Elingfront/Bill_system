<?php
require 'config/db.php';
if ($pdo) {
    echo "¡Oye que bien, lograste conectarte a la base de datos, felicidades!";
} else {
    echo "no te conectaste bru";
};
?>