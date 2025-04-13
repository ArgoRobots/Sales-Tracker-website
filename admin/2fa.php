<?php

date_default_timezone_set('UTC');

// Include the improved TOTP implementation
require_once __DIR__ . '/totp.php';

function save_2fa_secret($username, $secret) {
    $db = get_db_connection();
    
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
            $query = "UPDATE admin_users SET two_factor_secret = '" . 
                  $db->escapeString($secret) . "', two_factor_enabled = 1 WHERE username = '" . 
                  $db->escapeString($actual_username) . "'";
            
            error_log("Direct query: " . $query);
            
            $db->exec($query);
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
        // First get the actual username with correct case
        $find_stmt = $db->prepare('SELECT username FROM admin_users WHERE LOWER(username) = LOWER(:username)');
        $find_stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $find_result = $find_stmt->execute();
        $user = $find_result->fetchArray(SQLITE3_ASSOC);
        
        if (!$user) {
            error_log("DISABLE 2FA - User not found: " . $username);
            return false;
        }
        
        $actual_username = $user['username'];
        
        $stmt = $db->prepare('UPDATE admin_users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE username = :username');
        $stmt->bindValue(':username', $actual_username, SQLITE3_TEXT);
        
        $stmt->execute();
        $changes = $db->changes();
        
        error_log("DISABLE 2FA - Rows updated: " . $changes);
        
        return $changes > 0;
    } catch (Exception $e) {
        error_log('Error disabling 2FA: ' . $e->getMessage());
        return false;
    }
}

function get_2fa_secret($username) {
    $db = get_db_connection();
        
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
        $direct_query = "SELECT two_factor_secret FROM admin_users WHERE username = '" . 
                       $db->escapeString($actual_username) . "'";
        
        error_log("Direct query: " . $direct_query);
        
        $direct_result = $db->query($direct_query);
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
        // First get the actual username with correct case
        $find_stmt = $db->prepare('SELECT username FROM admin_users WHERE LOWER(username) = LOWER(:username)');
        $find_stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $find_result = $find_stmt->execute();
        $user = $find_result->fetchArray(SQLITE3_ASSOC);
        
        if (!$user) {
            error_log("IS 2FA ENABLED - User not found: " . $username);
            return false;
        }
        
        $actual_username = $user['username'];
        
        $stmt = $db->prepare('SELECT two_factor_enabled FROM admin_users WHERE username = :username');
        $stmt->bindValue(':username', $actual_username, SQLITE3_TEXT);
        
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        return $result && $result['two_factor_enabled'] == 1;
    } catch (Exception $e) {
        error_log('Error checking if 2FA is enabled: ' . $e->getMessage());
        return false;
    }
}

function generate_2fa_secret() {
    return TOTP::generateSecret();
}

function verify_2fa_code($secret, $code) {
    if (empty($secret)) {
        error_log("2FA verification failed: Empty secret");
        return false;
    }
    
    // Log request information
    error_log("Server timezone: " . date_default_timezone_get());
    error_log("Server time: " . date('Y-m-d H:i:s'));
    error_log("UTC time: " . gmdate('Y-m-d H:i:s'));
    error_log("TOTP verification - Secret: $secret, Code: $code");
    
    // Use the improved TOTP implementation
    return TOTP::verify($secret, $code);
}

function get_qr_code_url($username, $secret, $issuer = 'Argo Sales Tracker Admin') {
    return "otpauth://totp/".urlencode($issuer).":".urlencode($username)."?secret=".$secret."&issuer=".urlencode($issuer)."&algorithm=SHA1&digits=6&period=30";
}

// Enable for easier debugging
if (isset($_GET['2fa_debug'])) {
    // Include debug helpers
    require_once __DIR__ . '/2fa_debug_helpers.php';
    show_debug_if_enabled();
}