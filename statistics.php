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
