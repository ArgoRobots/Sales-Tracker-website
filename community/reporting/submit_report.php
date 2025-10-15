<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to report content.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$reporter_id = $_SESSION['user_id'];
$content_type = $_POST['report_type'];
$content_id = $_POST['report_id'];
$reason = $_POST['reason'];
$details = $_POST['report_details'] ?? '';

// Get the reported user ID based on content type
$reported_user_id = getReportedUserId($content_type, $content_id);

if (!$reported_user_id) {
    echo json_encode(['success' => false, 'message' => 'Content not found.']);
    exit;
}

// Check if user already reported this content
$stmt = $pdo->prepare("SELECT id FROM community_reports 
                      WHERE reporter_user_id = ? AND content_type = ? AND content_id = ? AND status = 'pending'");
$stmt->execute([$reporter_id, $content_type, $content_id]);

if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You have already reported this content.']);
    exit;
}

// Insert report
$stmt = $pdo->prepare("INSERT INTO community_reports 
                      (reporter_user_id, reported_user_id, content_type, content_id, reason, details, status) 
                      VALUES (?, ?, ?, ?, ?, ?, 'pending')");
$success = $stmt->execute([$reporter_id, $reported_user_id, $content_type, $content_id, $reason, $details]);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit report.']);
}

function getReportedUserId($content_type, $content_id) {
    global $pdo;
    
    switch ($content_type) {
        case 'post':
            $stmt = $pdo->prepare("SELECT user_id FROM community_posts WHERE id = ?");
            break;
        case 'comment':
            $stmt = $pdo->prepare("SELECT user_id FROM community_comments WHERE id = ?");
            break;
        case 'user_bio':
            return $content_id; // For user bios, content_id is the user_id
        default:
            return null;
    }
    
    $stmt->execute([$content_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['user_id'] : null;
}
?>
