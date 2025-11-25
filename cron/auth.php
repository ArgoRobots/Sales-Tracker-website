<?php
/**
 * Cron Page Authentication
 *
 * Provides TOTP-based authentication for the cron management UI.
 * Reuses the admin 2FA system to avoid code duplication.
 */

session_start();

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../admin/settings/totp.php';
require_once __DIR__ . '/../admin/settings/2fa.php';

/**
 * Check if user is authenticated for cron access
 *
 * @return bool True if authenticated
 */
function is_cron_authenticated() {
    return isset($_SESSION['cron_authenticated']) && $_SESSION['cron_authenticated'] === true;
}

/**
 * Set cron authentication status
 *
 * @param bool $authenticated Authentication status
 */
function set_cron_authenticated($authenticated) {
    $_SESSION['cron_authenticated'] = $authenticated;
    if ($authenticated) {
        $_SESSION['cron_auth_time'] = time();
    }
}

/**
 * Clear cron authentication
 */
function clear_cron_authentication() {
    unset($_SESSION['cron_authenticated']);
    unset($_SESSION['cron_auth_time']);
}

/**
 * Check if cron authentication has expired (30 minutes)
 *
 * @return bool True if expired
 */
function is_cron_auth_expired() {
    if (!isset($_SESSION['cron_auth_time'])) {
        return true;
    }
    $timeout = 30 * 60; // 30 minutes
    return (time() - $_SESSION['cron_auth_time']) > $timeout;
}

/**
 * Get admin users who have 2FA enabled (for TOTP verification)
 *
 * @return array Array of admin usernames with 2FA enabled
 */
function get_2fa_enabled_admins() {
    $db = get_db_connection();
    $result = $db->query('SELECT username FROM admin_users WHERE two_factor_enabled = 1');
    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row['username'];
    }
    return $admins;
}

/**
 * Verify TOTP code against any admin with 2FA enabled
 *
 * @param string $code TOTP code to verify
 * @return bool True if code is valid for any admin
 */
function verify_cron_totp($code) {
    $admins = get_2fa_enabled_admins();

    foreach ($admins as $username) {
        $secret = get_2fa_secret($username);
        if ($secret && verify_2fa_code($secret, $code)) {
            return true;
        }
    }

    return false;
}
