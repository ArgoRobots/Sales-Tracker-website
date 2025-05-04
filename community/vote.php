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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['success'] = false;
    $response['message'] = 'You must be logged in to vote';
    $response['show_message'] = true;
    $response['message_html'] = 'You must be logged in to vote';
    $response['message_style'] = [
        'position' => 'fixed',
        'top' => '20px',
        'left' => '50%',
        'transform' => 'translateX(-50%)',
        'padding' => '10px 20px',
        'backgroundColor' => '#f8d7da',
        'color' => '#842029',
        'borderRadius' => '4px',
        'zIndex' => '1000',
        'boxShadow' => '0 2px 4px rgba(0,0,0,0.2)'
    ];
    $response['message_duration'] = 3000; // milliseconds
    echo json_encode($response);
    exit;
}

// Get user information from session
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'] ?? '';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    $vote_type = isset($_POST['vote_type']) ? intval($_POST['vote_type']) : 0;

    // Basic validation
    if (empty($post_id) && empty($comment_id)) {
        $response['message'] = 'Missing required parameters';
    } elseif ($vote_type !== 1 && $vote_type !== -1) {
        $response['message'] = 'Invalid vote type';
    } else {
        $db = get_db_connection();

        if ($post_id > 0) {
            // Voting on a post
            // Verify post exists
            $post = get_post($post_id);

            if (!$post) {
                $response['message'] = 'Post not found';
            } else {
                // Check if user is the author of the post
                if ($post['user_id'] == $user_id) {
                    $response['success'] = false;
                    $response['message'] = 'You cannot vote on your own post';
                    $response['show_message'] = true;
                    $response['message_html'] = 'You cannot vote on your own post';
                    $response['message_style'] = [
                        'position' => 'fixed',
                        'top' => '20px',
                        'left' => '50%',
                        'transform' => 'translateX(-50%)',
                        'padding' => '10px 20px',
                        'backgroundColor' => '#f8d7da',
                        'color' => '#842029',
                        'borderRadius' => '4px',
                        'zIndex' => '1000',
                        'boxShadow' => '0 2px 4px rgba(0,0,0,0.2)'
                    ];
                    $response['message_duration'] = 3000; // milliseconds
                } else {
                    // Process the vote
                    $result = vote_post($post_id, $email, $vote_type);

                    if ($result !== false) {
                        // Connect vote to user account
                        if ($user_id > 0) {
                            // Update the vote record with user_id
                            $stmt = $db->prepare('UPDATE community_votes SET user_id = ? WHERE post_id = ? AND user_email = ?');
                            $stmt->bind_param('iis', $user_id, $post_id, $email);
                            $stmt->execute();
                            $stmt->close();
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
        } elseif ($comment_id > 0) {
            // Voting on a comment
            // Verify comment exists and check if user is the author
            $stmt = $db->prepare('SELECT id, user_id FROM community_comments WHERE id = ?');
            $stmt->bind_param('i', $comment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $comment = $result->fetch_assoc();
            $stmt->close();

            if (!$comment) {
                $response['message'] = 'Comment not found';
            } elseif ($comment['user_id'] == $user_id) {
                $response['success'] = false;
                $response['message'] = 'You cannot vote on your own comment';
                $response['show_message'] = true;
                $response['message_html'] = 'You cannot vote on your own comment';
                $response['message_style'] = [
                    'position' => 'fixed',
                    'top' => '20px',
                    'left' => '50%',
                    'transform' => 'translateX(-50%)',
                    'padding' => '10px 20px',
                    'backgroundColor' => '#f8d7da',
                    'color' => '#842029',
                    'borderRadius' => '4px',
                    'zIndex' => '1000',
                    'boxShadow' => '0 2px 4px rgba(0,0,0,0.2)'
                ];
                $response['message_duration'] = 3000; // milliseconds
            } else {
                // Process the comment vote
                $result = vote_comment($comment_id, $email, $vote_type);

                if ($result !== false) {
                    // Connect vote to user account
                    if ($user_id > 0) {
                        // Update the vote record with user_id
                        $stmt = $db->prepare('UPDATE comment_votes SET user_id = ? WHERE comment_id = ? AND user_email = ?');
                        $stmt->bind_param('iis', $user_id, $comment_id, $email);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $response = [
                        'success' => true,
                        'message' => 'Comment vote recorded successfully',
                        'new_vote_count' => $result['new_vote_count'],
                        'user_vote' => $result['user_vote']
                    ];
                } else {
                    $response['message'] = 'Error recording comment vote';
                }
            }
        }
    }
}

// Send the response
echo json_encode($response);
