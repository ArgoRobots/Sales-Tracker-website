<?php

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

/**
 * Database connection function for MySQL
 */
function get_db_connection()
{
    $host = $_ENV['DB_HOST'] ?? 'localhost';
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
