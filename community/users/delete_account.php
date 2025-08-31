<?php
session_start();
require_once '../../db_connect.php';
require_once '../community_functions.php';
require_once 'user_functions.php';
require_once '../../email_sender.php';

header('Content-Type: application/json');
$response = [
    'success' => false,
    'message' => 'Unauthorized access'
];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$db = get_db_connection();

try {
    // Get user information before scheduling deletion
    $stmt = $db->prepare('SELECT username, email FROM community_users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        exit;
    }

    $scheduledDate = date('Y-m-d H:i:s', strtotime('+30 days'));
    $stmt = $db->prepare('UPDATE community_users SET deletion_scheduled_at = ? WHERE id = ?');
    $stmt->bind_param('si', $scheduledDate, $user_id);
    $stmt->execute();
    $stmt->close();

    // Send account deletion scheduled email
    $email_sent = send_account_deletion_scheduled_email($user['email'], $user['username'], $scheduledDate);

    clear_remember_token($user_id);
    session_unset();
    session_destroy();

    $response['success'] = true;
    $response['message'] = 'Account scheduled for deletion in 30 days. You will receive a confirmation email shortly.';

    // Log email sending status (optional)
    if (!$email_sent) {
        error_log("Failed to send deletion scheduled email to: " . $user['email']);
    }
} catch (Exception $e) {
    error_log("Error in delete_account.php: " . $e->getMessage());
    $response['message'] = 'Error scheduling account deletion';
}

echo json_encode($response);
