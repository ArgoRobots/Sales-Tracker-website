<?php
/**
 * This script retrieves the details of a specific feature request for display in a modal.
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
        'message' => 'Invalid feature ID'
    ]);
    exit;
}

$feature_id = (int)$_GET['id'];

try {
    // Load database connection
    require_once '../db_connect.php';
    $db = get_db_connection();
    
    // Get feature details
    $stmt = $db->prepare("SELECT * FROM feature_requests WHERE id = :id");
    $stmt->bindValue(':id', $feature_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $feature = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$feature) {
        echo json_encode([
            'success' => false,
            'message' => 'Feature request not found'
        ]);
        exit;
    }
    
    // Format status for display
    $status_display = ucwords(str_replace('_', ' ', $feature['status']));
    
    // Format category for display
    $category_display = ucwords(str_replace('_', ' ', $feature['category']));
    
    // Get priority class
    $priority_class = !empty($feature['priority']) ? strtolower($feature['priority']) : 'none';
    $priority_display = !empty($feature['priority']) ? ucfirst($feature['priority']) : 'Not set';
    
    // Format mockups
    $mockups = [];
    if (!empty($feature['mockup_paths'])) {
        $mockup_paths = explode('|', $feature['mockup_paths']);
        foreach ($mockup_paths as $path) {
            $mockups[] = "../uploads/feature_mockups/" . $path;
        }
    }
    
    // Build HTML for feature details
    $html = <<<HTML
    <div class="detail-section">
        <h2>Feature Request #{$feature['id']}: {$feature['title']}</h2>
        
        <div class="detail-meta">
            <div class="detail-meta-item">
                <div class="detail-meta-label">Category</div>
                <div class="detail-meta-value">{$category_display}</div>
            </div>
            
            <div class="detail-meta-item">
                <div class="detail-meta-label">Priority</div>
                <div class="detail-meta-value">
                    <span class="priority-tag {$priority_class}">{$priority_display}</span>
                </div>
            </div>
            
            <div class="detail-meta-item">
                <div class="detail-meta-label">Status</div>
                <div class="detail-meta-value">
                    <span class="status-tag {$feature['status']}">{$status_display}</span>
                </div>
            </div>
HTML;

    // Add submission date
    $created_date = date('F j, Y \a\t g:i a', strtotime($feature['created_at']));
    $html .= <<<HTML
            <div class="detail-meta-item">
                <div class="detail-meta-label">Submitted</div>
                <div class="detail-meta-value">{$created_date}</div>
            </div>
HTML;

    // Add email if available
    if (!empty($feature['email'])) {
        $html .= <<<HTML
            <div class="detail-meta-item">
                <div class="detail-meta-label">Requester Email</div>
                <div class="detail-meta-value">{$feature['email']}</div>
            </div>
HTML;
    }

    $html .= "</div>"; // Close detail-meta

    // Feature description
    $html .= <<<HTML
        <h3>Feature Description</h3>
        <div class="detail-content">
            <pre>{$feature['description']}</pre>
        </div>
HTML;

    // Business benefit
    $html .= <<<HTML
        <h3>Business Benefit</h3>
        <div class="detail-content">
            <pre>{$feature['benefit']}</pre>
        </div>
HTML;

    // Examples or references (if available)
    if (!empty($feature['examples'])) {
        $html .= <<<HTML
        <h3>Examples or References</h3>
        <div class="detail-content">
            <pre>{$feature['examples']}</pre>
        </div>
HTML;
    }

    // Mockups
    if (!empty($mockups)) {
        $html .= "<h3>Visual Mockups</h3><div class=\"screenshots-container\">";
        
        foreach ($mockups as $mockup) {
            $html .= "<img src=\"{$mockup}\" alt=\"Feature Mockup\" class=\"screenshot\" onclick=\"openImageFullscreen(this.src)\">";
        }
        
        $html .= "</div>";
    }

    // Update status form
    $html .= <<<HTML
        <div class="update-status-container">
            <h3>Update Status</h3>
            <form method="post" action="admin-feedback.php?tab=features">
                <input type="hidden" name="action" value="update_feature_status">
                <input type="hidden" name="feature_id" value="{$feature['id']}">
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