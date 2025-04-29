<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';

// Clear remember token if user is logged in
if (isset($_SESSION['user_id'])) {
    // Clear remember token from database and cookie
    clear_remember_token($_SESSION['user_id']);

    // Clear user-specific session data
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    unset($_SESSION['role']);
    unset($_SESSION['email_verified']);
    unset($_SESSION['avatar']);
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
