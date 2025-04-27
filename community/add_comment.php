<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';

header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Invalid request'
];

// Check if user is logged in
if (!is_user_logged_in()) {
    $response['message'] = 'You must be logged in to comment';
    echo json_encode($response);
    exit;
}

$user_id = $current_user['id'] ?? 0;
$response = '';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $content = isset($_POST['comment_content']) ? trim($_POST['comment_content']) : '';

    // Get user data from session
    $username = $_SESSION['username'] ?? 'Unknown';
    $email = $_SESSION['email'] ?? '';

    // Basic validation
    if (empty($content)) {
        $response['message'] = 'All fields are required';
    } elseif (strlen($content) > 2000) {
        $response['message'] = 'Comment is too long (maximum 2,000 characters)';
    } else {
        // Verify post exists
        $post = get_post($post_id);

        if (!$post) {
            $response['message'] = 'Post not found';
        } else {
            // Add the comment
            $comment = add_comment($post_id, $username, $email, $content);

            if ($comment) {
                // Connect comment to user account
                $db = get_db_connection();

                // Make sure we have a valid user ID
                $user_id = isset($current_user['id']) ? intval($current_user['id']) : 0;

                if ($user_id > 0) {
                    $stmt = $db->prepare('UPDATE community_comments SET user_id = :user_id WHERE id = :comment_id');
                    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
                    $stmt->bindValue(':comment_id', $comment['id'], SQLITE3_INTEGER);
                    $stmt->execute();
                }

                $response = [
                    'success' => true,
                    'message' => 'Comment added successfully',
                    'comment' => $comment
                ];
            } else {
                $response['message'] = 'Error adding comment to the database';
            }
        }
    }
}

// Send the response
echo json_encode($response);
