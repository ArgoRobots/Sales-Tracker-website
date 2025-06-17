<?php

/**
 * Searching users for @mentions
 * 
 * This endpoint searches for users based on a query string and returns results
 * formatted for the @mentions dropdown.
 */

// Start session and include necessary files
session_start();
require_once '../../db_connect.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Get query parameters
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';
    $post_id = isset($_GET['postId']) ? intval($_GET['postId']) : 0;

    // Connect to the database
    $db = get_db_connection();
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Get users who have commented on the post
    $commenters = [];
    if ($post_id > 0) {
        $sql_commenters = "
            SELECT DISTINCT u.id, u.username, u.avatar, u.role
            FROM community_users u
            JOIN community_comments c ON u.id = c.user_id
            WHERE c.post_id = ?
            ORDER BY c.created_at DESC
        ";

        $stmt = $db->prepare($sql_commenters);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $db->error);
        }

        $stmt->bind_param('i', $post_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $commenters[$row['id']] = $row;
        }
        $stmt->close();

        // Get the post author if not already in commenters
        $sql_author = "
            SELECT DISTINCT u.id, u.username, u.avatar, u.role
            FROM community_users u
            JOIN community_posts p ON u.id = p.user_id
            WHERE p.id = ?
        ";

        $stmt = $db->prepare($sql_author);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $db->error);
        }

        $stmt->bind_param('i', $post_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($author = $result->fetch_assoc()) {
            if (!isset($commenters[$author['id']])) {
                $commenters[$author['id']] = $author;
            }
        }
        $stmt->close();
    }

    // If query is empty (just '@'), show only commenters and post author
    if (empty($query)) {
        // Return combined commenters and author (already collected earlier)
        echo json_encode(['users' => array_values($commenters)]);
        exit;
    }
    // For non-empty queries, proceed with the existing search logic
    $search_exact_start = $query . '%';
    $search_anywhere = '%' . $query . '%';

    $users = [];

    // First, get exact start matches
    $sql_exact_start = "
        SELECT id, username, avatar, role
        FROM community_users
        WHERE username LIKE ?
        ORDER BY username ASC
        LIMIT 10
    ";

    $stmt = $db->prepare($sql_exact_start);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $db->error);
    }

    $stmt->bind_param('s', $search_exact_start);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[$row['id']] = $row;
    }
    $stmt->close();

    // Then, if we have fewer than 10 results, get partial matches anywhere in the username
    if (count($users) < 10) {
        $ids_to_exclude = !empty($users) ? implode(',', array_keys($users)) : '0';
        $sql_anywhere = "
            SELECT id, username, avatar, role
            FROM community_users
            WHERE username LIKE ? AND id NOT IN ($ids_to_exclude)
            ORDER BY username ASC
            LIMIT " . (10 - count($users));

        $stmt = $db->prepare($sql_anywhere);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $db->error);
        }

        $stmt->bind_param('s', $search_anywhere);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $users[$row['id']] = $row;
        }
        $stmt->close();
    }

    // Combine results, giving priority to commenters and the post author
    $combined_users = [];

    // First add exact matches from commenters
    foreach ($commenters as $id => $user) {
        if (stripos($user['username'], $query) === 0) {
            $combined_users[$id] = $user;
            unset($commenters[$id]);
            unset($users[$id]);
        }
    }

    // Then add exact matches from general users
    foreach ($users as $id => $user) {
        if (stripos($user['username'], $query) === 0) {
            $combined_users[$id] = $user;
            unset($users[$id]);
        }
    }

    // Then add remaining commenters
    foreach ($commenters as $id => $user) {
        $combined_users[$id] = $user;
        unset($users[$id]);
    }

    // Finally add remaining users
    foreach ($users as $id => $user) {
        $combined_users[$id] = $user;
    }

    // Format the results for the @mentions dropdown
    $response = [
        'users' => array_values($combined_users)
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    error_log('Error in mentions/search.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
exit;
