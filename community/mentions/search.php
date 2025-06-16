<?php
// Updated version of search.php with improved search algorithm

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

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
if (!$is_logged_in) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get query parameters
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$post_id = isset($_GET['postId']) ? intval($_GET['postId']) : 0;

// Connect to the database
$db = get_db_connection();

// Get users who have commented on the post (if post_id is provided)
$commenters = [];
if ($post_id > 0) {
    $sql_commenters = "
        SELECT DISTINCT u.id, u.username, u.avatar, u.role
        FROM community_users u
        JOIN community_comments c ON u.id = c.user_id
        WHERE c.post_id = ?
        ORDER BY u.username ASC
    ";

    $stmt = $db->prepare($sql_commenters);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
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
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($author = $result->fetch_assoc()) {
        if (!isset($commenters[$author['id']])) {
            $commenters[$author['id']] = $author;
        }
    }

    $stmt->close();
}

// Prepare query string for different matching patterns
$search_exact_start = '';
$search_anywhere = '';

if (!empty($query)) {
    // For exact start matches (highest priority)
    $search_exact_start = $query . '%';

    // For matches anywhere in the username
    $search_anywhere = '%' . $query . '%';
}

// Get users matching the query, prioritizing exact start matches
$users = [];

// Only run the search if we have a query
if (!empty($query)) {
    // First, get exact start matches
    $sql_exact_start = "
        SELECT id, username, avatar, role
        FROM community_users
        WHERE username LIKE ?
        ORDER BY username ASC
        LIMIT 10
    ";

    $stmt = $db->prepare($sql_exact_start);
    $stmt->bind_param('s', $search_exact_start);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $users[$row['id']] = $row;
    }
    $stmt->close();

    // Then, if we have fewer than 10 results, get partial matches anywhere in the username
    if (count($users) < 10) {
        $sql_anywhere = "
            SELECT id, username, avatar, role
            FROM community_users
            WHERE username LIKE ? AND id NOT IN (" . implode(',', array_keys($users) ?: [0]) . ")
            ORDER BY username ASC
            LIMIT " . (10 - count($users));

        $stmt = $db->prepare($sql_anywhere);
        $stmt->bind_param('s', $search_anywhere);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $users[$row['id']] = $row;
        }
        $stmt->close();
    }
} else {
    // If no query, show recent users from the post/comment thread
    $users = $commenters;
}

// Combine results, giving priority to commenters and the post author
$combined_users = [];

// First add exact matches from commenters
foreach ($commenters as $id => $user) {
    if (!empty($query) && stripos($user['username'], $query) === 0) {
        $combined_users[$id] = $user;
        unset($commenters[$id]);
        unset($users[$id]);
    }
}

// Then add exact matches from general users
foreach ($users as $id => $user) {
    if (!empty($query) && stripos($user['username'], $query) === 0) {
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
exit;
