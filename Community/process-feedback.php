<?php
/**
 * Process Bug Reports and Feature Requests
 * 
 * This script handles form submissions from the bug report and feature request pages.
 * It validates the data, stores it in the database, and sends email notifications.
 */

// Set headers for JSON response
header('Content-Type: application/json');

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Initialize response
$response = [
    'success' => false,
    'message' => 'An error occurred while processing your request'
];

try {
    // Load database connection
    require_once '../db_connect.php';
    
    // Get the database connection
    $db = get_db_connection();
    
    // Create feedback table if it doesn't exist
    create_feedback_table($db);
    
    // Get form data
    $report_type = $_POST['report_type'] ?? '';
    
    // Process based on report type
    if ($report_type === 'bug') {
        process_bug_report($db);
    } elseif ($report_type === 'feature') {
        process_feature_request($db);
    } else {
        throw new Exception('Invalid report type');
    }
    
    // Send email notification to administrators
    send_notification_email($report_type);
    
    // Return success response
    $response = [
        'success' => true,
        'message' => 'Your feedback has been submitted successfully'
    ];
    
} catch (Exception $e) {
    // Log the error
    error_log("Feedback processing error: " . $e->getMessage());
    
    // Return error response
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Return JSON response
echo json_encode($response);
exit;

/**
 * Create feedback table if it doesn't exist
 * 
 * @param SQLite3 $db Database connection
 */
function create_feedback_table($db) {
    // Create table for bug reports
    $db->exec("CREATE TABLE IF NOT EXISTS bug_reports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        severity TEXT NOT NULL,
        version TEXT NOT NULL,
        operating_system TEXT NOT NULL,
        browser TEXT,
        steps_to_reproduce TEXT NOT NULL,
        actual_result TEXT NOT NULL,
        expected_result TEXT NOT NULL,
        email TEXT,
        screenshot_paths TEXT,
        ip_address TEXT,
        user_agent TEXT,
        status TEXT DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create table for feature requests
    $db->exec("CREATE TABLE IF NOT EXISTS feature_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        category TEXT NOT NULL,
        priority TEXT,
        description TEXT NOT NULL,
        benefit TEXT NOT NULL,
        examples TEXT,
        email TEXT,
        mockup_paths TEXT,
        ip_address TEXT,
        user_agent TEXT,
        status TEXT DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

/**
 * Process bug report submission
 * 
 * @param SQLite3 $db Database connection
 */
function process_bug_report($db) {
    // Validate required fields
    $required_fields = ['title', 'severity', 'version', 'operating_system', 'steps_to_reproduce', 'actual_result', 'expected_result'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Required field '{$field}' is missing");
        }
    }
    
    // Prepare statement
    $stmt = $db->prepare("INSERT INTO bug_reports 
        (title, severity, version, operating_system, browser, steps_to_reproduce, 
        actual_result, expected_result, email, screenshot_paths, ip_address, user_agent) 
        VALUES 
        (:title, :severity, :version, :operating_system, :browser, :steps_to_reproduce, 
        :actual_result, :expected_result, :email, :screenshot_paths, :ip_address, :user_agent)");
    
    // Bind values
    $stmt->bindValue(':title', $_POST['title'], SQLITE3_TEXT);
    $stmt->bindValue(':severity', $_POST['severity'], SQLITE3_TEXT);
    $stmt->bindValue(':version', $_POST['version'], SQLITE3_TEXT);
    $stmt->bindValue(':operating_system', $_POST['operating_system'], SQLITE3_TEXT);
    $stmt->bindValue(':browser', $_POST['browser'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':steps_to_reproduce', $_POST['steps_to_reproduce'], SQLITE3_TEXT);
    $stmt->bindValue(':actual_result', $_POST['actual_result'], SQLITE3_TEXT);
    $stmt->bindValue(':expected_result', $_POST['expected_result'], SQLITE3_TEXT);
    $stmt->bindValue(':email', $_POST['email'] ?? '', SQLITE3_TEXT);
    
    // Process screenshots
    $screenshot_paths = [];
    
    if (!empty($_FILES['screenshot']['name'][0])) {
        $screenshot_paths = process_file_uploads('screenshot', 'bug_screenshots');
    }
    
    $stmt->bindValue(':screenshot_paths', implode('|', $screenshot_paths), SQLITE3_TEXT);
    $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);
    $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '', SQLITE3_TEXT);
    
    // Execute the statement
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception('Failed to save bug report');
    }
}

/**
 * Process feature request submission
 * 
 * @param SQLite3 $db Database connection
 */
function process_feature_request($db) {
    // Validate required fields
    $required_fields = ['title', 'category', 'description', 'benefit'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Required field '{$field}' is missing");
        }
    }
    
    // Prepare statement
    $stmt = $db->prepare("INSERT INTO feature_requests 
        (title, category, priority, description, benefit, examples, email, mockup_paths, ip_address, user_agent) 
        VALUES 
        (:title, :category, :priority, :description, :benefit, :examples, :email, :mockup_paths, :ip_address, :user_agent)");
    
    // Bind values
    $stmt->bindValue(':title', $_POST['title'], SQLITE3_TEXT);
    $stmt->bindValue(':category', $_POST['category'], SQLITE3_TEXT);
    $stmt->bindValue(':priority', $_POST['priority'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':description', $_POST['description'], SQLITE3_TEXT);
    $stmt->bindValue(':benefit', $_POST['benefit'], SQLITE3_TEXT);
    $stmt->bindValue(':examples', $_POST['examples'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':email', $_POST['email'] ?? '', SQLITE3_TEXT);
    
    // Process mockups
    $mockup_paths = [];
    
    if (!empty($_FILES['mockup']['name'][0])) {
        $mockup_paths = process_file_uploads('mockup', 'feature_mockups');
    }
    
    $stmt->bindValue(':mockup_paths', implode('|', $mockup_paths), SQLITE3_TEXT);
    $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);
    $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '', SQLITE3_TEXT);
    
    // Execute the statement
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception('Failed to save feature request');
    }
}

/**
 * Process file uploads
 * 
 * @param string $file_input_name The name of the file input
 * @param string $directory The directory to save the files
 * @return array Array of file paths
 */
function process_file_uploads($file_input_name, $directory) {
    $file_paths = [];
    
    // Create directory if it doesn't exist
    $upload_dir = __DIR__ . "/../uploads/{$directory}/";
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Process each file
    $files = $_FILES[$file_input_name];
    
    for ($i = 0; $i < count($files['name']); $i++) {
        // Skip empty files
        if (empty($files['name'][$i])) continue;
        
        // Generate unique filename
        $file_extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;
        
        // Validate file type (only allow images)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($files['type'][$i], $allowed_types)) {
            continue; // Skip invalid files
        }
        
        // Validate file size (5MB max)
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if ($files['size'][$i] > $max_size) {
            continue; // Skip files that are too large
        }
        
        // Move the uploaded file
        if (move_uploaded_file($files['tmp_name'][$i], $file_path)) {
            $file_paths[] = $new_filename;
        }
    }
    
    return $file_paths;
}

/**
 * Send notification email to administrators
 * 
 * @param string $report_type The type of report (bug or feature)
 */
function send_notification_email($report_type) {
    // In a real application, you would send an email to administrators
    // For this example, we'll just log the notification
    
    $report_description = $report_type === 'bug' ? 'Bug Report' : 'Feature Request';
    $report_title = $_POST['title'] ?? 'Untitled Report';
    
    error_log("New {$report_description} received: {$report_title}");
    
    // In a real implementation, you would use something like:
    // mail('admin@argorobots.com', "New {$report_description}: {$report_title}", $email_body, $headers);
}
?>