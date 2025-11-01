<?php
date_default_timezone_set('UTC');
require_once __DIR__ . '/totp.php';

/**
 * Get user by username (case-insensitive)
 * 
 * @param string $username Username
 * @return array|null User data or null if not found
 */
function get_user_by_username($username)
{
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT * FROM admin_users WHERE LOWER(username) = LOWER(?)');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

/**
 * Save a 2FA secret for a user
 * 
 * @param string $username Username
 * @param string $secret TOTP secret
 * @return bool Success status
 */
function save_2fa_secret($username, $secret)
{
    $user = get_user_by_username($username);
    if (!$user) return false;

    try {
        $db = get_db_connection();
        $stmt = $db->prepare('UPDATE admin_users SET two_factor_secret = ?, two_factor_enabled = 1 WHERE username = ?');
        $stmt->bind_param('ss', $secret, $user['username']);
        $success = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
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
    $user = get_user_by_username($username);
    if (!$user) return false;

    try {
        $db = get_db_connection();
        $stmt = $db->prepare('UPDATE admin_users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE username = ?');
        $stmt->bind_param('s', $user['username']);
        $success = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
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
    try {
        $user = get_user_by_username($username);
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
    try {
        $user = get_user_by_username($username);
        return $user && $user['two_factor_enabled'] == 1;
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
    return !empty($secret) && TOTP::verify($secret, $code);
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
    $params = [
        'secret' => $secret,
        'issuer' => $issuer,
        'algorithm' => 'SHA1',
        'digits' => '6',
        'period' => '30'
    ];

    return "otpauth://totp/" . urlencode($issuer) . ":" . urlencode($username) . "?" . http_build_query($params);
}
