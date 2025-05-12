<?php
require_once 'statistics.php';

// Set the file to download
$file = 'path/to/your/ArgoSalesTracker.zip';
$version = '1.0'; // Update with current version

// Check if file exists
if (file_exists($file)) {
    // Track the download
    track_event('download', $version);

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
