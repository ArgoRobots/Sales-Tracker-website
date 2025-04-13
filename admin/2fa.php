<?php

date_default_timezone_set('UTC');

function save_2fa_secret($username, $secret) {
    $db = get_db_connection();
    $username = strtolower(trim($username)); // Normalize username
    
    error_log("SAVING 2FA SECRET:");
    error_log("Username: " . $username);
    error_log("Secret: " . $secret);

    try {
        // First check if the user exists and get their actual username with proper case
        $stmt = $db->prepare('SELECT username FROM admin_users WHERE LOWER(username) = LOWER(:username)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if (!$user) {
            error_log("User $username does not exist in database");
            return false;
        }
        
        // Use the actual username from the database
        $actual_username = $user['username'];
        error_log("Actual username from DB: " . $actual_username);
        
        // Make sure the two_factor_secret column exists
        verify_db_schema();
        
        // Update with explicit column names
        $stmt = $db->prepare("
            UPDATE admin_users 
            SET two_factor_secret = :secret, 
                two_factor_enabled = 1 
            WHERE username = :username
        ");
        $stmt->bindValue(':secret', $secret, SQLITE3_TEXT);
        $stmt->bindValue(':username', $actual_username, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        // Log the number of affected rows
        $changes = $db->changes();
        error_log("Rows updated: " . $changes);
        
        if ($changes == 0) {
            error_log("WARNING: No rows were updated!");
            
            // Try direct update as fallback
            $db->exec("UPDATE admin_users SET two_factor_secret = '$secret', two_factor_enabled = 1 WHERE username = '$actual_username'");
            error_log("Fallback direct update executed. Rows: " . $db->changes());
        }
        
        // Verify the secret was saved by using direct query
        $verify_stmt = $db->prepare("SELECT two_factor_secret FROM admin_users WHERE username = :username");
        $verify_stmt->bindValue(':username', $actual_username, SQLITE3_TEXT);
        $verify_result = $verify_stmt->execute();
        $verify_row = $verify_result->fetchArray(SQLITE3_ASSOC);
        
        if (!$verify_row || $verify_row['two_factor_secret'] !== $secret) {
            error_log("VERIFICATION FAILED: Secret was not saved correctly");
            error_log("Expected: " . $secret);
            error_log("Got: " . ($verify_row ? $verify_row['two_factor_secret'] : "NULL"));
            return false;
        }
        
        error_log("SECRET SAVED SUCCESSFULLY!");
        return true;
    } catch (Exception $e) {
        error_log("Error saving secret: " . $e->getMessage());
        return false;
    }
}

function disable_2fa($username) {
    $db = get_db_connection();
    
    try {
        $stmt = $db->prepare('UPDATE admin_users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        
        return $stmt->execute();
    } catch (Exception $e) {
        error_log('Error disabling 2FA: ' . $e->getMessage());
        return false;
    }
}

function get_2fa_secret($username) {
    $db = get_db_connection();
    
    // Normalize username to match save_2fa_secret function
    $username = strtolower(trim($username));
    
    try {
        // First get the actual username with correct case
        $find_stmt = $db->prepare('SELECT username FROM admin_users WHERE LOWER(username) = LOWER(:username)');
        $find_stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $find_result = $find_stmt->execute();
        $user = $find_result->fetchArray(SQLITE3_ASSOC);
        
        if (!$user) {
            error_log("GET 2FA SECRET - User not found: " . $username);
            return null;
        }
        
        $actual_username = $user['username'];
        error_log("GET 2FA SECRET - Found user: " . $actual_username);
        
        // Now get the secret using the exact username from the database
        $stmt = $db->prepare('SELECT two_factor_secret FROM admin_users WHERE username = :username');
        $stmt->bindValue(':username', $actual_username, SQLITE3_TEXT);
        
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        // Also try direct query for debugging
        $direct_result = $db->query("SELECT two_factor_secret FROM admin_users WHERE username = '$actual_username'");
        $direct_row = $direct_result ? $direct_result->fetchArray(SQLITE3_ASSOC) : null;
        
        // Add debug logging
        error_log("GET 2FA SECRET:");
        error_log("Username: " . $username);
        error_log("Actual username: " . $actual_username);
        error_log("Query executed: " . ($row ? "SUCCESS" : "FAILURE"));
        error_log("Retrieved secret: " . ($row ? $row['two_factor_secret'] : "EMPTY"));
        error_log("Direct query result: " . ($direct_row ? $direct_row['two_factor_secret'] : "EMPTY"));
        
        return $row ? $row['two_factor_secret'] : null;
    } catch (Exception $e) {
        error_log('Error getting 2FA secret: ' . $e->getMessage());
        return null;
    }
}

function is_2fa_enabled($username) {
    $db = get_db_connection();
    
    try {
        $stmt = $db->prepare('SELECT two_factor_enabled FROM admin_users WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        return $result && $result['two_factor_enabled'] == 1;
    } catch (Exception $e) {
        error_log('Error checking if 2FA is enabled: ' . $e->getMessage());
        return false;
    }
}

function generate_2fa_secret() {
    require_once __DIR__ . '/totp.php';
    return BasicTOTP::generateSecret();
}

function verify_2fa_code($secret, $code, $discrepancy = 4, $debug = true) {
    if (empty($secret)) {
        error_log("2FA verification failed: Empty secret");
        return false;
    }
    
    // Log request information
    error_log("Server timezone: " . date_default_timezone_get());
    error_log("Server time: " . date('Y-m-d H:i:s'));
    error_log("UTC time: " . gmdate('Y-m-d H:i:s'));
    error_log("TOTP verification - Secret: $secret, Code: $code");
    
    // Load our TOTP implementation
    require_once __DIR__ . '/totp.php';
    
    // Use wide window to account for time drift
    return BasicTOTP::verify($secret, $code, 10);
}

function get_qr_code_url($username, $secret, $issuer = 'Argo Sales Tracker Admin') {
    return "otpauth://totp/".urlencode($issuer).":".urlencode($username)."?secret=".$secret."&issuer=".urlencode($issuer)."&algorithm=SHA1&digits=6&period=30";
}

function verify_db_schema() {
    $db = get_db_connection();
    
    try {
        // Check if columns exist
        $result = $db->query("PRAGMA table_info(admin_users)");
        $has_secret = false;
        $has_enabled = false;
        $columns = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $columns[] = $row['name'];
            if ($row['name'] == 'two_factor_secret') $has_secret = true;
            if ($row['name'] == 'two_factor_enabled') $has_enabled = true;
        }
        
        error_log("Current admin_users columns: " . implode(", ", $columns));
        
        // Add missing columns
        if (!$has_secret) {
            $db->exec("ALTER TABLE admin_users ADD COLUMN two_factor_secret TEXT");
            error_log("Added missing two_factor_secret column");
        }
        
        if (!$has_enabled) {
            $db->exec("ALTER TABLE admin_users ADD COLUMN two_factor_enabled INTEGER DEFAULT 0");
            error_log("Added missing two_factor_enabled column");
        }
        
        // Verify admin_users table has data
        $count_result = $db->query("SELECT COUNT(*) as count FROM admin_users");
        $count = $count_result->fetchArray(SQLITE3_ASSOC)['count'];
        error_log("Number of users in admin_users table: " . $count);
        
        // Check if 'admin' user exists
        $admin_check = $db->query("SELECT username FROM admin_users WHERE LOWER(username) = 'admin'");
        $admin_exists = $admin_check->fetchArray(SQLITE3_ASSOC);
        error_log("Admin user exists: " . ($admin_exists ? "YES" : "NO"));
        
        if ($admin_exists) {
            error_log("Admin username exact case: " . $admin_exists['username']);
        }
        
        return $has_secret && $has_enabled;
    } catch (Exception $e) {
        error_log("Schema verification failed: " . $e->getMessage());
        return false;
    }
}
function check_db_permissions() {
    $db_path = '/home/argorobots/public_html/database/license_db.sqlite';
    
    if (!is_writable($db_path)) {
        error_log("DATABASE IS NOT WRITABLE");
        return false;
    }
     error_log("DATABASE WRITABLE");
    
    return true;
}