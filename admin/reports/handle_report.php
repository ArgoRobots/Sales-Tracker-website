<?php
session_start();
require_once '../../db_connect.php';
require_once '../../email_sender.php';

header('Content-Type: application/json');

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate input
$report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
$action = $_POST['action'] ?? '';

if ($report_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
    exit;
}

if (!in_array($action, ['delete', 'ban', 'dismiss', 'reset_username', 'clear_bio'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    $db = get_db_connection();

    // Get admin user ID from community_users table (may be null if admin is only in admin_users)
    $stmt = $db->prepare('SELECT id FROM community_users WHERE email = ? AND role = "admin" LIMIT 1');
    $admin_email = $_SESSION['admin_email'] ?? '';
    $stmt->bind_param('s', $admin_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_user = $result->fetch_assoc();
    $stmt->close();

    $admin_user_id = $admin_user ? $admin_user['id'] : null;

    // Handle different actions
    if ($action === 'delete') {
        $content_type = $_POST['content_type'] ?? '';
        $content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;

        if (!in_array($content_type, ['post', 'comment']) || $content_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid content parameters']);
            exit;
        }

        // Delete the content
        if ($content_type === 'post') {
            $stmt = $db->prepare('DELETE FROM community_posts WHERE id = ?');
        } else {
            $stmt = $db->prepare('DELETE FROM community_comments WHERE id = ?');
        }
        $stmt->bind_param('i', $content_id);

        if (!$stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to delete content']);
            exit;
        }
        $stmt->close();

        // Mark report as resolved
        $stmt = $db->prepare('UPDATE content_reports SET status = "resolved", resolved_by = ?, resolved_at = NOW(), resolution_action = "content_deleted" WHERE id = ?');
        $stmt->bind_param('ii', $admin_user_id, $report_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true, 'message' => 'Content deleted successfully']);

    } elseif ($action === 'ban') {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $violation_type = $_POST['violation_type'] ?? '';
        $additional_details = $_POST['additional_details'] ?? '';
        $ban_duration = $_POST['ban_duration'] ?? '';

        if ($user_id <= 0 || empty($violation_type) || empty($ban_duration)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Format the ban reason from violation type and additional details
        $ban_reason = ucfirst(str_replace('_', ' ', $violation_type));
        if (!empty($additional_details)) {
            $ban_reason .= ': ' . $additional_details;
        }

        if (!in_array($ban_duration, ['5_days', '10_days', '30_days', '100_days', '1_year', 'permanent'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid ban duration']);
            exit;
        }

        // Calculate expiration date
        $expires_at = null;
        if ($ban_duration === '5_days') {
            $expires_at = date('Y-m-d H:i:s', strtotime('+5 days'));
        } elseif ($ban_duration === '10_days') {
            $expires_at = date('Y-m-d H:i:s', strtotime('+10 days'));
        } elseif ($ban_duration === '30_days') {
            $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
        } elseif ($ban_duration === '100_days') {
            $expires_at = date('Y-m-d H:i:s', strtotime('+100 days'));
        } elseif ($ban_duration === '1_year') {
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 year'));
        }
        // For permanent, expires_at remains null

        // Get user details for email
        $stmt = $db->prepare('SELECT username, email FROM community_users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        // Insert ban record (banned_by can be NULL if admin is not in community_users)
        if ($expires_at) {
            $stmt = $db->prepare('INSERT INTO user_bans (user_id, banned_by, ban_reason, ban_duration, expires_at) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('iisss', $user_id, $admin_user_id, $ban_reason, $ban_duration, $expires_at);
        } else {
            $stmt = $db->prepare('INSERT INTO user_bans (user_id, banned_by, ban_reason, ban_duration) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('iiss', $user_id, $admin_user_id, $ban_reason, $ban_duration);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to ban user']);
            exit;
        }
        $stmt->close();

        // Mark current report as resolved
        $stmt = $db->prepare('UPDATE content_reports SET status = "resolved", resolved_by = ?, resolved_at = NOW(), resolution_action = "user_banned" WHERE id = ?');
        $stmt->bind_param('ii', $admin_user_id, $report_id);
        $stmt->execute();
        $stmt->close();

        // Mark all other pending reports for this user as resolved
        $stmt = $db->prepare('UPDATE content_reports r
            LEFT JOIN community_posts p ON r.content_type = "post" AND r.content_id = p.id
            LEFT JOIN community_comments c ON r.content_type = "comment" AND r.content_id = c.id
            SET r.status = "resolved",
                r.resolved_by = ?,
                r.resolved_at = NOW(),
                r.resolution_action = "user_banned"
            WHERE r.status = "pending"
            AND r.id != ?
            AND (
                (r.content_type = "post" AND p.user_id = ?) OR
                (r.content_type = "comment" AND c.user_id = ?) OR
                (r.content_type = "user" AND r.content_id = ?)
            )');
        $stmt->bind_param('iiiii', $admin_user_id, $report_id, $user_id, $user_id, $user_id);
        $stmt->execute();
        $affected_reports = $stmt->affected_rows;
        $stmt->close();

        // Send ban notification email
        send_ban_notification_email($user['email'], $user['username'], $ban_reason, $ban_duration, $expires_at);

        $message = 'User banned successfully';
        if ($affected_reports > 0) {
            $message .= " and {$affected_reports} other report(s) resolved";
        }

        echo json_encode(['success' => true, 'message' => $message]);

    } elseif ($action === 'dismiss') {
        // Mark report as dismissed
        $stmt = $db->prepare('UPDATE content_reports SET status = "dismissed", resolved_by = ?, resolved_at = NOW(), resolution_action = "dismissed" WHERE id = ?');
        $stmt->bind_param('ii', $admin_user_id, $report_id);

        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'Report dismissed successfully']);
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to dismiss report']);
            exit;
        }

    } elseif ($action === 'reset_username') {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $violation_type = $_POST['violation_type'] ?? '';
        $additional_details = $_POST['additional_details'] ?? '';

        if ($user_id <= 0 || empty($violation_type)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Get user details before making changes
        $stmt = $db->prepare('SELECT username, email FROM community_users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        $old_username = $user['username'];
        $user_email = $user['email'];

        // Generate a random username
        $random_username = 'user_' . bin2hex(random_bytes(8));

        // Check if username already exists (very unlikely but let's be safe)
        $attempts = 0;
        while ($attempts < 5) {
            $stmt = $db->prepare('SELECT id FROM community_users WHERE username = ?');
            $stmt->bind_param('s', $random_username);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows === 0) {
                break;
            }

            $random_username = 'user_' . bin2hex(random_bytes(8));
            $attempts++;
        }

        // Update username in community_users table
        $stmt = $db->prepare('UPDATE community_users SET username = ? WHERE id = ?');
        $stmt->bind_param('si', $random_username, $user_id);

        if (!$stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to reset username']);
            exit;
        }
        $stmt->close();

        // Update username across all posts and comments
        $stmt = $db->prepare('UPDATE community_posts SET user_name = ? WHERE user_id = ?');
        $stmt->bind_param('si', $random_username, $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare('UPDATE community_comments SET user_name = ? WHERE user_id = ?');
        $stmt->bind_param('si', $random_username, $user_id);
        $stmt->execute();
        $stmt->close();

        // Mark report as resolved
        $stmt = $db->prepare('UPDATE content_reports SET status = "resolved", resolved_by = ?, resolved_at = NOW(), resolution_action = "username_reset" WHERE id = ?');
        $stmt->bind_param('ii', $admin_user_id, $report_id);
        $stmt->execute();
        $stmt->close();

        // Send email notification to user
        send_username_reset_email($user_email, $old_username, $random_username, $violation_type, $additional_details);

        echo json_encode(['success' => true, 'message' => "Username reset to: {$random_username}"]);

    } elseif ($action === 'clear_bio') {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $violation_type = $_POST['violation_type'] ?? '';
        $additional_details = $_POST['additional_details'] ?? '';

        if ($user_id <= 0 || empty($violation_type)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Get user details before making changes
        $stmt = $db->prepare('SELECT username, email FROM community_users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        $username = $user['username'];
        $user_email = $user['email'];

        // Clear the bio
        $stmt = $db->prepare('UPDATE community_users SET bio = NULL WHERE id = ?');
        $stmt->bind_param('i', $user_id);

        if (!$stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to clear bio']);
            exit;
        }
        $stmt->close();

        // Mark report as resolved
        $stmt = $db->prepare('UPDATE content_reports SET status = "resolved", resolved_by = ?, resolved_at = NOW(), resolution_action = "bio_cleared" WHERE id = ?');
        $stmt->bind_param('ii', $admin_user_id, $report_id);
        $stmt->execute();
        $stmt->close();

        // Send email notification to user
        send_bio_cleared_email($user_email, $username, $violation_type, $additional_details);

        echo json_encode(['success' => true, 'message' => 'Bio cleared successfully']);
    }

} catch (Exception $e) {
    error_log('Error handling report: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}