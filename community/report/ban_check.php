<?php
/**
 * Check if a user is currently banned
 *
 * @param int $user_id User ID to check
 * @return array|null Returns ban info if banned, null if not banned
 */
function is_user_banned($user_id)
{
    $db = get_db_connection();

    // Check for active ban that hasn't expired
    $stmt = $db->prepare('
        SELECT id, ban_reason, ban_duration, banned_at, expires_at
        FROM user_bans
        WHERE user_id = ?
        AND is_active = 1
        AND (expires_at IS NULL OR expires_at > NOW())
        ORDER BY banned_at DESC
        LIMIT 1
    ');

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ban = $result->fetch_assoc();
    $stmt->close();

    return $ban;
}

/**
 * Get user-friendly ban message
 *
 * @param array $ban Ban record from database
 * @return string User-friendly ban message
 */
function get_ban_message($ban)
{
    if (!$ban) {
        return '';
    }

    $message = 'Your account has been banned from posting content. ';

    if ($ban['expires_at']) {
        $expiry_date = date('F j, Y \a\t g:i A', strtotime($ban['expires_at']));
        $message .= "This ban will expire on {$expiry_date}. ";
    } else {
        $message .= 'This ban is permanent. ';
    }

    $message .= "Reason: {$ban['ban_reason']}";

    return $message;
}
