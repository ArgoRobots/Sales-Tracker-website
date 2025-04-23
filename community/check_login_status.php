<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Initialize response array
$response = [
    'logged_in' => $logged_in
];

// Add user data if logged in
if ($logged_in) {
    $response['username'] = $_SESSION['username'] ?? 'Unknown';
    $response['avatar'] = $_SESSION['avatar'] ?? '';
}

// Return JSON response
echo json_encode($response);
