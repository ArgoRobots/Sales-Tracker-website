<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Invalid request'
];

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $user_email = isset($_POST['user_email']) ? trim($_POST['user_email']) : '';
    $content = isset($_POST['comment_content']) ? trim($_POST['comment_content']) : '';
    
    // Basic validation
    if (empty($post_id) || empty($user_name) || empty($user_email) || empty($content)) {
        $response['message'] = 'All fields are required';
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address';
    } elseif (strlen($content) > 2000) {
        $response['message'] = 'Comment is too long (maximum 2,000 characters)';
    } else {
        // Verify post exists
        $post = get_post($post_id);
        
        if (!$post) {
            $response['message'] = 'Post not found';
        } else {
            // Store user's email in session
            $_SESSION['user_email'] = $user_email;
            
            // Add the comment
            $comment = add_comment($post_id, $user_name, $user_email, $content);
            
            if ($comment) {
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