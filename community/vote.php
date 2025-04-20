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
    $user_email = isset($_POST['user_email']) ? trim($_POST['user_email']) : '';
    $vote_type = isset($_POST['vote_type']) ? intval($_POST['vote_type']) : 0;
    
    // Basic validation
    if (empty($post_id) || empty($user_email)) {
        $response['message'] = 'Missing required parameters';
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format';
    } elseif ($vote_type !== 1 && $vote_type !== -1) {
        $response['message'] = 'Invalid vote type';
    } else {
        // Verify post exists
        $post = get_post($post_id);
        
        if (!$post) {
            $response['message'] = 'Post not found';
        } else {
            // Store user's email in session
            $_SESSION['user_email'] = $user_email;
            
            // Process the vote
            $result = vote_post($post_id, $user_email, $vote_type);
            
            if ($result !== false) {
                $response = [
                    'success' => true,
                    'message' => 'Vote recorded successfully',
                    'new_vote_count' => $result['new_vote_count'],
                    'user_vote' => $result['user_vote']
                ];
            } else {
                $response['message'] = 'Error recording vote';
            }
        }
    }
}

// Send the response
echo json_encode($response);