<?php
session_start();

// Load environment variables from .env file
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Type: application/json');

// Configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB max
define('ALLOWED_MIME_TYPES', ['application/json', 'text/plain']);
define('DATA_DIR', 'admin/data-logs');
define('MAX_UPLOADS_PER_HOUR', 100); // Rate limiting
define('MAX_FILENAME_LENGTH', 255);

// Authentication configuration
define('API_KEY', $_ENV['UPLOAD_API_KEY']); 
define('ALLOWED_USER_AGENT', 'ArgoSalesTracker'); // Expected User-Agent prefix

// Authenticate request
function authenticateRequest()
{
    // Check API key in header
    $provided_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if (empty($provided_key) || !hash_equals(API_KEY, $provided_key)) {
        logSecurityEvent('invalid_api_key', $provided_key);
        return false;
    }

    // Check User-Agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (strpos($user_agent, ALLOWED_USER_AGENT) !== 0) {
        logSecurityEvent('invalid_user_agent', $user_agent);
        return false;
    }

    return true;
}

// Rate limiting check (enhanced with authentication)
function checkRateLimit()
{
    // Use API key in rate limiting to prevent API key sharing abuse
    $api_key = $_SERVER['HTTP_X_API_KEY'] ?? 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_key = md5($api_key . $ip); // Combine API key and IP for rate limiting
    $rate_file = sys_get_temp_dir() . '/upload_rate_' . $rate_key;

    $current_time = time();
    $uploads = [];

    // Read existing rate data
    if (file_exists($rate_file)) {
        $data = file_get_contents($rate_file);
        $uploads = json_decode($data, true) ?: [];
    }

    // Remove uploads older than 1 hour
    $uploads = array_filter($uploads, function ($timestamp) use ($current_time) {
        return ($current_time - $timestamp) < 3600;
    });

    // Check if rate limit exceeded
    if (count($uploads) >= MAX_UPLOADS_PER_HOUR) {
        return false;
    }

    // Add current upload
    $uploads[] = $current_time;
    file_put_contents($rate_file, json_encode($uploads));

    return true;
}

// Validate JSON content
function validateJsonContent($content)
{
    // Attempt to decode the content to ensure it's valid JSON
    json_decode($content);

    // Check if JSON decoding resulted in an error
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Uploaded content not in correct JSON format: " . json_last_error_msg());
        return false;
    }

    // Check for potentially malicious content
    $dangerous_patterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/javascript:/i',
        '/vbscript:/i',
        '/onload=/i',
        '/onerror=/i',
        '/eval\s*\(/i',
        '/document\.cookie/i',
        '/document\.write/i',
        '/window\.location/i',
        '/<\?php/i',
        '/<\%/i'
    ];

    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            error_log("Malicious content detected in upload: " . $pattern);
            return false;
        }
    }

    return true;
}

// Sanitize filename
function sanitizeFilename($filename)
{
    // Remove any path traversal attempts
    $filename = basename($filename);

    // Remove dangerous characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

    // Limit length
    if (strlen($filename) > MAX_FILENAME_LENGTH) {
        $filename = substr($filename, 0, MAX_FILENAME_LENGTH);
    }

    return $filename;
}

// Log security events
function logSecurityEvent($event, $details = '')
{
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'api_key_provided' => !empty($_SERVER['HTTP_X_API_KEY']),
        'event' => $event,
        'details' => $details
    ];
}

// Main upload handling
try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Only POST method allowed']);
        logSecurityEvent('invalid_method', $_SERVER['REQUEST_METHOD']);
        exit;
    }

    // Authenticate request FIRST
    if (!authenticateRequest()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Rate limiting check
    if (!checkRateLimit()) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded']);
        logSecurityEvent('rate_limit_exceeded');
        exit;
    }

    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error_code = $_FILES['file']['error'] ?? 'no_file';
        http_response_code(400);
        echo json_encode(['error' => 'Upload error', 'code' => $error_code]);
        logSecurityEvent('upload_error', $error_code);
        exit;
    }

    $uploaded_file = $_FILES['file'];

    // Validate file size
    if ($uploaded_file['size'] > MAX_FILE_SIZE) {
        http_response_code(413);
        echo json_encode(['error' => 'File too large', 'max_size' => MAX_FILE_SIZE]);
        logSecurityEvent('file_too_large', $uploaded_file['size']);
        exit;
    }

    if ($uploaded_file['size'] === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Empty file']);
        logSecurityEvent('empty_file');
        exit;
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $uploaded_file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, ALLOWED_MIME_TYPES, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type', 'detected' => $mime_type]);
        logSecurityEvent('invalid_mime_type', $mime_type);
        exit;
    }

    // Read and validate file content
    $content = file_get_contents($uploaded_file['tmp_name']);
    if ($content === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to read uploaded file']);
        logSecurityEvent('read_failure');
        exit;
    }

    // Validate JSON content
    if (!validateJsonContent($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or malicious content']);
        logSecurityEvent('invalid_content');
        exit;
    }

    // Create secure directory if needed
    if (!is_dir(DATA_DIR)) {
        if (!mkdir(DATA_DIR, 0755, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create data directory']);
            logSecurityEvent('mkdir_failure');
            exit;
        }

        // Create .htaccess to prevent direct access
        $htaccess_content = "Order deny,allow\nDeny from all\n";
        file_put_contents(DATA_DIR . '/.htaccess', $htaccess_content);
    }

    // Generate secure filename
    $timestamp = date('Ymd_His');
    $random_suffix = bin2hex(random_bytes(4));
    $filename = DATA_DIR . "/argo_data_{$timestamp}_{$random_suffix}.json";

    // Ensure filename doesn't already exist
    $counter = 1;
    $base_filename = $filename;
    while (file_exists($filename)) {
        $filename = str_replace('.json', "_{$counter}.json", $base_filename);
        $counter++;

        if ($counter > 1000) {
            http_response_code(500);
            echo json_encode(['error' => 'Unable to generate unique filename']);
            logSecurityEvent('filename_generation_failure');
            exit;
        }
    }

    // Write file with secure permissions
    $bytes_written = file_put_contents($filename, $content, LOCK_EX);

    if ($bytes_written === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save file']);
        logSecurityEvent('write_failure');
        exit;
    }

    // Set secure permissions
    chmod($filename, 0644);

    // Success response
    $response = [
        'status' => 'success',
        'file' => basename($filename),
        'bytes' => $bytes_written,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    logSecurityEvent('exception', $e->getMessage());
    error_log("Upload error: " . $e->getMessage());
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    logSecurityEvent('fatal_error', $e->getMessage());
    error_log("Upload fatal error: " . $e->getMessage());
}
