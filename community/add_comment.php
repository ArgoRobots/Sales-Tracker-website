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
    $content = isset($_POST['comment_content']) ? trim($_POST['comment_content']) : '';
    
    // Use default values for name and email
    $user_name = 'Anonymous';
    $user_email = 'anonymous@example.com';
    
    // Basic validation
    if (empty($post_id) || empty($content)) {
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