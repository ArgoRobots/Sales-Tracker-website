<?php
session_start();
require_once '../../db_connect.php';
require_once '../users/user_functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to report content.']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get and validate input
$content_type = $_POST['content_type'] ?? '';
$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
$violation_type = $_POST['violation_type'] ?? '';
$additional_info = $_POST['additional_info'] ?? '';

// Validation
if (!in_array($content_type, ['post', 'comment', 'user'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid content type.']);
    exit;
}

if ($content_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid content ID.']);
    exit;
}

if (empty($violation_type)) {
    echo json_encode(['success' => false, 'message' => 'Please select a violation type.']);
    exit;
}

$valid_violations = ['spam', 'harassment', 'hateful', 'inappropriate', 'misinformation', 'off-topic', 'inappropriate_username', 'inappropriate_bio', 'impersonation', 'other'];
if (!in_array($violation_type, $valid_violations)) {
    echo json_encode(['success' => false, 'message' => 'Invalid violation type.']);
    exit;
}

try {
    $db = get_db_connection();

    // Get reporter info
    $reporter_user_id = $_SESSION['user_id'];
    $reporter_email = $_SESSION['email'];

    // Verify content exists
    if ($content_type === 'post') {
        $stmt = $db->prepare('SELECT id, user_id FROM community_posts WHERE id = ?');
        $stmt->bind_param('i', $content_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $content = $result->fetch_assoc();
        $stmt->close();

        if (!$content) {
            echo json_encode(['success' => false, 'message' => 'Post not found.']);
            exit;
        }

        // Don't allow users to report their own posts
        if ($content['user_id'] == $reporter_user_id) {
            echo json_encode(['success' => false, 'message' => 'You cannot report your own content.']);
            exit;
        }
    } elseif ($content_type === 'comment') {
        $stmt = $db->prepare('SELECT id, user_id FROM community_comments WHERE id = ?');
        $stmt->bind_param('i', $content_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $content = $result->fetch_assoc();
        $stmt->close();

        if (!$content) {
            echo json_encode(['success' => false, 'message' => 'Comment not found.']);
            exit;
        }

        // Don't allow users to report their own comments
        if ($content['user_id'] == $reporter_user_id) {
            echo json_encode(['success' => false, 'message' => 'You cannot report your own content.']);
            exit;
        }
    } else {
        // User report
        $stmt = $db->prepare('SELECT id FROM community_users WHERE id = ?');
        $stmt->bind_param('i', $content_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $content = $result->fetch_assoc();
        $stmt->close();

        if (!$content) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }

        // Don't allow users to report themselves
        if ($content_id == $reporter_user_id) {
            echo json_encode(['success' => false, 'message' => 'You cannot report yourself.']);
            exit;
        }
    }

    // Check if user has already reported this content
    $stmt = $db->prepare('SELECT id FROM content_reports WHERE reporter_user_id = ? AND content_type = ? AND content_id = ? AND status = "pending"');
    $stmt->bind_param('isi', $reporter_user_id, $content_type, $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_report = $result->fetch_assoc();
    $stmt->close();

    if ($existing_report) {
        echo json_encode(['success' => false, 'message' => 'You have already reported this content.']);
        exit;
    }

    // Insert report
    $stmt = $db->prepare('INSERT INTO content_reports (reporter_user_id, reporter_email, content_type, content_id, violation_type, additional_info) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ississ', $reporter_user_id, $reporter_email, $content_type, $content_id, $violation_type, $additional_info);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Report submitted successfully.']);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Failed to submit report. Please try again.']);
    }

} catch (Exception $e) {
    error_log('Error submitting report: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
