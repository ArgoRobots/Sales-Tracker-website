<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Invalid request'
];

// Check if user is logged in
if (!is_user_logged_in()) {
    $response['message'] = 'You must be logged in to vote';
    echo json_encode($response);
    exit;
}

$current_user = get_current_user();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $vote_type = isset($_POST['vote_type']) ? intval($_POST['vote_type']) : 0;

    // Basic validation
    if (empty($post_id)) {
        $response['message'] = 'Missing required parameters';
    } elseif ($vote_type !== 1 && $vote_type !== -1) {
        $response['message'] = 'Invalid vote type';
    } else {
        // Verify post exists
        $post = get_post($post_id);

        if (!$post) {
            $response['message'] = 'Post not found';
        } else {
            // Process the vote
            $result = vote_post($post_id, $current_user['email'], $vote_type);

            if ($result !== false) {
                // Connect vote to user account
                $db = get_db_connection();

                // Check if user already has a vote for this post
                $stmt = $db->prepare('SELECT id FROM community_votes WHERE post_id = :post_id AND user_email = :user_email');
                $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
                $stmt->bindValue(':user_email', $current_user['email'], SQLITE3_TEXT);
                $vote_record = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

                // If vote record exists, update it
                if ($vote_record) {
                    $stmt = $db->prepare('UPDATE community_votes SET user_id = :user_id WHERE id = :vote_id');
                    $stmt->bindValue(':user_id', $current_user['id'], SQLITE3_INTEGER);
                    $stmt->bindValue(':vote_id', $vote_record['id'], SQLITE3_INTEGER);
                    $stmt->execute();
                }

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
