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

    // Get current rate limit record for this user and action
    $stmt = $db->prepare('SELECT * FROM rate_limits WHERE user_id = :user_id AND action_type = :action_type');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':action_type', $action_type, SQLITE3_TEXT);
    $result = $stmt->execute();

    if ($result === false) {
        error_log('Failed to execute rate limit query.');
        return false;
    }

    $row = $result->fetchArray(SQLITE3_ASSOC);

    // First-time post, no rate limiting needed
    if (!$row) {
        // Create a new record for this user and action
        $stmt = $db->prepare('INSERT INTO rate_limits (user_id, action_type, count, period_start, last_action_at) 
                             VALUES (:user_id, :action_type, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':action_type', $action_type, SQLITE3_TEXT);
        $stmt->execute();
        return false; // Allow the action
    }

    // DEBUG: Let's check exactly what's in the database
    $debug_output = "<div style='background-color: #f0f0f0; margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
    $debug_output .= "<h4>Rate Limit Debug Info</h4>";
    $debug_output .= "<p>User ID: $user_id, Action: $action_type</p>";
    $debug_output .= "<p>Count: {$row['count']}</p>";
    $debug_output .= "<p>Period Start: {$row['period_start']}</p>";
    $debug_output .= "<p>Last Action: {$row['last_action_at']}</p>";

    // Get current time and convert timestamps
    $now = time();
    $period_start = strtotime($row['period_start']);
    $last_action = strtotime($row['last_action_at']);
    $count = (int)$row['count'];

    $debug_output .= "<p>Now: " . date('Y-m-d H:i:s', $now) . "</p>";
    $debug_output .= "<p>Period Start (parsed): " . date('Y-m-d H:i:s', $period_start) . "</p>";
    $debug_output .= "<p>Last Action (parsed): " . date('Y-m-d H:i:s', $last_action) . "</p>";

    // Check if timestamps parsed correctly
    if ($period_start === false || $last_action === false) {
        // Handle invalid timestamps by resetting the record
        $debug_output .= "<p style='color: red;'>Invalid timestamps detected! Resetting record.</p>";
        $stmt = $db->prepare('UPDATE rate_limits SET count = 1, period_start = CURRENT_TIMESTAMP, last_action_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':id', $row['id'], SQLITE3_INTEGER);
        $stmt->execute();
        $debug_output .= "</div>";

        // Return the debug output for analysis
        return $debug_output;
    }

    // Check for long-term limit reset (if period has expired)
    $long_limit = $long_limits[$action_type];
    $long_window_seconds = $long_limit['hours'] * 3600;

    $seconds_since_period_start = $now - $period_start;
    $debug_output .= "<p>Seconds since period start: $seconds_since_period_start (long window: $long_window_seconds)</p>";

    if ($seconds_since_period_start >= $long_window_seconds) {
        // Long-term period has expired, reset counter
        $debug_output .= "<p style='color: green;'>Long-term period expired! Resetting counter.</p>";
        $stmt = $db->prepare('UPDATE rate_limits SET count = 1, period_start = CURRENT_TIMESTAMP, last_action_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':id', $row['id'], SQLITE3_INTEGER);
        $stmt->execute();
        $debug_output .= "</div>";
        return false; // Allow the action
    }

    // Check short-term limit
    $short_limit = $short_limits[$action_type];
    $short_window_seconds = $short_limit['minutes'] * 60;
    $seconds_since_last_action = $now - $last_action;

    $debug_output .= "<p>Seconds since last action: $seconds_since_last_action (short window: $short_window_seconds)</p>";

    if ($seconds_since_last_action < $short_window_seconds && $count >= $short_limit['count']) {
        // Short-term limit exceeded
        $next_allowed_time = $last_action + $short_window_seconds;
        $wait_seconds = max(0, $next_allowed_time - $now);

        $debug_output .= "<p style='color: red;'>Short-term limit exceeded!</p>";
        $debug_output .= "<p>Next allowed time: " . date('Y-m-d H:i:s', $next_allowed_time) . "</p>";
        $debug_output .= "<p>Wait seconds: $wait_seconds</p>";
        $debug_output .= "</div>";

        // Return the regular rate limit message
        return build_rate_limit_message($action_type, $wait_seconds);
    }

    // Check long-term limit
    if ($count >= $long_limit['count']) {
        // Long-term limit exceeded
        $next_allowed_time = $period_start + $long_window_seconds;
        $wait_seconds = max(0, $next_allowed_time - $now);

        $debug_output .= "<p style='color: red;'>Long-term limit exceeded!</p>";
        $debug_output .= "<p>Next allowed time: " . date('Y-m-d H:i:s', $next_allowed_time) . "</p>";
        $debug_output .= "<p>Wait seconds: $wait_seconds</p>";
        $debug_output .= "</div>";

        // Return the regular rate limit message
        return build_rate_limit_message($action_type, $wait_seconds);
    }

    // All checks passed, increment counter and update last action time
    $debug_output .= "<p style='color: green;'>All checks passed! Incrementing counter.</p>";
    $debug_output .= "</div>";

    $stmt = $db->prepare('UPDATE rate_limits SET count = count + 1, last_action_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->bindValue(':id', $row['id'], SQLITE3_INTEGER);
    $stmt->execute();

    return false; // Allow the action
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
