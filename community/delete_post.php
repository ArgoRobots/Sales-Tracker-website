<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Unauthorized access'
];

// Check if user is an admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if ($post_id > 0) {
            // Delete the post
            if (delete_post($post_id)) {
                $response = [
                    'success' => true,
                    'message' => 'Post deleted successfully'
                ];
            } else {
                $response['message'] = 'Error deleting post';
            }
        } else {
            $response['message'] = 'Invalid post ID';
        }
    } else {
        $response['message'] = 'Invalid request method';
    }
}

// Send the response
echo json_encode($response);