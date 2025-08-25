<?php
session_start();
require_once '../db_connect.php';
require_once 'formatting/formatting_functions.php';

// Check if mentions functionality exists
$mentions_available = file_exists(__DIR__ . '/mentions/mentions.php');
if ($mentions_available) {
    require_once 'mentions/mentions.php';
}

header('Content-Type: application/json');

// Only handle AJAX preview requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['preview_request'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$post_type = isset($_POST['post_type']) ? trim($_POST['post_type']) : '';

// Bug-specific fields
$bug_location = isset($_POST['bug_location']) ? trim($_POST['bug_location']) : '';
$bug_version = isset($_POST['bug_version']) ? trim($_POST['bug_version']) : '';
$bug_steps = isset($_POST['bug_steps']) ? trim($_POST['bug_steps']) : '';
$bug_expected = isset($_POST['bug_expected']) ? trim($_POST['bug_expected']) : '';
$bug_actual = isset($_POST['bug_actual']) ? trim($_POST['bug_actual']) : '';

// Get current user info
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT username, email, avatar FROM community_users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_user = $result->fetch_assoc();
    $stmt->close();
}

// Default user info if not logged in
if (!$current_user) {
    $current_user = [
        'username' => 'Preview User',
        'email' => '',
        'avatar' => null
    ];
}

// Format content using existing functions
function formatPreviewContent($text)
{
    if (empty($text)) return '';

    // Use the existing formatting function
    $formatted = render_formatted_text($text);

    // Process mentions if function exists
    if (function_exists('process_mentions')) {
        $formatted = process_mentions($formatted);
    }

    return $formatted;
}

// Build response data
$response = [
    'title' => htmlspecialchars($title),
    'post_type' => $post_type,
    'content' => formatPreviewContent($content),
    'user' => [
        'username' => htmlspecialchars($current_user['username']),
        'avatar' => $current_user['avatar']
    ],
    'bug_metadata' => []
];

// Add bug metadata if this is a bug report
if ($post_type === 'bug') {
    $response['bug_metadata'] = [
        'location' => [
            'raw' => $bug_location,
            'formatted' => formatPreviewContent($bug_location)
        ],
        'version' => [
            'raw' => $bug_version,
            'formatted' => formatPreviewContent($bug_version)
        ],
        'steps' => [
            'raw' => $bug_steps,
            'formatted' => formatPreviewContent($bug_steps)
        ],
        'expected' => [
            'raw' => $bug_expected,
            'formatted' => formatPreviewContent($bug_expected)
        ],
        'actual' => [
            'raw' => $bug_actual,
            'formatted' => formatPreviewContent($bug_actual)
        ]
    ];
}

echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
