<?php
// Start session to check for admin status
session_start();
require_once 'statistics.php';

// Get available versions from filesystem
function getOlderVersions()
{
    $versionsPath = __DIR__ . '/resources/downloads/versions/';
    $versions = [];

    // Check if directory exists
    if (!is_dir($versionsPath)) {
        error_log("Versions directory does not exist: " . $versionsPath);
        return $versions;
    }

    // Scan the versions directory for version folders
    $versionFolders = scandir($versionsPath);

    foreach ($versionFolders as $folder) {
        if ($folder === '.' || $folder === '..') continue;

        $versionPath = $versionsPath . $folder . '/';

        // Check if it's a directory
        if (!is_dir($versionPath)) {
            continue;
        }

        // Look for installer files in the version directory
        $files = scandir($versionPath);

        foreach ($files as $file) {
            // Look for installer files with the pattern "Argo Sales Tracker Installer V.{version}.exe"
            if (preg_match('/^Argo Sales Tracker Installer V\.(.+)\.exe$/i', $file, $matches)) {
                $version = $matches[1];
                $filepath = $versionPath . $file;

                if (file_exists($filepath)) {
                    $filesize = filesize($filepath);
                    $modified = filemtime($filepath);

                    $versionData = [
                        'version' => $version,
                        'filename' => $file,
                        'filepath' => $filepath,
                        'filesize' => $filesize,
                        'modified' => $modified,
                        'folder' => $folder
                    ];

                    $versions[] = $versionData;
                }
                break; // Only one installer per version folder
            }
        }
    }

    // Sort versions in descending order
    usort($versions, function ($a, $b) {
        return version_compare($b['version'], $a['version']);
    });

    return $versions;
}

function getLatestVersion()
{
    $versions = getOlderVersions();
    return !empty($versions) ? $versions[0] : null;
}

function serveFile($filepath, $filename, $version)
{
    // Clean any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Set headers for download
    header('Content-Type: application/octet-stream');
    header('Content-Transfer-Encoding: binary');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Record the download event for statistics tracking
    track_event('download', $version);

    // Serve the file
    readfile($filepath);
    exit;
}

// Get request parameters
$requestedVersion = $_GET['version'] ?? null;
$requestedFilename = $_GET['filename'] ?? null;

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

// Case 1: Specific version requested
if ($requestedVersion) {
    $versionPath = __DIR__ . "/resources/downloads/versions/$requestedVersion/";

    if (is_dir($versionPath)) {
        $files = scandir($versionPath);
        foreach ($files as $file) {
            if (preg_match('/^Argo Sales Tracker Installer V\.(.+)\.exe$/i', $file)) {
                $filePath = $versionPath . $file;
                if (file_exists($filePath)) {
                    serveFile($filePath, $file, $requestedVersion);
                }
                break;
            }
        }
    }

    // If we get here, version not found
    http_response_code(404);
    die("Version $requestedVersion not found");
}

// Case 2: Specific filename requested, try to find it in any version
if ($requestedFilename) {
    $versions = getOlderVersions();

    foreach ($versions as $version) {
        if ($version['filename'] === $requestedFilename) {
            serveFile($version['filepath'], $version['filename'], $version['version']);
        }
    }

    // If we get here, filename not found
    http_response_code(404);
    die("File $requestedFilename not found");
}

// Case 3: Default - serve latest version
$latestVersion = getLatestVersion();

if (!$latestVersion) {
    http_response_code(404);
    die('No installer versions available');
}

serveFile($latestVersion['filepath'], $latestVersion['filename'], $latestVersion['version']);
