<?php
session_start();

// Unset all user session variables
if (isset($_SESSION['user_id'])) {
    // Clear user-specific session data
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    unset($_SESSION['role']);
    unset($_SESSION['email_verified']);
}

// Redirect to login page
header('Location: login.php');
exit;
