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
    $response['message'] = 'You must be logged in to delete posts';
    echo json_encode($response);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if ($post_id <= 0) {
        $response['message'] = 'Invalid post ID';
        echo json_encode($response);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'] ?? 'user';

    // Get the post to verify ownership
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT * FROM community_posts WHERE id = ?');
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    if (!$post) {
        $response['message'] = 'Post not found';
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    // Check permission: admin or post owner can delete
    $can_delete = ($role === 'admin') ||
        (isset($post['user_id']) && $post['user_id'] == $user_id);

    if (!$can_delete) {
        $response['message'] = "You do not have permission to delete this post. User ID: $user_id, Post User ID: {$post['user_id']}";
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    // Delete the post
    $stmt = $db->prepare('DELETE FROM community_posts WHERE id = ?');
    $stmt->bind_param('i', $post_id);

    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Post deleted successfully'
        ];
    } else {
        $response['message'] = 'Error deleting post: ' . $db->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method';
}

// Send the response
echo json_encode($response);
