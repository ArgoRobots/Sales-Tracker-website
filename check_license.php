<?php
/**
 * License Check Endpoint
 * Validates if a license key exists and is activated for AI subscription discount
 */

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Rate limiting
$rate_limit_key = 'license_check_attempts';
$rate_limit_time_key = 'license_check_first_attempt';
$max_attempts = 10;
$window_seconds = 60;

if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = 0;
    $_SESSION[$rate_limit_time_key] = time();
}

// Reset counter if window has passed
if (time() - $_SESSION[$rate_limit_time_key] > $window_seconds) {
    $_SESSION[$rate_limit_key] = 0;
    $_SESSION[$rate_limit_time_key] = time();
}

// Check rate limit
if ($_SESSION[$rate_limit_key] >= $max_attempts) {
    $wait_time = $window_seconds - (time() - $_SESSION[$rate_limit_time_key]);
    http_response_code(429);
    echo json_encode([
        'error' => 'Too many requests. Please wait ' . $wait_time . ' seconds.',
        'valid' => false,
        'rate_limited' => true
    ]);
    exit();
}

$_SESSION[$rate_limit_key]++;

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['license_key']) || empty($input['license_key'])) {
    http_response_code(400);
    echo json_encode(['error' => 'License key is required', 'valid' => false]);
    exit();
}

$license_key = trim($input['license_key']);

// Load database connection
require_once 'db_connect.php';

try {
    // Check if license key exists and is activated
    $stmt = $pdo->prepare("SELECT id, email, activated, activation_date FROM license_keys WHERE license_key = ?");
    $stmt->execute([$license_key]);
    $license = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($license && $license['activated'] == 1) {
        // Valid and activated license
        echo json_encode([
            'valid' => true,
            'message' => 'License verified successfully',
            'email' => $license['email']
        ]);
    } else if ($license) {
        // License exists but not activated
        echo json_encode([
            'valid' => false,
            'message' => 'License key exists but has not been activated'
        ]);
    } else {
        // License not found
        echo json_encode([
            'valid' => false,
            'message' => 'License key not found'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'valid' => false
    ]);
}
