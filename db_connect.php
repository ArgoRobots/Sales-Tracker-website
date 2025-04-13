<?php
function get_db_connection() {
    $db_path = '/home/argorobots/public_html/database/license_db.sqlite';
    
    error_log("DB Path: $db_path");
    error_log("DB Directory: " . dirname($db_path));
    error_log("Database exists: " . (file_exists($db_path) ? "Yes" : "No"));
    
    // Check directory permissions
    $db_dir = dirname($db_path);
    error_log("Directory writable: " . (is_writable($db_dir) ? "Yes" : "No"));
    error_log("File writable: " . (file_exists($db_path) && is_writable($db_path) ? "Yes" : "No"));
    
    try {
        $db = new SQLite3($db_path);
        $db->exec('PRAGMA foreign_keys = ON;');
        
        // Set more permissive permissions
        if (file_exists($db_path)) {
            chmod($db_path, 0666);
        }
        
        error_log("Checking if tables exist");
        
        // Create tables if they don't exist
        $db->exec('
            CREATE TABLE IF NOT EXISTS license_keys (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                license_key TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                activated INTEGER DEFAULT 0,
                activation_date DATETIME DEFAULT NULL,
                ip_address TEXT DEFAULT NULL
            );
            
            CREATE TABLE IF NOT EXISTS admin_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                email TEXT,
                two_factor_secret TEXT,
                two_factor_enabled INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME
            );
        ');
        
        return $db;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Database connection failed: " . $e->getMessage());
    }
}