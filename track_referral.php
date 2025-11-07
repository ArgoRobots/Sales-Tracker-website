<?php
/**
 * Referral tracking middleware
 * Include this file at the top of pages where you want to track referral sources
 */

require_once 'statistics.php';

// Check if 'source' parameter exists in the URL
if (isset($_GET['source']) && !empty($_GET['source'])) {
    $source_code = trim($_GET['source']);

    // Sanitize the source code (alphanumeric, hyphens, and underscores only)
    if (preg_match('/^[a-zA-Z0-9_-]+$/', $source_code)) {
        // Get current page URL
        $page_url = $_SERVER['REQUEST_URI'];

        // Track the referral visit
        track_referral_visit($source_code, $page_url);
    }
}
