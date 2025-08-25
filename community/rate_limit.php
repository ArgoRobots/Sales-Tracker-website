<?php

$RATE_LIMITS = [
    'post' => [
        'short' => ['count' => 1, 'minutes' => 5],    // 1 post per 5 minutes
        'long'  => ['count' => 5, 'hours' => 1]       // 5 posts per hour
    ],
    'comment' => [
        'short' => ['count' => 3, 'minutes' => 5],    // 3 comments per 5 minutes
        'long'  => ['count' => 20, 'hours' => 1]      // 20 comments per hour
    ]
];

/**
 * Check if a user has exceeded rate limits
 *
 * @param int    $user_id     User ID
 * @param string $action_type Action type ('post' or 'comment')
 * @return bool|string        False if within limits, HTML string if limit exceeded
 */
function check_rate_limit($user_id, $action_type)
{
    global $RATE_LIMITS;

    // Skip checks for admin users
    $is_logged_in = isset($_SESSION['user_id']);
    $role         = $is_logged_in ? ($_SESSION['role'] ?? 'user') : '';
    if ($role === 'admin') {
        return false;
    }

    $db = get_db_connection();

    // Check if rate limit is defined for this action type
    if (!isset($RATE_LIMITS[$action_type])) {
        return false;
    }

    $now         = time();
    $short_limit = $RATE_LIMITS[$action_type]['short'];
    $long_limit  = $RATE_LIMITS[$action_type]['long'];

    // Retrieve existing rate limit row
    $stmt = $db->prepare('SELECT count, period_start, last_action_at FROM rate_limits WHERE user_id = ? AND action_type = ?');
    $stmt->bind_param('is', $user_id, $action_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $row    = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        // Reset count if the long-term window has passed
        if (strtotime($row['period_start']) <= $now - ($long_limit['hours'] * 3600)) {
            $stmt = $db->prepare('UPDATE rate_limits SET count = 0, period_start = CURRENT_TIMESTAMP WHERE user_id = ? AND action_type = ?');
            $stmt->bind_param('is', $user_id, $action_type);
            $stmt->execute();
            $stmt->close();

            $row['count']        = 0;
            $row['period_start'] = date('Y-m-d H:i:s', $now);
        }

        // Short-term limit check
        $last_action_timestamp = strtotime($row['last_action_at']);
        $short_cooldown_end = $last_action_timestamp + ($short_limit['minutes'] * 60);

        if ($last_action_timestamp > $now - ($short_limit['minutes'] * 60) && $row['count'] >= $short_limit['count']) {
            $remaining_seconds = $short_cooldown_end - $now;
            return build_rate_limit_message($action_type, $remaining_seconds, $row['last_action_at']);
        }

        // Long-term limit check
        if ($row['count'] >= $long_limit['count']) {
            $remaining = ($long_limit['hours'] * 3600) - ($now - strtotime($row['period_start']));
            return build_rate_limit_message($action_type, $remaining, $row['period_start']);
        }
    }

    // Record this action
    if ($row) {
        // Update existing record
        $stmt = $db->prepare('UPDATE rate_limits SET count = count + 1, last_action_at = CURRENT_TIMESTAMP WHERE user_id = ? AND action_type = ?');
        $stmt->bind_param('is', $user_id, $action_type);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new record
        $stmt = $db->prepare('INSERT INTO rate_limits (user_id, action_type, count, period_start, last_action_at) VALUES (?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $stmt->bind_param('is', $user_id, $action_type);
        $stmt->execute();
        $stmt->close();
    }

    return false;
}

/**
 * Build an HTML rate limit message
 *
 * @param string $action_type Action type ('post' or 'comment')
 * @param int    $wait_seconds Wait time in seconds
 * @param string $last_action_time The timestamp of the last action (from database)
 * @return string HTML message
 */
function build_rate_limit_message($action_type, $wait_seconds, $last_action_time = null)
{
    // Calculate actual reset time based on when the rate limit was triggered
    if ($last_action_time) {
        $reset_timestamp = strtotime($last_action_time) + $wait_seconds;
    } else {
        $reset_timestamp = time() + $wait_seconds;
    }

    // Make sure we don't show negative time
    $current_wait = $reset_timestamp - time();
    if ($current_wait <= 0) {
        return false; // Rate limit has expired
    }

    $minutes = floor($current_wait / 60);
    $seconds = $current_wait % 60;
    $time_str = sprintf('%dm %02ds', $minutes, $seconds);

    return '<div class="rate-limit-message">'
        . 'You are ' . ($action_type === 'post' ? 'posting' : 'commenting') . ' too frequently. '
        . 'Please wait <span class="countdown-timer" data-reset-timestamp="' . $reset_timestamp . '">' . $time_str . '</span> before '
        . ($action_type === 'post' ? 'posting' : 'commenting') . ' again.</div>';
}
