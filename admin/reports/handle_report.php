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

if (!in_array($action, ['delete', 'ban', 'dismiss'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    $db = get_db_connection();

    // Get admin user ID from community_users table
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
        $ban_reason = $_POST['ban_reason'] ?? '';
        $ban_duration = $_POST['ban_duration'] ?? '';

        if ($user_id <= 0 || empty($ban_reason) || empty($ban_duration)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        if (!in_array($ban_duration, ['30_days', '1_year', 'permanent'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid ban duration']);
            exit;
        }

        // Calculate expiration date
        $expires_at = null;
        if ($ban_duration === '30_days') {
            $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
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

        // Check if user has any existing admin role
        if (!$admin_user_id) {
            echo json_encode(['success' => false, 'message' => 'Admin user not found in community users table']);
            exit;
        }

        // Insert ban record
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

        // Mark report as resolved
        $stmt = $db->prepare('UPDATE content_reports SET status = "resolved", resolved_by = ?, resolved_at = NOW(), resolution_action = "user_banned" WHERE id = ?');
        $stmt->bind_param('ii', $admin_user_id, $report_id);
        $stmt->execute();
        $stmt->close();

        // Send ban notification email
        send_ban_notification_email($user['email'], $user['username'], $ban_reason, $ban_duration, $expires_at);

        echo json_encode(['success' => true, 'message' => 'User banned successfully']);

    } elseif ($action === 'dismiss') {
        // Mark report as dismissed
        $stmt = $db->prepare('UPDATE content_reports SET status = "dismissed", resolved_by = ?, resolved_at = NOW(), resolution_action = "dismissed" WHERE id = ?');
        $stmt->bind_param('ii', $admin_user_id, $report_id);

        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'Report dismissed']);
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to dismiss report']);
        }
    }

} catch (Exception $e) {
    error_log('Error handling report: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
