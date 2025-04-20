<?php
/**
 * This script retrieves the details of a specific bug report for display in a modal.
 */

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Validate ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid bug ID'
    ]);
    exit;
}

$bug_id = (int)$_GET['id'];

try {
    // Load database connection
    require_once '../db_connect.php';
    $db = get_db_connection();
    
    // Get bug details
    $stmt = $db->prepare("SELECT * FROM bug_reports WHERE id = :id");
    $stmt->bindValue(':id', $bug_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $bug = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$bug) {
        echo json_encode([
            'success' => false,
            'message' => 'Bug report not found'
        ]);
        exit;
    }
    
    // Format status for display
    $status_display = ucwords(str_replace('_', ' ', $bug['status']));
    
    // Get severity class
    $severity_class = strtolower($bug['severity']);
    
    // Format screenshots
    $screenshots = [];
    if (!empty($bug['screenshot_paths'])) {
        $screenshot_paths = explode('|', $bug['screenshot_paths']);
        foreach ($screenshot_paths as $path) {
            $screenshots[] = "../uploads/bug_screenshots/" . $path;
        }
    }
    
    // Build HTML for bug details
    $html = <<<HTML
    <div class="detail-section">
        <h2>Bug Report #{$bug['id']}: {$bug['title']}</h2>
        
        <div class="detail-meta">
            <div class="detail-meta-item">
                <div class="detail-meta-label">Severity</div>
                <div class="detail-meta-value">
                    <span class="severity-tag {$severity_class}">{$bug['severity']}</span>
                </div>
            </div>
            
            <div class="detail-meta-item">
                <div class="detail-meta-label">Status</div>
                <div class="detail-meta-value">
                    <span class="status-tag {$bug['status']}">{$status_display}</span>
                </div>
            </div>
            
            <div class="detail-meta-item">
                <div class="detail-meta-label">Version</div>
                <div class="detail-meta-value">{$bug['version']}</div>
            </div>
            
            <div class="detail-meta-item">
                <div class="detail-meta-label">Operating System</div>
                <div class="detail-meta-value">{$bug['operating_system']}</div>
            </div>
HTML;

    // Add browser if available
    if (!empty($bug['browser'])) {
        $html .= <<<HTML
            <div class="detail-meta-item">
                <div class="detail-meta-label">Browser</div>
                <div class="detail-meta-value">{$bug['browser']}</div>
            </div>
HTML;
    }

    // Add submission date
    $created_date = date('F j, Y \a\t g:i a', strtotime($bug['created_at']));
    $html .= <<<HTML
            <div class="detail-meta-item">
                <div class="detail-meta-label">Submitted</div>
                <div class="detail-meta-value">{$created_date}</div>
            </div>
HTML;

    // Add email if available
    if (!empty($bug['email'])) {
        $html .= <<<HTML
            <div class="detail-meta-item">
                <div class="detail-meta-label">Reporter Email</div>
                <div class="detail-meta-value">{$bug['email']}</div>
            </div>
HTML;
    }

    $html .= "</div>"; // Close detail-meta

    // Steps to reproduce
    $html .= <<<HTML
        <h3>Steps to Reproduce</h3>
        <div class="detail-content">
            <pre>{$bug['steps_to_reproduce']}</pre>
        </div>
HTML;

    // Actual result
    $html .= <<<HTML
        <h3>Actual Result</h3>
        <div class="detail-content">
            <pre>{$bug['actual_result']}</pre>
        </div>
HTML;

    // Expected result
    $html .= <<<HTML
        <h3>Expected Result</h3>
        <div class="detail-content">
            <pre>{$bug['expected_result']}</pre>
        </div>
HTML;

    // Screenshots
    if (!empty($screenshots)) {
        $html .= "<h3>Screenshots</h3><div class=\"screenshots-container\">";
        
        foreach ($screenshots as $screenshot) {
            $html .= "<img src=\"{$screenshot}\" alt=\"Bug Screenshot\" class=\"screenshot\" onclick=\"openImageFullscreen(this.src)\">";
        }
        
        $html .= "</div>";
    }

    // Update status form
    $html .= <<<HTML
        <div class="update-status-container">
            <h3>Update Status</h3>
            <form method="post" action="admin-feedback.php?tab=bugs">
                <input type="hidden" name="action" value="update_bug_status">
                <input type="hidden" name="bug_id" value="{$bug['id']}">
                <div class="status-update-controls">
                    <select name="status" class="status-select">
                    </select>
                    <button type="submit" class="btn update-btn">Update Status</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Function to open image in fullscreen
        function openImageFullscreen(src) {
            const fullscreenContainer = document.createElement('div');
            fullscreenContainer.className = 'fullscreen-image-container';
            
            const img = document.createElement('img');
            img.src = src;
            img.className = 'fullscreen-image';
            
            const closeBtn = document.createElement('span');
            closeBtn.className = 'fullscreen-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = function() {
                document.body.removeChild(fullscreenContainer);
            };
            
            fullscreenContainer.appendChild(img);
            fullscreenContainer.appendChild(closeBtn);
            
            fullscreenContainer.onclick = function(e) {
                if (e.target === fullscreenContainer) {
                    document.body.removeChild(fullscreenContainer);
                }
            };
            
            document.body.appendChild(fullscreenContainer);
        }
    </script>
HTML;

    // Return success response with HTML
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>