<?php

/**
 * Check if a user has exceeded rate limits
 * 
 * @param int $user_id User ID
 * @param string $action_type Action type ('post' or 'comment')
 * @return bool|string False if within limits, HTML string if limit exceeded
 */
function check_rate_limit($user_id, $action_type)
{
    $is_logged_in = isset($_SESSION['user_id']);
    $role = $is_logged_in ? ($_SESSION['role'] ?? 'user') : '';
    if ($role === 'admin') {
        return false;
    }

    $db = get_db_connection();

    $short_limits = [
        'post' => ['count' => 1, 'minutes' => 5],    // 1 post per 5 minutes
        'comment' => ['count' => 3, 'minutes' => 5]  // 3 comments per 5 minutes
    ];

    $long_limits = [
        'post' => ['count' => 5, 'hours' => 1],      // 5 posts per hour
        'comment' => ['count' => 20, 'hours' => 1]   // 20 comments per hour
    ];

    // Delete any existing rate limit records to provides a fresh start to avoid timezone issues
    $stmt = $db->prepare('DELETE FROM rate_limits WHERE user_id = :user_id AND action_type = :action_type');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':action_type', $action_type, SQLITE3_TEXT);
    $stmt->execute();

    // Now count actual posts/comments in the database from recent timestamps
    $time_limit = ($action_type === 'post') ? 5 : 5; // minutes
    $recent_items_sql = '';

    if ($action_type === 'post') {
        // Count posts in the last X minutes
        $recent_items_sql = "SELECT COUNT(*) as count FROM community_posts 
                             WHERE user_id = :user_id 
                             AND datetime(created_at) > datetime('now', '-{$time_limit} minutes')";
    } else {
        // Count comments in the last X minutes
        $recent_items_sql = "SELECT COUNT(*) as count FROM community_comments 
                             WHERE user_id = :user_id 
                             AND datetime(created_at) > datetime('now', '-{$time_limit} minutes')";
    }

    $stmt = $db->prepare($recent_items_sql);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $recent_count = $row['count'];

    // If the user has made too many posts/comments recently, limit them
    if ($recent_count >= $short_limits[$action_type]['count']) {
        // Calculate wait time based on 5 minutes from now
        $wait_seconds = 300; // 5 minutes in seconds
        return build_rate_limit_message($action_type, $wait_seconds);
    }

    // Check for long-term limits (hourly)
    $hour_limit = ($action_type === 'post') ? 1 : 1; // hours
    $hourly_items_sql = '';

    if ($action_type === 'post') {
        // Count posts in the last X hours
        $hourly_items_sql = "SELECT COUNT(*) as count FROM community_posts 
                             WHERE user_id = :user_id 
                             AND datetime(created_at) > datetime('now', '-{$hour_limit} hours')";
    } else {
        // Count comments in the last X hours
        $hourly_items_sql = "SELECT COUNT(*) as count FROM community_comments 
                             WHERE user_id = :user_id 
                             AND datetime(created_at) > datetime('now', '-{$hour_limit} hours')";
    }

    $stmt = $db->prepare($hourly_items_sql);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $hourly_count = $row['count'];

    // If the user has made too many posts/comments in the last hour, limit them
    if ($hourly_count >= $long_limits[$action_type]['count']) {
        // Calculate wait time based on 1 hour from now
        $wait_seconds = 3600; // 1 hour in seconds
        return build_rate_limit_message($action_type, $wait_seconds);
    }

    // User is within limits, insert a fresh record
    $stmt = $db->prepare('INSERT INTO rate_limits (user_id, action_type, count, period_start, last_action_at) 
                         VALUES (:user_id, :action_type, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':action_type', $action_type, SQLITE3_TEXT);
    $stmt->execute();

    return false;
}

/**
 * Build an HTML rate limit message
 *
 * @param string $action_type
 * @param int $wait_seconds
 * @return string
 */
function build_rate_limit_message($action_type, $wait_seconds)
{
    $minutes = floor($wait_seconds / 60);
    $seconds = $wait_seconds % 60;
    $time_str = sprintf('%dm %02ds', $minutes, $seconds);

    // Add a timestamp for the countdown timer
    $reset_timestamp = time() + $wait_seconds;

    return '<div class="rate-limit-message">' .
        'You are ' . ($action_type === 'post' ? 'posting' : 'commenting') . ' too frequently. ' .
        'Please wait <span class="countdown-timer" data-reset-timestamp="' . $reset_timestamp . '">' . $time_str . '</span> before ' .
        ($action_type === 'post' ? 'posting' : 'commenting') . ' again.</div>';
}
