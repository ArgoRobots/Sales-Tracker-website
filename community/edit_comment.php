<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Unauthorized access'
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to edit comments';
    echo json_encode($response);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    $comment_content = isset($_POST['comment_content']) ? trim($_POST['comment_content']) : '';

    if ($comment_id <= 0) {
        $response['message'] = 'Invalid comment ID';
        echo json_encode($response);
        exit;
    }

    if (empty($comment_content)) {
        $response['message'] = 'Comment content cannot be empty';
        echo json_encode($response);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'] ?? 'user';

    // Get the comment to verify ownership
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT * FROM community_comments WHERE id = :id');
    $stmt->bindValue(':id', $comment_id, SQLITE3_INTEGER);
    $comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$comment) {
        $response['message'] = 'Comment not found';
        echo json_encode($response);
        exit;
    }

    // Debug information to help troubleshoot permission issues
    error_log("Comment ID: $comment_id, User ID: $user_id, Comment User ID: {$comment['user_id']}, Role: $role");

    // Check if user has permission to edit this comment
    // Admin can edit any comment, regular users can only edit their own comments
    if ($role === 'admin' || $comment['user_id'] == $user_id) {
        // Update the comment with new content
        $stmt = $db->prepare('UPDATE community_comments SET content = :content WHERE id = :id');
        $stmt->bindValue(':content', $comment_content, SQLITE3_TEXT);
        $stmt->bindValue(':id', $comment_id, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            // Get the updated comment
            $stmt = $db->prepare('SELECT * FROM community_comments WHERE id = :id');
            $stmt->bindValue(':id', $comment_id, SQLITE3_INTEGER);
            $updated_comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

            $response = [
                'success' => true,
                'message' => 'Comment updated successfully',
                'comment' => $updated_comment
            ];
        } else {
            $response['message'] = 'Error updating comment';
        }
    } else {
        $response['message'] = "You do not have permission to edit this comment. Your user ID: $user_id, Comment user ID: {$comment['user_id']}";
    }
}

// Send the response
echo json_encode($response);
