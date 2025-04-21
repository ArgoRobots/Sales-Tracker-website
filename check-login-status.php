<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');

// Function to check if user is logged in
function is_user_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Initialize response array
$response = [
    'logged_in' => false,
    'display_name' => '',
    'username' => '',
    'avatar' => '',
    'is_admin' => false
];

// Check if user is logged in
if (is_user_logged_in()) {
    // User is logged in, populate response with session data
    $response['logged_in'] = true;
    $response['display_name'] = $_SESSION['display_name'] ?? 'User';
    $response['username'] = $_SESSION['username'] ?? '';
    $response['is_admin'] = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    
    // Check if we have user avatar in session
    if (isset($_SESSION['avatar']) && !empty($_SESSION['avatar'])) {
        $response['avatar'] = $_SESSION['avatar'];
    } else {
        // Try to get avatar from database if we have user_id
        if (isset($_SESSION['user_id'])) {
            require_once 'users/user_functions.php';
            $user = get_user($_SESSION['user_id']);
            if ($user && !empty($user['avatar'])) {
                $response['avatar'] = $user['avatar'];
                // Store in session for future use
                $_SESSION['avatar'] = $user['avatar'];
            }
        }
    }
}

// Return JSON response
echo json_encode($response);
exit;