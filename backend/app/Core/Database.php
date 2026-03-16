<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {

    private static $instance = null;
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {

            try {
                $host = "localhost";
                $db   = "sistema_contable_alt"; 
                $user = "root";
                $pass = ""; 
                self::$instance = new PDO(
                    "mysql:host=$host;dbname=$db;charset=utf8mb4",
                    $user,
                    $pass
                );

                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
