<?php

$RATE_LIMITS = [
    'post' => [
        'short' => ['count' => 1, 'minutes' => 5],    // 1 post per 5 minutes
        'long' => ['count' => 5, 'hours' => 1]        // 5 posts per hour
    ],
    'comment' => [
        'short' => ['count' => 3, 'minutes' => 5],    // 3 comments per 5 minutes
        'long' => ['count' => 20, 'hours' => 1]       // 20 comments per hour
    ]
];

/**
 * Check if a user has exceeded rate limits
 * 
 * @param int $user_id User ID
 * @param string $action_type Action type ('post' or 'comment')
 * @return bool|string False if within limits, HTML string if limit exceeded
 */
function check_rate_limit($user_id, $action_type)
{
    global $RATE_LIMITS;

    // Skip checks for admin users
    $is_logged_in = isset($_SESSION['user_id']);
    $role = $is_logged_in ? ($_SESSION['role'] ?? 'user') : '';
    if ($role === 'admin') {
        return false;
    }

    $db = get_db_connection();

    // Check if rate limit is defined for this action type
    if (!isset($RATE_LIMITS[$action_type])) {
        return false;
    }

    // Delete existing rate limit records for a fresh start
    $stmt = $db->prepare('DELETE FROM rate_limits WHERE user_id = :user_id AND action_type = :action_type');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':action_type', $action_type, SQLITE3_TEXT);
    $stmt->execute();

    // Check short-term limit (minutes)
    $table = $action_type === 'post' ? 'community_posts' : 'community_comments';
    $time_limit = $RATE_LIMITS[$action_type]['short']['minutes'];
    $count_limit = $RATE_LIMITS[$action_type]['short']['count'];

    $result = check_period_limit($db, $table, $user_id, $time_limit, 'minutes');

    if ($result >= $count_limit) {
        // Short-term limit exceeded
        return build_rate_limit_message($action_type, 300); // 5 minutes in seconds
    }

    // Check long-term limit (hours)
    $time_limit = $RATE_LIMITS[$action_type]['long']['hours'];
    $count_limit = $RATE_LIMITS[$action_type]['long']['count'];

    $result = check_period_limit($db, $table, $user_id, $time_limit, 'hours');

    if ($result >= $count_limit) {
        // Long-term limit exceeded
        return build_rate_limit_message($action_type, 3600); // 1 hour in seconds
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
 * Check how many actions the user has performed in a time period
 * 
 * @param SQLite3 $db Database connection
 * @param string $table Table to check ('community_posts' or 'community_comments')
 * @param int $user_id User ID
 * @param int $time_value Time value (e.g., 5)
 * @param string $time_unit Time unit ('minutes' or 'hours')
 * @return int Count of actions in the specified period
 */
function check_period_limit($db, $table, $user_id, $time_value, $time_unit)
{
    $sql = "SELECT COUNT(*) as count FROM {$table} 
            WHERE user_id = :user_id 
            AND datetime(created_at) > datetime('now', '-{$time_value} {$time_unit}')";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    return $row['count'];
}

/**
 * Build an HTML rate limit message
 *
 * @param string $action_type Action type ('post' or 'comment')
 * @param int $wait_seconds Wait time in seconds
 * @return string HTML message
 */
function build_rate_limit_message($action_type, $wait_seconds)
{
    $minutes = floor($wait_seconds / 60);
    $seconds = $wait_seconds % 60;
    $time_str = sprintf('%dm %02ds', $minutes, $seconds);
    $reset_timestamp = time() + $wait_seconds;

    return '<div class="rate-limit-message">' .
        'You are ' . ($action_type === 'post' ? 'posting' : 'commenting') . ' too frequently. ' .
        'Please wait <span class="countdown-timer" data-reset-timestamp="' . $reset_timestamp . '">' . $time_str . '</span> before ' .
        ($action_type === 'post' ? 'posting' : 'commenting') . ' again.</div>';
}
