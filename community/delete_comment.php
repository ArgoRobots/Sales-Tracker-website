<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';

header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Unauthorized access'
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to delete comments';
    echo json_encode($response);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

    if ($comment_id <= 0) {
        $response['message'] = 'Invalid comment ID';
        echo json_encode($response);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'] ?? 'user';

    // Get the comment to verify ownership
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT * FROM community_comments WHERE id = ?');
    $stmt->bind_param('i', $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();

    if (!$comment) {
        $response['message'] = 'Comment not found';
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    // Check permission: admin or comment owner can delete
    $can_delete = ($role === 'admin') ||
        (isset($comment['user_id']) && $comment['user_id'] == $user_id);

    if (!$can_delete) {
        $response['message'] = 'You do not have permission to delete this comment';
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    // Delete the comment
    $stmt = $db->prepare('DELETE FROM community_comments WHERE id = ?');
    $stmt->bind_param('i', $comment_id);

    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Comment deleted successfully',
            'post_id' => $comment['post_id']
        ];
    } else {
        $response['message'] = 'Error deleting comment: ' . $db->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method';
}

// Send the response
echo json_encode($response);
