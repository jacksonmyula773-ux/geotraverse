<?php
/**
 * Database Configuration
 * Kwa sasa haitumiki, lakini kwa baadae utakapounganisha database
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'geotraverse');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        return null;
    }
}
?>