<?php
// This script resets rate limits for a specific user or all users
require_once '../db_connect.php';

// Function to reset rate limits
function reset_user_rate_limits($user_id = null)
{
    $db = get_db_connection();

    if ($user_id !== null) {
        // Reset rate limits for specific user
        $stmt = $db->prepare('DELETE FROM rate_limits WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();

        if ($result) {
            echo "Rate limits reset successfully for user ID: $user_id\n";
        } else {
            echo "Error resetting rate limits for user ID: $user_id\n";
        }
    } else {
        // Reset rate limits for all users
        $result = $db->exec('DELETE FROM rate_limits');

        if ($result !== false) {
            echo "Rate limits reset successfully for all users\n";
        } else {
            echo "Error resetting rate limits for all users\n";
        }
    }
}

// Get current user ID from session if available
session_start();
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Reset rate limits for current user
if ($current_user_id) {
    reset_user_rate_limits($current_user_id);
    echo '<p>Your rate limits have been reset. You can now create posts normally.</p>';
    echo '<p><a href="../community/index.php">Return to community</a></p>';
} else {
    echo '<p>You need to be logged in to reset your rate limits.</p>';
    echo '<p><a href="../community/users/login.php">Log in</a></p>';
}
