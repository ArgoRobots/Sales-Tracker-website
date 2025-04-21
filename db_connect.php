<?php
function get_db_connection()
{
    $db_path = '/home/argorobots/public_html/database/license_db.sqlite';
    $schema_path = __DIR__ . '/database/schema.sql';

    try {
        $db = new SQLite3($db_path);
        $db->exec('PRAGMA foreign_keys = ON;');

        // Set permissions
        if (file_exists($db_path)) {
            chmod($db_path, 0666);
        }

        // Read schema from file and execute it
        if (!file_exists($schema_path)) {
            error_log("Schema file not found: $schema_path");
            die("Schema file not found: $schema_path");
        }

        $schema_sql = file_get_contents($schema_path);
        if ($schema_sql === false) {
            error_log("Failed to read schema file: $schema_path");
            die("Failed to read schema file: $schema_path");
        }

        // Execute schema SQL
        $db->exec($schema_sql);

        return $db;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Database connection failed: " . $e->getMessage());
    }
}
