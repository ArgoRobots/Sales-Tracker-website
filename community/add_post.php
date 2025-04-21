<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Invalid request'
];

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $user_email = isset($_POST['user_email']) ? trim($_POST['user_email']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $post_type = isset($_POST['post_type']) ? trim($_POST['post_type']) : '';

    // Basic validation
    if (empty($user_name) || empty($user_email) || empty($title) || empty($content) || empty($post_type)) {
        $response['message'] = 'All fields are required';
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address';
    } elseif (!in_array($post_type, ['bug', 'feature'])) {
        $response['message'] = 'Invalid post type';
    } elseif (strlen($title) > 255) {
        $response['message'] = 'Title is too long (maximum 255 characters)';
    } elseif (strlen($content) > 10000) {
        $response['message'] = 'Content is too long (maximum 10,000 characters)';
    } else {
        // Store user's email in session
        $_SESSION['user_email'] = $user_email;

        // Add the post
        $post_id = add_post($user_name, $user_email, $title, $content, $post_type);

        if ($post_id) {
            $response = [
                'success' => true,
                'message' => 'Post added successfully',
                'post_id' => $post_id
            ];
        } else {
            $response['message'] = 'Error adding post to the database';
        }
    }
}

// Send the response
echo json_encode($response);
