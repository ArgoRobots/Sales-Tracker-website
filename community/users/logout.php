<?php
session_start();

// Unset all user session variables
if (isset($_SESSION['user_id'])) {
    // Clear remember_me cookie if it exists
    if (isset($_COOKIE['remember_me'])) {
        // Update the database to remove token
        require_once '../../db_connect.php';
        $db = get_db_connection();
        $stmt = $db->prepare('UPDATE community_users SET remember_token = NULL WHERE id = :id');
        $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $stmt->execute();
        
        // Delete the cookie by setting expiration to past
        setcookie('remember_me', '', time() - 3600, '/');
    }
    
    // Clear user-specific session data
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['display_name']);
    unset($_SESSION['email']);
    unset($_SESSION['role']);
    unset($_SESSION['email_verified']);
}

// Redirect to login page
header('Location: login.php');
exit;