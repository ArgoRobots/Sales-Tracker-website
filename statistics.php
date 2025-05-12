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

    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Get user agent
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Insert event
    $stmt = $db->prepare('INSERT INTO statistics (event_type, event_data, ip_address, user_agent) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $event_type, $event_data, $ip_address, $user_agent);
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
