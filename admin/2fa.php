<?php
date_default_timezone_set('UTC');
require_once __DIR__ . '/totp.php';

/**
 * Save a 2FA secret for a user
 * 
 * @param string $username Username
 * @param string $secret TOTP secret
 * @return bool Success status
 */
function save_2fa_secret($username, $secret)
{
    $db = get_db_connection();

    try {
        // Get the correct case username
        $stmt = $db->prepare('SELECT username FROM admin_users WHERE LOWER(username) = LOWER(?)');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return false;
        }

        // Update the user record
        $stmt = $db->prepare('UPDATE admin_users SET two_factor_secret = ?, two_factor_enabled = 1 WHERE username = ?');
        $stmt->bind_param('ss', $secret, $user['username']);
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        return $affected_rows > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Disable 2FA for a user
 * 
 * @param string $username Username
 * @return bool Success status
 */
function disable_2fa($username)
{
    $db = get_db_connection();

    try {
        // Get the correct case username
        $stmt = $db->prepare('SELECT username FROM admin_users WHERE LOWER(username) = LOWER(?)');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return false;
        }

        // Update the user record
        $stmt = $db->prepare('UPDATE admin_users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE username = ?');
        $stmt->bind_param('s', $user['username']);
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        return $affected_rows > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get a user's 2FA secret
 * 
 * @param string $username Username
 * @return string|null 2FA secret or null if not found
 */
function get_2fa_secret($username)
{
    $db = get_db_connection();

    try {
        // Get the correct case username
        $stmt = $db->prepare('SELECT username, two_factor_secret FROM admin_users WHERE LOWER(username) = LOWER(?)');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user ? $user['two_factor_secret'] : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Check if 2FA is enabled for a user
 * 
 * @param string $username Username
 * @return bool True if 2FA is enabled
 */
function is_2fa_enabled($username)
{
    $db = get_db_connection();

    try {
        $stmt = $db->prepare('SELECT two_factor_enabled FROM admin_users WHERE LOWER(username) = LOWER(?)');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row && $row['two_factor_enabled'] == 1;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Generate a new 2FA secret
 * 
 * @return string New 2FA secret
 */
function generate_2fa_secret()
{
    return TOTP::generateSecret();
}

/**
 * Verify a 2FA code
 * 
 * @param string $secret 2FA secret
 * @param string $code Code to verify
 * @return bool True if code is valid
 */
function verify_2fa_code($secret, $code)
{
    if (empty($secret)) {
        return false;
    }

    return TOTP::verify($secret, $code);
}

/**
 * Get a QR code URL for 2FA setup
 * 
 * @param string $username Username
 * @param string $secret 2FA secret
 * @param string $issuer Issuer name
 * @return string QR code URL
 */
function get_qr_code_url($username, $secret, $issuer = 'Argo Sales Tracker Admin')
{
    return "otpauth://totp/" . urlencode($issuer) . ":" . urlencode($username) . "?secret=" . $secret . "&issuer=" . urlencode($issuer) . "&algorithm=SHA1&digits=6&period=30";
}
