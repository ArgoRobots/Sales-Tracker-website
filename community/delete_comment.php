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
if ($_SESSION['role'] === 'admin') {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if ($comment_id > 0) {
            // Delete the comment
            $result = delete_comment($comment_id);

            if ($result && $result['success']) {
                $response = [
                    'success' => true,
                    'message' => 'Comment deleted successfully',
                    'post_id' => $result['post_id']
                ];
            } else {
                $response['message'] = 'Error deleting comment';
            }
        } else {
            $response['message'] = 'Invalid comment ID';
        }
    } else {
        $response['message'] = 'Invalid request method';
    }
}

// Send the response
echo json_encode($response);
