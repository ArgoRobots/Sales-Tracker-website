<?php
// Database connection file
function get_db_connection() {
    $db_path = __DIR__ . '/database/license_db.sqlite';
    $db_directory = dirname($db_path);
    
    // Create database directory if it doesn't exist
    if (!file_exists($db_directory)) {
        mkdir($db_directory, 0755, true);
    }
    
    $db = new SQLite3($db_path);
    $db->enableExceptions(true);
    
    // Check if tables exist, if not create them
    try {
        // Check if license_keys table exists
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='license_keys'");
        if (!$result->fetchArray()) {
            // Create tables using our schema
            $schema = file_get_contents(__DIR__ . '/database/schema.sql');
            $db->exec($schema);
        }
    } catch (Exception $e) {
        error_log('Database initialization error: ' . $e->getMessage());
        die('Error initializing database. Please check error logs.');
    }
    
    return $db;
}