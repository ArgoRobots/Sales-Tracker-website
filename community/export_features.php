<?php
/**
 * This script exports feature requests to CSV format for admin use.
 */

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied";
    exit;
}

// Load database connection
require_once '../db_connect.php';
$db = get_db_connection();

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;
$search_query = isset($_GET['search']) ? $_GET['search'] : null;

// Query to get feature requests with optional filters
$query = "SELECT * FROM feature_requests";
$conditions = [];
$params = [];

if ($status_filter !== null) {
    $conditions[] = "status = :status";
    $params[':status'] = $status_filter;
}

if ($search_query !== null) {
    $conditions[] = "(title LIKE :search OR description LIKE :search OR benefit LIKE :search OR examples LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value, SQLITE3_TEXT);
}

$result = $stmt->execute();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=feature_requests_export_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel to correctly display UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV header row
fputcsv($output, [
    'ID',
    'Title',
    'Category',
    'Priority',
    'Description',
    'Business Benefit',
    'Examples/References',
    'Email',
    'Status',
    'Created Date'
]);

// Write data rows
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['title'],
        $row['category'],
        $row['priority'],
        $row['description'],
        $row['benefit'],
        $row['examples'],
        $row['email'],
        $row['status'],
        $row['created_at']
    ]);
}

// Close output stream
fclose($output);
exit;