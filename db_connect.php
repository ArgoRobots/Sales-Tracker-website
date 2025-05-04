<?php

/**
 * Database connection function for MySQL
 */
function get_db_connection()
{
    $host = 'localhost';
    $username = 'argorobots_Admin';
    $password = 'qDzI>-#HT6px8Xi';
    $database = 'argorobots_argo_community';

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
