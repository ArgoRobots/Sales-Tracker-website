<?php
require_once 'statistics.php';
require_once 'db_connect.php';

// Get the latest version dynamically
function getLatestVersion()
{
    $downloadDir = 'resources/downloads/';
    if (!is_dir($downloadDir)) {
        return null;
    }

    $files = glob($downloadDir . 'Argo Sales Tracker Installer V.*.exe');
    if (empty($files)) {
        return null;
    }

    // Sort files to get the latest version
    usort($files, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    return basename($files[0]);
}

// Get version from URL parameter or find latest
$requestedVersion = $_GET['version'] ?? null;
if ($requestedVersion) {
    $filename = 'Argo Sales Tracker Installer V.' . $requestedVersion . '.exe';
} else {
    $filename = getLatestVersion();
}

if (!$filename) {
    http_response_code(404);
    echo 'No installer found.';
    exit;
}

$file = 'resources/downloads/' . $filename;

// Check if file exists
if (!file_exists($file)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

// Extract version from filename for tracking
preg_match('/V\.([0-9.]+)\.exe$/', $filename, $matches);
$version = $matches[1] ?? 'unknown';

// Track the download
track_event('download', $version);

// Update download count in database
try {
    $db = get_db_connection();
    $stmt = $db->prepare("UPDATE version_history SET download_count = download_count + 1 WHERE version = ?");
    $stmt->bind_param('s', $version);
    $stmt->execute();
} catch (Exception $e) {
    error_log('Database error in get_installer.php: ' . $e->getMessage());
}

// Clean any output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Transfer-Encoding: binary');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Serve the file
readfile($file);
exit;
