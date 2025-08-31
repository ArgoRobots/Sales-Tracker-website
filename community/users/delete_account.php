<?php
session_start();
require_once '../../db_connect.php';
require_once '../community_functions.php';
require_once 'user_functions.php';

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
    $scheduledDate = date('Y-m-d H:i:s', strtotime('+30 days'));
    $stmt = $db->prepare('UPDATE community_users SET deletion_scheduled_at = ? WHERE id = ?');
    $stmt->bind_param('si', $scheduledDate, $user_id);
    $stmt->execute();
    $stmt->close();

    clear_remember_token($user_id);
    session_unset();
    session_destroy();

    $response['success'] = true;
    $response['message'] = 'Account scheduled for deletion in 30 days. Log in before then to cancel.';
} catch (Exception $e) {
    $response['message'] = 'Error scheduling account deletion';
}

echo json_encode($response);
