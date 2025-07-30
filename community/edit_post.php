<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';
require_once 'formatting/formatting_functions.php';

// Check for remember me cookie and auto-login user if valid
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    check_remember_me();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: users/login.php');
    exit;
}

// Get post ID from URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect to index if no valid post ID
if ($post_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get the post
$post = get_post($post_id);

// Redirect if post not found
if (!$post) {
    header('Location: index.php');
    exit;
}

// Check if user has permission to edit
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

// Determine if user can edit this post
$can_edit_post = ($role === 'admin') ||
    (isset($post['user_id']) && $post['user_id'] == $user_id);

if (!$can_edit_post) {
    // Redirect to view post if no permission
    header("Location: view_post.php?id=$post_id");
    exit;
}

// Check for metadata
$metadata = [];
$db = get_db_connection();

// Check if metadata column exists
$metadata_exists = false;
$result = $db->query("SHOW COLUMNS FROM community_posts LIKE 'metadata'");
$metadata_exists = ($result->num_rows > 0);

if ($metadata_exists) {
    // Get metadata if it exists
    $stmt = $db->prepare('SELECT metadata FROM community_posts WHERE id = ?');
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && !empty($row['metadata'])) {
        $metadata = json_decode($row['metadata'], true);
    }
    $stmt->close();
}

// Process form submission
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $post_type = isset($_POST['post_type']) ? trim($_POST['post_type']) : '';

    // Process bug-specific fields if this is a bug report
    $bug_metadata = [];
    if ($post_type === 'bug') {
        $bug_metadata = [
            'bug_location' => isset($_POST['bug_location']) ? trim($_POST['bug_location']) : '',
            'bug_version' => isset($_POST['bug_version']) ? trim($_POST['bug_version']) : '',
            'bug_steps' => isset($_POST['bug_steps']) ? trim($_POST['bug_steps']) : '',
            'bug_expected' => isset($_POST['bug_expected']) ? trim($_POST['bug_expected']) : '',
            'bug_actual' => isset($_POST['bug_actual']) ? trim($_POST['bug_actual']) : ''
        ];
    }

    // Process feature-specific fields if this is a feature request
    if ($post_type === 'feature' && isset($_POST['feature_benefit']) && !empty($_POST['feature_benefit'])) {
        // For feature requests, we keep the format with the benefit in the content
        $content_has_benefit = strpos($content, '**Benefit:**') !== false;

        if (!$content_has_benefit) {
            $content = "**Benefit:**\n" . trim($_POST['feature_benefit']) . "\n\n" . $content;
        }
    }

    // Basic validation
    if (empty($title) || empty($content) || empty($post_type)) {
        $error_message = 'All fields are required';
    } elseif (!in_array($post_type, ['bug', 'feature'])) {
        $error_message = 'Invalid post type';
    } elseif (strlen($title) > 255) {
        $error_message = 'Title is too long (maximum 255 characters)';
    } elseif (strlen($content) > 10000) {
        $error_message = 'Content is too long (maximum 10,000 characters)';
    } else {
        $db = get_db_connection();

        // Check if anything actually changed before saving to history
        $has_changes = false;

        // Check if title or content changed
        if ($title !== $post['title'] || $content !== $post['content'] || $post_type !== $post['post_type']) {
            $has_changes = true;
        }

        // Check if metadata changed (for bug reports)
        if ($post_type === 'bug' && $metadata_exists) {
            $current_metadata = null;
            $stmt = $db->prepare('SELECT metadata FROM community_posts WHERE id = ?');
            $stmt->bind_param('i', $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row && !empty($row['metadata'])) {
                $current_metadata = json_decode($row['metadata'], true);

                // Compare metadata fields
                foreach ($bug_metadata as $key => $value) {
                    if (!isset($current_metadata[$key]) || $current_metadata[$key] !== $value) {
                        $has_changes = true;
                        break;
                    }
                }
            } else if (!empty(array_filter($bug_metadata))) {
                // If no previous metadata but new metadata has values
                $has_changes = true;
            }
            $stmt->close();
        }

        // Only proceed if there are actual changes
        if ($has_changes) {
            $result = $db->query("SHOW COLUMNS FROM post_edit_history LIKE 'metadata'");

            // Get the current metadata for history
            $previous_metadata = null;
            if ($metadata_exists) {
                $stmt = $db->prepare('SELECT metadata FROM community_posts WHERE id = ?');
                $stmt->bind_param('i', $post_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                if ($row && isset($row['metadata'])) {
                    $previous_metadata = $row['metadata'];
                }
                $stmt->close();
            }

            // Save the current post to history
            $stmt = $db->prepare('INSERT INTO post_edit_history (post_id, user_id, title, content, metadata, edited_at) 
                                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)');
            $stmt->bind_param('iisss', $post_id, $user_id, $post['title'], $post['content'], $previous_metadata);
            $stmt->execute();
            $stmt->close();

            // Update the post
            $stmt = $db->prepare('UPDATE community_posts 
                                SET title = ?, content = ?, post_type = ?, updated_at = CURRENT_TIMESTAMP 
                                WHERE id = ?');
            $stmt->bind_param('sssi', $title, $content, $post_type, $post_id);
            $update_success = $stmt->execute();
            $stmt->close();

            // Save bug metadata if applicable
            if ($post_type === 'bug' && !empty($bug_metadata) && $metadata_exists) {
                $metadata_json = json_encode($bug_metadata);
                $stmt = $db->prepare('UPDATE community_posts SET metadata = ? WHERE id = ?');
                $stmt->bind_param('si', $metadata_json, $post_id);
                $stmt->execute();
                $stmt->close();
            }

            if ($update_success) {
                // Redirect immediately to view page with success message
                header("Location: view_post.php?id=$post_id&updated=1");
                exit;
            } else {
                $error_message = 'Error updating the post';
            }
        } else {
            // No changes detected, just redirect back to the post without error
            header("Location: view_post.php?id=$post_id");
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Edit Post - Argo Community</title>

    <?php include 'resources/head/google-analytics.php'; ?>

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>
    <script src="formatting/text-formatting.js" defer></script>

    <link rel="stylesheet" href="create-post.css">
    <link rel="stylesheet" href="edit-post.css">
    <link rel="stylesheet" href="formatting/formatted-text.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/footer/style.css">

    <!-- Mentions system -->
    <link rel="stylesheet" href="mentions/mentions.css">
    <script src="mentions/mentions.js" defer></script>
    <script src="mentions/init.js" defer></script>
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="community-header">
        <h1>Argo Sales Tracker Community</h1>
    </div>

    <div class="community-wrapper">
        <div class="post-form-container">
            <?php if ($error_message): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="post-form">
                <h2>Edit Post</h2>

                <form method="post" action="edit_post.php?id=<?php echo $post_id; ?>">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="post_type">Post Type</label>
                        <select id="post_type" name="post_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="bug" <?php echo $post['post_type'] === 'bug' ? 'selected' : ''; ?>>Bug Report</option>
                            <option value="feature" <?php echo $post['post_type'] === 'feature' ? 'selected' : ''; ?>>Feature Request</option>
                        </select>
                    </div>

                    <!-- Bug Report Specific Fields -->
                    <div id="bug-specific-fields" style="display: <?php echo $post['post_type'] === 'bug' ? 'block' : 'none'; ?>;">
                        <div class="form-group">
                            <label for="bug_location">Where did you find this bug?</label>
                            <select id="bug_location" name="bug_location">
                                <option value="">-- Select Location --</option>
                                <option value="website" <?php echo isset($metadata['bug_location']) && $metadata['bug_location'] === 'website' ? 'selected' : ''; ?>>Website</option>
                                <option value="sales_tracker" <?php echo isset($metadata['bug_location']) && $metadata['bug_location'] === 'sales_tracker' ? 'selected' : ''; ?>>Sales Tracker Application</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="bug_version">Browser or Sales Tracker Version</label>
                            <input type="text" id="bug_version" name="bug_version" placeholder="e.g., Chrome 99.0.4844 or Sales Tracker v2.1.3" value="<?php echo isset($metadata['bug_version']) ? htmlspecialchars($metadata['bug_version']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="bug_steps">Steps to Reproduce</label>
                            <textarea id="bug_steps" name="bug_steps" rows="4" class="formattable mentionable" placeholder="Please provide step-by-step instructions to reproduce the issue"><?php echo isset($metadata['bug_steps']) ? htmlspecialchars($metadata['bug_steps']) : ''; ?></textarea>
                            <?php add_formatting_toolbar('post_content'); ?>
                        </div>

                        <div class="form-group">
                            <label for="bug_expected">Expected Result</label>
                            <textarea id="bug_expected" name="bug_expected" rows="3" class="formattable mentionable" placeholder="What you expected to happen"><?php echo isset($metadata['bug_expected']) ? htmlspecialchars($metadata['bug_expected']) : ''; ?></textarea>
                            <?php add_formatting_toolbar('post_content'); ?>
                        </div>

                        <div class="form-group">
                            <label for="bug_actual">Actual Result</label>
                            <textarea id="bug_actual" name="bug_actual" rows="3" class="formattable mentionable" placeholder="What actually happened"><?php echo isset($metadata['bug_actual']) ? htmlspecialchars($metadata['bug_actual']) : ''; ?></textarea>
                            <?php add_formatting_toolbar('post_content'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content" id="content_label">
                            <?php
                            if ($post['post_type'] === 'bug') {
                                echo 'Additional Details';
                            } else if ($post['post_type'] === 'feature') {
                                echo 'Feature Description';
                            } else {
                                echo 'Content';
                            }
                            ?>
                        </label>
                        <?php
                        if ($post['post_type'] === 'feature' && !empty($feature_benefit)) {
                            $parts = explode("**Benefit:**\n$feature_benefit\n\n", $post['content'], 2);
                            $content_to_display = $parts[1] ?? $post['content'];
                        } else {
                            $content_to_display = $post['content'];
                        }
                        ?>
                        <textarea id="content" name="content" class="formattable mentionable" required><?= htmlspecialchars($content_to_display) ?></textarea>
                        <?php add_formatting_toolbar('post_content'); ?>
                    </div>

                    <div class="form-actions">
                        <a href="view_post.php?id=<?php echo $post_id; ?>" class="btn btn-black">Cancel</a>
                        <button type="submit" class="btn btn-blue">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const postTypeSelect = document.getElementById('post_type');
            const bugFields = document.getElementById('bug-specific-fields');
            const contentLabel = document.getElementById('content_label');

            // Function to show/hide fields based on post type
            function toggleFields() {
                const selectedType = postTypeSelect.value;

                // Show fields based on selection
                if (selectedType === 'bug') {
                    bugFields.style.display = 'block';
                    contentLabel.textContent = 'Additional Details or Context';
                } else {
                    bugFields.style.display = 'none';
                    contentLabel.textContent = 'Content';
                }
            }

            // Add change event listener
            postTypeSelect.addEventListener('change', toggleFields);
        });
    </script>

    <!-- This will be used by mentions.js -->
    <div class="mention-dropdown" id="mentionDropdown"></div>
</body>

</html>