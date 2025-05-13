<?php
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
    $db = get_db_connection();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Get country code from IP address if GeoIP extension is available
    $country_code = null;
    if (function_exists('geoip_country_code_by_name')) {
        $country_code = geoip_country_code_by_name($ip_address);
    }

    // Insert event
    $stmt = $db->prepare('INSERT INTO statistics (event_type, event_data, ip_address, user_agent, country_code) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $event_type, $event_data, $ip_address, $user_agent, $country_code);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Get count of events by type
 * 
 * @param string $event_type Type of event to count
 * @param string $event_data Optional event data to filter by
 * @return int Event count
 */
function get_event_count($event_type, $event_data = null)
{
    $db = get_db_connection();

    if ($event_data === null) {
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM statistics WHERE event_type = ?');
        $stmt->bind_param('s', $event_type);
    } else {
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM statistics WHERE event_type = ? AND event_data = ?');
        $stmt->bind_param('ss', $event_type, $event_data);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['count'];
}

/**
 * Get downloads by time period
 * 
 * @param string $period 'day', 'week', 'month', 'year'
 * @return array Period statistics
 */
function get_downloads_by_period($period = 'month')
{
    $db = get_db_connection();

    $sql_period = '';
    switch ($period) {
        case 'day':
            $sql_period = 'DATE(created_at)';
            break;
        case 'week':
            $sql_period = 'YEARWEEK(created_at)';
            break;
        case 'month':
            $sql_period = 'DATE_FORMAT(created_at, "%Y-%m")';
            break;
        case 'year':
            $sql_period = 'YEAR(created_at)';
            break;
    }

    $stmt = $db->prepare("
        SELECT 
            $sql_period as period, 
            COUNT(*) as count 
        FROM statistics 
        WHERE event_type = 'download' 
        GROUP BY period 
        ORDER BY period DESC 
        LIMIT 12
    ");

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();

    return $data;
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
 * Get page views by time period
 * 
 * @param string $period 'day', 'week', 'month', 'year'
 * @return array Period statistics
 */
function get_page_views_by_period($period = 'month')
{
    $db = get_db_connection();

    $sql_period = '';
    switch ($period) {
        case 'day':
            $sql_period = 'DATE(created_at)';
            break;
        case 'week':
            $sql_period = 'YEARWEEK(created_at)';
            break;
        case 'month':
            $sql_period = 'DATE_FORMAT(created_at, "%Y-%m")';
            break;
        case 'year':
            $sql_period = 'YEAR(created_at)';
            break;
    }

    $stmt = $db->prepare("
        SELECT 
            $sql_period as period, 
            COUNT(*) as count 
        FROM statistics 
        WHERE event_type = 'page_view' 
        GROUP BY period 
        ORDER BY period DESC 
        LIMIT 12
    ");

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();

    return $data;
}

/**
 * Get user geographic distribution
 * 
 * @param int $limit Maximum number of countries to return
 * @return array Country statistics
 */
function get_user_countries($limit = 10)
{
    $db = get_db_connection();

    $query = "
        SELECT 
            country_code,
            COUNT(*) as count
        FROM statistics
        WHERE country_code IS NOT NULL AND country_code != ''
        GROUP BY country_code
        ORDER BY count DESC
        LIMIT ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();

    return $data;
}

/**
 * Get most active community users
 * 
 * @param int $limit Maximum number of users to return
 * @return array User statistics
 */
function get_most_active_users($limit = 5)
{
    $db = get_db_connection();

    $query = "
        SELECT 
            u.username,
            u.email,
            COUNT(DISTINCT p.id) as post_count,
            COUNT(DISTINCT c.id) as comment_count,
            SUM(p.views) as total_views,
            (COUNT(DISTINCT p.id) * 2 + COUNT(DISTINCT c.id)) as activity_score
        FROM community_users u
        LEFT JOIN community_posts p ON u.id = p.user_id
        LEFT JOIN community_comments c ON u.id = c.user_id
        GROUP BY u.id, u.username, u.email
        ORDER BY activity_score DESC
        LIMIT ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();

    return $data;
}
