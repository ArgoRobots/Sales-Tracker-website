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

$db->begin_transaction();
try {
    // Get avatar path before deletion
    $stmt = $db->prepare('SELECT avatar FROM community_users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $avatar_path = $user['avatar'] ?? '';

    // Delete user's posts (will cascade to comments, votes, etc.)
    $stmt = $db->prepare('DELETE FROM community_posts WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete comments made by the user on other posts
    $stmt = $db->prepare('DELETE FROM community_comments WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete votes made by the user
    $stmt = $db->prepare('DELETE FROM community_votes WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete comment votes made by the user
    $stmt = $db->prepare('DELETE FROM comment_votes WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete edit history authored by the user
    $stmt = $db->prepare('DELETE FROM post_edit_history WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();

    // Finally delete the user account
    $stmt = $db->prepare('DELETE FROM community_users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();

    $db->commit();

    // Remove avatar file if exists
    if (!empty($avatar_path)) {
        $file_path = dirname(__DIR__) . '/' . $avatar_path;
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
    }

    // Clear remember token and session
    clear_remember_token($user_id);
    session_unset();
    session_destroy();

    $response['success'] = true;
    $response['message'] = 'Account deleted successfully';
} catch (Exception $e) {
    $db->rollback();
    $response['message'] = 'Error deleting account';
}

echo json_encode($response);
