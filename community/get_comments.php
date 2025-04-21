<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

header('Content-Type: application/json');

$comments = [];
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if ($post_id > 0) {
    $comments = get_post_comments($post_id);
}

// Send the response
echo json_encode($comments);
