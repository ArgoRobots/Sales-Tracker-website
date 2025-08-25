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
        if (strtotime($row['last_action_at']) > $now - ($short_limit['minutes'] * 60) &&
            $row['count'] >= $short_limit['count']) {
            return build_rate_limit_message($action_type, $short_limit['minutes'] * 60);
        }

        // Long-term limit check
        if ($row['count'] >= $long_limit['count']) {
            $remaining = ($long_limit['hours'] * 3600) - ($now - strtotime($row['period_start']));
            return build_rate_limit_message($action_type, $remaining);
        }
    }

    // Record this action
    $stmt = $db->prepare(
        'INSERT INTO rate_limits (user_id, action_type, count, period_start, last_action_at) ' .
        'VALUES (?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ' .
        'ON DUPLICATE KEY UPDATE count = count + 1, last_action_at = CURRENT_TIMESTAMP'
    );
    $stmt->bind_param('is', $user_id, $action_type);
    $stmt->execute();
    $stmt->close();

    return false;
}

/**
 * Build an HTML rate limit message
 *
 * @param string $action_type Action type ('post' or 'comment')
 * @param int    $wait_seconds Wait time in seconds
 * @return string HTML message
 */
function build_rate_limit_message($action_type, $wait_seconds)
{
    $minutes = floor($wait_seconds / 60);
    $seconds = $wait_seconds % 60;
    $time_str = sprintf('%dm %02ds', $minutes, $seconds);
    $reset_timestamp = time() + $wait_seconds;

    return '<div class="rate-limit-message">'
        . 'You are ' . ($action_type === 'post' ? 'posting' : 'commenting') . ' too frequently. '
        . 'Please wait <span class="countdown-timer" data-reset-timestamp="' . $reset_timestamp . '">' . $time_str . '</span> before '
        . ($action_type === 'post' ? 'posting' : 'commenting') . ' again.</div>';
}
?>
