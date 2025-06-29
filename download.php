<?php
require_once 'statistics.php';
require_once 'db_connect.php';

// Set the file to download
$file = 'resources/downloads/Argo Sales Tracker Installer V.1.0.3.exe';
$version = '1.0.3';

// Check if file exists
if (file_exists($file)) {
    track_event('download', $version);

    // Also increment the download count in version_history if the table exists
    $db = get_db_connection();
    $stmt = $db->prepare("UPDATE version_history SET download_count = download_count + 1 WHERE version = ?");
    $stmt->bind_param('s', $version);
    $stmt->execute();

    // Set headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));

    // Read file and output
    readfile($file);
    exit;
} else {
    echo 'File not found.';
}
