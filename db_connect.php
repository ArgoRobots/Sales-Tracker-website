<?php

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create PDO connection for files that need it
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("PDO connection error: " . $e->getMessage());
    $pdo = null;
}

/**
 * Database connection function for MySQL (mysqli)
 */
function get_db_connection()
{
    $host = $_ENV['DB_HOST'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    $database = $_ENV['DB_NAME'];

    // Create new connection
    $db = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($db->connect_error) {
        error_log("Database connection error: " . $db->connect_error);
        die("Database connection failed: " . $db->connect_error);
    }

    // Set character set
    $db->set_charset("utf8mb4");

    return $db;
}
