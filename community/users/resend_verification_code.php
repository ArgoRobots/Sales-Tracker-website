<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';
require_once '../../email_sender.php';

// Check if temp_user_id is set
if (!isset($_SESSION['temp_user_id'])) {
    // Redirect to registration page if no temp user ID exists
    header('Location: register.php?error=no_session');
    exit;
}

$user_id = $_SESSION['temp_user_id'];

// Get user details from database
$db = get_db_connection();
$stmt = $db->prepare('SELECT id, email, username, email_verified FROM community_users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: register.php?error=user_not_found');
    exit;
}

// Check if user is already verified
if ($user['email_verified'] == 1) {
    header('Location: login.php?message=already_verified');
    exit;
}

// Generate a new verification code
$new_verification_code = generate_verification_code();

// Update the database with the new verification code
$stmt = $db->prepare('UPDATE community_users SET verification_code = ? WHERE id = ?');
$stmt->bind_param('si', $new_verification_code, $user_id);
$update_result = $stmt->execute();
$stmt->close();

if (!$update_result) {
    header('Location: verify_code.php?error=update_failed');
    exit;
}

// Send the verification email
$email_sent = send_verification_email($user['email'], $new_verification_code, $user['username']);

if ($email_sent) {
    // Redirect back to verification page with success message
    header('Location: verify_code.php?message=code_resent');
    exit;
} else {
    // Redirect back with error message
    header('Location: verify_code.php?error=email_failed');
    exit;
}
