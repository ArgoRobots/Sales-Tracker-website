<?php
function get_db_connection()
{
    $host = "localhost";
    $user = "root";           // default root user
    $pass = "";               // leave empty for XAMPP (unless you set one)
    $db   = "argo_sales_tracker"; // your database name

    // Create connection
    $conn = new mysqli($host, $user, $pass, $db);

    // Check connection
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    return $conn;
}
?>
