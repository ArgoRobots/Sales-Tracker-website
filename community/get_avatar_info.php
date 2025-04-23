<?php

/**
 * This script returns the user's avatar information as JSON
 */
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db_connect.php';
require_once 'users/user_functions.php';

// Prepare response data
$response = array(
    'logged_in' => false,
    'has_avatar' => false,
    'avatar_url' => '',
    'initial' => '',
    'profile_link' => 'users/login.php',
    'login_link' => 'users/login.php'
);

// Check if user is logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $response['logged_in'] = true;

    // Get user data
    $user = get_user($_SESSION['user_id']);

    if ($user) {
        // Set profile link
        $response['profile_link'] = 'users/profile.php';

        // Check if user has an avatar
        if (!empty($user['avatar'])) {
            $response['has_avatar'] = true;
            $response['avatar_url'] = '/community/' . $user['avatar'];
        } else if (isset($_SESSION['avatar']) && !empty($_SESSION['avatar'])) {
            // Check session for avatar as fallback
            $response['has_avatar'] = true;
            $response['avatar_url'] = $_SESSION['avatar'];
        }

        // Set initial for avatar placeholder
        if (isset($user['username']) && !empty($user['username'])) {
            $response['initial'] = strtoupper(substr($user['username'], 0, 1));
        } else if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
            $response['initial'] = strtoupper(substr($_SESSION['username'], 0, 1));
        }
    }
}

// Return the response as JSON
echo json_encode($response);
