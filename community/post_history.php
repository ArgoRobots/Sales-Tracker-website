<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';

// Check for remember me cookie and auto-login user if valid
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    check_remember_me();
}

// Get post ID from URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect to index if no valid post ID
if ($post_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get the current post
$post = get_post($post_id);

// Redirect if post not found
if (!$post) {
    header('Location: index.php');
    exit;
}

// Check if metadata column exists in post_edit_history
$db = get_db_connection();
$metadata_column_exists = false;
$result = $db->query("PRAGMA table_info(post_edit_history)");
while ($col = $result->fetchArray(SQLITE3_ASSOC)) {
    if ($col['name'] === 'metadata') {
        $metadata_column_exists = true;
        break;
    }
}

// Add column if it doesn't exist
if (!$metadata_column_exists) {
    $db->exec("ALTER TABLE post_edit_history ADD COLUMN metadata TEXT");
}

// Fetch the current post metadata
$current_metadata = null;
$metadata_exists = false;
$result = $db->query("PRAGMA table_info(community_posts)");
while ($col = $result->fetchArray(SQLITE3_ASSOC)) {
    if ($col['name'] === 'metadata') {
        $metadata_exists = true;
        break;
    }
}

if ($metadata_exists) {
    $stmt = $db->prepare('SELECT metadata FROM community_posts WHERE id = :id');
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result && !empty($result['metadata'])) {
        $current_metadata = $result['metadata'];
    }
}

// Fetch edit history ordered by newest first
$stmt = $db->prepare('SELECT h.*, u.username 
    FROM post_edit_history h
    LEFT JOIN community_users u ON h.user_id = u.id
    WHERE h.post_id = :post_id
    ORDER BY h.edited_at DESC');
$stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$history = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $history[] = $row;
}

// The current version is not in the edit history, so add it as the most current version
$current_post = [
    'id' => 'current',
    'title' => $post['title'],
    'content' => $post['content'],
    'metadata' => $current_metadata,
    'edited_at' => isset($post['updated_at']) && $post['updated_at'] != $post['created_at'] ? $post['updated_at'] : $post['created_at'],
    'username' => $post['user_name'],
    'user_id' => $post['user_id'],
    'is_current' => true,
    'is_original' => count($history) === 0
];

// Add the current version at the beginning
array_unshift($history, $current_post);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Post Edit History - Argo Community</title>

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="post-history.css">
    <link rel="stylesheet" href="view-post.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="community-header">
        <h2>Post Edit History</h2>
    </div>

    <div class="community-wrapper">
        <div class="page-header">
            <a href="view_post.php?id=<?php echo $post_id; ?>" class="btn back-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back to Post
            </a>
        </div>

        <div class="history-container">
            <div class="history-header">
                <h2>Edit History for: <?php echo htmlspecialchars($post['title']); ?></h2>
            </div>

            <?php if (count($history) <= 1 && !isset($history[0]['is_original'])): ?>
                <div class="no-history">
                    <p>This post has not been edited yet.</p>
                </div>
            <?php else: ?>
                <?php
                // Initialize previous version for comparison
                $prev_metadata = null;

                foreach ($history as $index => $version):
                    // Parse metadata if exists
                    $version_metadata = null;
                    if (isset($version['metadata']) && !empty($version['metadata'])) {
                        $version_metadata = json_decode($version['metadata'], true);
                    }

                    // Determine if this is the first iteration
                    $is_first = ($index === 0);
                ?>
                    <div class="history-entry">
                        <div class="history-meta">
                            <div>
                                <?php if ($index === (count($history) - 1)): ?>
                                    <strong>Created by:</strong>
                                <?php else: ?>
                                    <strong>Edited by:</strong>
                                <?php endif; ?>

                                <a href="users/profile.php?username=<?php echo urlencode($version['username']); ?>">
                                    <?php echo htmlspecialchars($version['username']); ?>
                                </a>

                                <?php if (isset($version['is_current'])): ?>
                                    <span class="current-badge">Current Version</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <strong>Date:</strong> <?php echo date('M j, Y g:i a', strtotime($version['edited_at'])); ?>
                            </div>
                        </div>

                        <div class="history-title">
                            <?php echo htmlspecialchars($version['title']); ?>
                        </div>

                        <div class="history-content">
                            <?php echo nl2br(htmlspecialchars($version['content'])); ?>
                        </div>

                        <?php if ($post['post_type'] === 'bug'): ?>
                            <div class="history-metadata">
                                <div class="history-metadata-title">Bug Report Details</div>

                                <?php if ($version_metadata): ?>
                                    <?php
                                    // Fields to display
                                    $fields = [
                                        'bug_location' => 'Location',
                                        'bug_version' => 'Version',
                                        'bug_steps' => 'Steps to Reproduce',
                                        'bug_expected' => 'Expected Result',
                                        'bug_actual' => 'Actual Result'
                                    ];

                                    foreach ($fields as $field_key => $field_label):
                                        // Check if this field has changed from the previous version
                                        $has_changed = false;
                                        if (!$is_first && $prev_metadata) {
                                            $prev_value = isset($prev_metadata[$field_key]) ? $prev_metadata[$field_key] : '';
                                            $current_value = isset($version_metadata[$field_key]) ? $version_metadata[$field_key] : '';
                                            $has_changed = ($prev_value !== $current_value);
                                        }
                                    ?>
                                        <div class="metadata-field <?php echo $has_changed ? 'metadata-changed' : ''; ?>">
                                            <div class="metadata-field-label"><?php echo $field_label; ?></div>
                                            <div class="metadata-field-value">
                                                <?php
                                                if (isset($version_metadata[$field_key]) && !empty($version_metadata[$field_key])) {
                                                    if ($field_key === 'bug_location') {
                                                        echo $version_metadata[$field_key] === 'website' ? 'Website' : 'Sales Tracker Application';
                                                    } else {
                                                        echo nl2br(htmlspecialchars($version_metadata[$field_key]));
                                                    }
                                                } else {
                                                    echo '<span class="no-metadata">Not specified</span>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="no-metadata">No structured bug data available for this version.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php
                    // Store current metadata as previous for next iteration
                    $prev_metadata = $version_metadata;

                    // Add divider between versions (except after the last one)
                    if ($index < count($history) - 1):
                    ?>
                        <div class="version-divider"></div>
                    <?php endif; ?>

                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>