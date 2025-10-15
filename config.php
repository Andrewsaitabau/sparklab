<?php
// config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Show all errors (important while developing)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database config
$DB_HOST = '127.0.0.1';   // or 'localhost'
$DB_NAME = 'sparklab';    // must match database name in phpMyAdmin
$DB_USER = 'root';        // XAMPP default
$DB_PASS = '';            // XAMPP default (blank)

// Connect using PDO
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}
?>