<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Initialize empty array for response
$comments = [];

// Get the post ID from the request
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if ($post_id > 0) {
    // Get the comments for this post
    $comments = get_post_comments($post_id);
}

// Send the response
echo json_encode($comments);