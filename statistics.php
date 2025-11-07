<?php
// Start a session if one doesn't exist so we can check admin status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';

/**
 * Track a statistical event
 * 
 * @param string $event_type Type of event (download, page_view, etc.)
 * @param string $event_data Additional event data
 * @return bool Success status
 */
function track_event($event_type, $event_data = '')
{
    // Don't track statistics for logged in admins
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return false;
    }

    $db = get_db_connection();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Only record the first occurrence of an event for each IP and event data
    $exists_stmt = $db->prepare('SELECT 1 FROM statistics WHERE event_type = ? AND event_data = ? AND ip_address = ? LIMIT 1');
    $exists_stmt->bind_param('sss', $event_type, $event_data, $ip_address);
    $exists_stmt->execute();
    $exists_result = $exists_stmt->get_result();
    if ($exists_result->num_rows > 0) {
        $exists_stmt->close();
        return false;
    }
    $exists_stmt->close();

    $country_code = null;

    // Check if we already have this IP's country code in our database
    $check_stmt = $db->prepare('SELECT country_code FROM statistics WHERE ip_address = ? AND country_code IS NOT NULL AND country_code != "" LIMIT 1');
    $check_stmt->bind_param('s', $ip_address);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // We already have this IP's country code
        $row = $check_result->fetch_assoc();
        $country_code = $row['country_code'];
        $check_stmt->close();
    } else {
        $check_stmt->close();

        // New IP or no country code yet, use cURL to contact the API
        if (function_exists('curl_init')) {
            $ch = curl_init("https://ipinfo.io/{$ip_address}/country");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ArgoSalesTracker/1.0');
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 200 && !empty($response)) {
                $country_code = trim($response);
            }
        }
    }

    // Insert event
    $stmt = $db->prepare('INSERT INTO statistics (event_type, event_data, ip_address, user_agent, country_code) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $event_type, $event_data, $ip_address, $user_agent, $country_code);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Track a page view
 *
 * @param string $page The page being viewed (e.g., 'homepage', 'download', 'documentation')
 * @return bool Success status
 */
function track_page_view($page)
{
    return track_event('page_view', $page);
}

/**
 * Track a referral visit from a source parameter
 *
 * @param string $source_code The source code from URL parameter (e.g., 'google-ad', 'twitter-sponsor')
 * @param string $page_url The current page URL
 * @return bool Success status
 */
function track_referral_visit($source_code, $page_url = '')
{
    // Don't track statistics for logged in admins
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return false;
    }

    $db = get_db_connection();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Store source in session for conversion tracking
    if (!isset($_SESSION['referral_source'])) {
        $_SESSION['referral_source'] = $source_code;
    }

    // Check if this IP already visited from this source today
    $today_start = date('Y-m-d 00:00:00');
    $exists_stmt = $db->prepare('SELECT 1 FROM referral_visits WHERE source_code = ? AND ip_address = ? AND visited_at >= ? LIMIT 1');
    $exists_stmt->bind_param('sss', $source_code, $ip_address, $today_start);
    $exists_stmt->execute();
    $exists_result = $exists_stmt->get_result();
    if ($exists_result->num_rows > 0) {
        $exists_stmt->close();
        return false; // Already tracked this IP for this source today
    }
    $exists_stmt->close();

    $country_code = null;

    // Check if we already have this IP's country code
    $check_stmt = $db->prepare('SELECT country_code FROM referral_visits WHERE ip_address = ? AND country_code IS NOT NULL AND country_code != "" LIMIT 1');
    $check_stmt->bind_param('s', $ip_address);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();
        $country_code = $row['country_code'];
        $check_stmt->close();
    } else {
        $check_stmt->close();

        // New IP, get country code from API
        if (function_exists('curl_init')) {
            $ch = curl_init("https://ipinfo.io/{$ip_address}/country");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ArgoSalesTracker/1.0');
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 200 && !empty($response)) {
                $country_code = trim($response);
            }
        }
    }

    // Insert referral visit
    $stmt = $db->prepare('INSERT INTO referral_visits (source_code, page_url, ip_address, user_agent, country_code) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $source_code, $page_url, $ip_address, $user_agent, $country_code);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Mark a referral visit as converted (when a license is purchased)
 *
 * @param string $license_key The purchased license key
 * @return bool Success status
 */
function mark_referral_conversion($license_key)
{
    // Check if there's a referral source in the session
    if (!isset($_SESSION['referral_source'])) {
        return false;
    }

    $db = get_db_connection();
    $source_code = $_SESSION['referral_source'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Update the most recent visit from this IP and source to mark it as converted
    $stmt = $db->prepare('UPDATE referral_visits SET converted = 1, license_key = ? WHERE source_code = ? AND ip_address = ? AND converted = 0 ORDER BY visited_at DESC LIMIT 1');
    $stmt->bind_param('sss', $license_key, $source_code, $ip_address);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}
