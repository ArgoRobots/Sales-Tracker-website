<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';
include_once 'rate_limit.php';
require_once 'formatting/formatting_functions.php';

require_login('', true);
$current_user = \CommunityUsers\get_current_user();

$html_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $current_user['id'];
    $html_message = check_rate_limit($user_id, 'post');

    if ($html_message === false) {
        // Validate inputs
        $title = isset($_POST['post_title']) ? trim($_POST['post_title']) : '';
        $content = isset($_POST['post_content']) ? trim($_POST['post_content']) : '';
        $post_type = isset($_POST['post_type']) ? trim($_POST['post_type']) : '';

        // Get bug-specific fields if this is a bug report
        $bug_metadata = [];
        if ($post_type === 'bug') {
            $bug_metadata = [
                'bug_location' => isset($_POST['bug_location']) ? trim($_POST['bug_location']) : '',
                'bug_version' => isset($_POST['bug_version']) ? trim($_POST['bug_version']) : '',
                'bug_steps' => isset($_POST['bug_steps']) ? trim($_POST['bug_steps']) : '',
                'bug_expected' => isset($_POST['bug_expected']) ? trim($_POST['bug_expected']) : '',
                'bug_actual' => isset($_POST['bug_actual']) ? trim($_POST['bug_actual']) : ''
            ];

            // We'll store the raw content as is, without formatting/duplication
            // The structured fields will be stored in metadata
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
            // Add the post with user info
            $post_id = add_post($current_user['id'], $current_user['username'], $current_user['email'], $title, $content, $post_type);

            if ($post_id) {
                // Connect post to user account
                $db = get_db_connection();
                $stmt = $db->prepare('UPDATE community_posts SET user_id = ? WHERE id = ?');
                $stmt->bind_param('ii', $current_user['id'], $post_id);
                $stmt->execute();

                // Save bug metadata as JSON in a separate field or table if needed
                if ($post_type === 'bug' && !empty($bug_metadata)) {
                    // Save metadata as JSON
                    $metadata_json = json_encode($bug_metadata);
                    $stmt = $db->prepare('UPDATE community_posts SET metadata = ? WHERE id = ?');
                    $stmt->bind_param('si', $metadata_json, $post_id);
                    $stmt->execute();
                }

                $stmt->close();

                // Redirect immediately to view page with success message
                header("Location: view_post.php?id=$post_id&created=1");
                exit;
            } else {
                $error_message = 'Error adding post to the database';
            }
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
    <title>Create New Post - Argo Community</title>

    <?php include 'resources/head/google-analytics.php'; ?>

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>
    <script src="formatting/text-formatting.js" defer></script>

    <link rel="stylesheet" href="create-post.css">
    <link rel="stylesheet" href="rate-limit.css">
    <link rel="stylesheet" href="formatting/formatted-text.css">
    <link rel="stylesheet" href="view-post.css">
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
            <div class="post-form">
                <h2>Create New Post</h2>

                <!-- Preview Toggle -->
                <div class="preview-toggle">
                    <button type="button" id="edit-tab" class="active">Edit</button>
                    <button type="button" id="preview-tab">Preview</button>
                </div>

                <!-- Edit Form -->
                <div class="edit-form-container" id="edit-container">
                    <form id="community-post-form" method="post" action="create_post.php">
                        <div class="form-group">
                            <label for="post_title">Title</label>
                            <input type="text" id="post_title" name="post_title" required>
                        </div>

                        <div class="form-group">
                            <label for="post_type">Post Type</label>
                            <select id="post_type" name="post_type" required>
                                <option value="">Select post type</option>
                                <option value="bug">Bug Report</option>
                                <option value="feature">Feature Request</option>
                            </select>
                        </div>

                        <!-- Bug-specific fields -->
                        <div id="bug-specific-fields" class="form-section hidden">
                            <div class="form-section-title">
                                Bug Report Details
                            </div>

                            <div class="form-group">
                                <label for="bug_location">Where did you encounter this bug?</label>
                                <input type="text" id="bug_location" name="bug_location" placeholder="e.g., Dashboard > Sales Report, Login page">
                                <small class="field-hint">Specify the page, feature, or area where the issue occurred</small>
                            </div>

                            <div class="form-group">
                                <label for="bug_version">Version/Browser</label>
                                <input type="text" id="bug_version" name="bug_version" placeholder="e.g., Chrome 118, Mobile app v2.1">
                                <small class="field-hint">Browser version, app version, or device info</small>
                            </div>

                            <div class="form-group">
                                <label for="bug_steps">Steps to Reproduce</label>
                                <textarea id="bug_steps" name="bug_steps" placeholder="1. Navigate to...&#10;2. Click on...&#10;3. Notice that..."></textarea>
                                <small class="field-hint">Detailed steps to help us reproduce the issue</small>
                            </div>

                            <div class="form-group">
                                <label for="bug_expected">Expected Behavior</label>
                                <textarea id="bug_expected" name="bug_expected" placeholder="What should have happened?"></textarea>
                                <small class="field-hint">Describe what you expected to happen</small>
                            </div>

                            <div class="form-group">
                                <label for="bug_actual">Actual Behavior</label>
                                <textarea id="bug_actual" name="bug_actual" placeholder="What actually happened?"></textarea>
                                <small class="field-hint">Describe what actually occurred</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="post_content" id="content_label">Content</label>
                            <textarea id="post_content" name="post_content" class="formattable mentionable" required></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="index.php" class="btn btn-black">Cancel</a>
                            <button type="submit" class="btn btn-blue">Create Post</button>
                        </div>
                    </form>
                </div>

                <!-- Preview Container -->
                <div class="preview-container" id="preview-container">
                    <div class="preview-post">
                        <div id="preview-content">
                            <div class="preview-empty-state">
                                <div class="preview-empty-icon">👁️</div>
                                <p>Fill out the form to see a preview of your post</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <!-- Mention dropdown -->
    <div class="mention-dropdown" id="mentionDropdown"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editTab = document.getElementById('edit-tab');
            const previewTab = document.getElementById('preview-tab');
            const editContainer = document.getElementById('edit-container');
            const previewContainer = document.getElementById('preview-container');
            const previewContent = document.getElementById('preview-content');

            const postTitle = document.getElementById('post_title');
            const postType = document.getElementById('post_type');
            const postContent = document.getElementById('post_content');
            const bugFields = document.getElementById('bug-specific-fields');
            const contentLabel = document.getElementById('content_label');

            // Bug-specific fields
            const bugLocation = document.getElementById('bug_location');
            const bugVersion = document.getElementById('bug_version');
            const bugSteps = document.getElementById('bug_steps');
            const bugExpected = document.getElementById('bug_expected');
            const bugActual = document.getElementById('bug_actual');

            // Mock user data (in real implementation, this would come from PHP)
            const currentUser = {
                username: 'Current User', // This would be populated from session
                avatar: null
            };

            // Tab switching
            editTab.addEventListener('click', function() {
                switchToEdit();
            });

            previewTab.addEventListener('click', function() {
                switchToPreview();
            });

            function switchToEdit() {
                editTab.classList.add('active');
                previewTab.classList.remove('active');
                editContainer.style.display = 'block';
                previewContainer.classList.remove('active');
            }

            function switchToPreview() {
                editTab.classList.remove('active');
                previewTab.classList.add('active');
                editContainer.style.display = 'none';
                previewContainer.classList.add('active');
                updatePreview();
            }

            // Post type change handler
            postType.addEventListener('change', function() {
                const selectedType = this.value;

                if (selectedType === 'bug') {
                    bugFields.style.display = 'block';
                    bugFields.classList.remove('hidden');
                    contentLabel.textContent = 'Additional Details or Context';
                } else {
                    bugFields.style.display = 'none';
                    bugFields.classList.add('hidden');
                    contentLabel.textContent = 'Content';
                }

                // Update preview if we're on preview tab
                if (previewTab.classList.contains('active')) {
                    updatePreview();
                }
            });

            // Update preview when any field changes
            const formFields = [postTitle, postType, postContent, bugLocation, bugVersion, bugSteps, bugExpected, bugActual];
            formFields.forEach(field => {
                if (field) {
                    field.addEventListener('input', function() {
                        if (previewTab.classList.contains('active')) {
                            updatePreview();
                        }
                    });
                }
            });

            let previewTimeout;

            function updatePreview() {
                const title = postTitle.value.trim();
                const type = postType.value;
                const content = postContent.value.trim();

                // If no content, show empty state
                if (!title && !type && !content) {
                    previewContent.innerHTML = `
                        <div class="preview-empty-state">
                            <div class="preview-empty-icon">👁️</div>
                            <p>Fill out the form to see a preview of your post</p>
                        </div>
                    `;
                    return;
                }

                // Clear existing timeout
                if (previewTimeout) {
                    clearTimeout(previewTimeout);
                }

                // Show loading state
                previewContent.innerHTML = `
                    <div class="preview-empty-state">
                        <div class="preview-empty-icon">⏳</div>
                        <p>Generating preview...</p>
                    </div>
                `;

                // Debounce the AJAX request
                previewTimeout = setTimeout(() => {
                    fetchServerPreview();
                }, 300);
            }

            function fetchServerPreview() {
                const formData = new FormData();
                formData.append('preview_request', '1');
                formData.append('title', postTitle.value);
                formData.append('content', postContent.value);
                formData.append('post_type', postType.value);

                // Add bug-specific fields
                if (bugLocation) formData.append('bug_location', bugLocation.value);
                if (bugVersion) formData.append('bug_version', bugVersion.value);
                if (bugSteps) formData.append('bug_steps', bugSteps.value);
                if (bugExpected) formData.append('bug_expected', bugExpected.value);
                if (bugActual) formData.append('bug_actual', bugActual.value);

                fetch('preview_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }
                        renderPreview(data);
                    })
                    .catch(error => {
                        console.error('Preview error:', error);
                        // Fallback to client-side preview
                        renderClientSidePreview();
                    });
            }

            function renderPreview(data) {
                let previewHTML = '';

                // Post header
                previewHTML += '<div class="preview-header">';
                previewHTML += `<h1 class="preview-title">${data.title || 'Untitled Post'}</h1>`;
                if (data.post_type) {
                    const typeName = data.post_type === 'bug' ? 'Bug Report' : 'Feature Request';
                    previewHTML += `<span class="preview-type-badge ${data.post_type}">${typeName}</span>`;
                }
                previewHTML += '</div>';

                // Post meta
                previewHTML += '<div class="preview-meta">';
                previewHTML += '<div class="preview-author">';

                if (data.user.avatar) {
                    previewHTML += `<img src="${data.user.avatar}" alt="${data.user.username}" class="preview-avatar">`;
                } else {
                    previewHTML += `<div class="preview-avatar">${data.user.username.charAt(0).toUpperCase()}</div>`;
                }

                previewHTML += `<span>Posted by ${data.user.username}</span>`;
                previewHTML += '</div>';
                previewHTML += '</div>';

                // Bug-specific content
                if (data.post_type === 'bug' && data.bug_metadata) {
                    const bug = data.bug_metadata;
                    const hasMetadata = Object.values(bug).some(field => field.raw);

                    if (hasMetadata) {
                        previewHTML += '<div class="preview-bug-info">';

                        if (bug.location.raw) {
                            previewHTML += '<div class="preview-bug-section">';
                            previewHTML += '<div class="preview-bug-section-title">Location</div>';
                            previewHTML += `<div class="preview-bug-section-content">${bug.location.formatted}</div>`;
                            previewHTML += '</div>';
                        }

                        if (bug.version.raw) {
                            previewHTML += '<div class="preview-bug-section">';
                            previewHTML += '<div class="preview-bug-section-title">Version/Browser</div>';
                            previewHTML += `<div class="preview-bug-section-content">${bug.version.formatted}</div>`;
                            previewHTML += '</div>';
                        }

                        if (bug.steps.raw) {
                            previewHTML += '<div class="preview-bug-section">';
                            previewHTML += '<div class="preview-bug-section-title">Steps to Reproduce</div>';
                            previewHTML += `<div class="preview-bug-section-content">${bug.steps.formatted}</div>`;
                            previewHTML += '</div>';
                        }

                        if (bug.expected.raw) {
                            previewHTML += '<div class="preview-bug-section">';
                            previewHTML += '<div class="preview-bug-section-title">Expected Behavior</div>';
                            previewHTML += `<div class="preview-bug-section-content">${bug.expected.formatted}</div>`;
                            previewHTML += '</div>';
                        }

                        if (bug.actual.raw) {
                            previewHTML += '<div class="preview-bug-section">';
                            previewHTML += '<div class="preview-bug-section-title">Actual Behavior</div>';
                            previewHTML += `<div class="preview-bug-section-content">${bug.actual.formatted}</div>`;
                            previewHTML += '</div>';
                        }

                        if (data.content) {
                            previewHTML += '<div class="preview-bug-section">';
                            previewHTML += '<div class="preview-bug-section-title">Additional Details</div>';
                            previewHTML += `<div class="preview-bug-section-content">${data.content}</div>`;
                            previewHTML += '</div>';
                        }

                        previewHTML += '</div>';
                    } else if (data.content) {
                        previewHTML += `<div class="preview-content">${data.content}</div>`;
                    }
                } else {
                    // Regular content
                    if (data.content) {
                        previewHTML += `<div class="preview-content">${data.content}</div>`;
                    }
                }

                previewContent.innerHTML = previewHTML;
            }

            function renderClientSidePreview() {
                // Fallback to basic client-side preview
                const title = postTitle.value.trim();
                const type = postType.value;
                const content = postContent.value.trim();

                let previewHTML = '';

                // Post header
                previewHTML += '<div class="preview-header">';
                previewHTML += `<h1 class="preview-title">${escapeHtml(title) || 'Untitled Post'}</h1>`;
                if (type) {
                    previewHTML += `<span class="preview-type-badge ${type}">${type === 'bug' ? 'Bug Report' : 'Feature Request'}</span>`;
                }
                previewHTML += '</div>';

                // Post meta
                previewHTML += '<div class="preview-meta">';
                previewHTML += '<div class="preview-author">';
                previewHTML += `<div class="preview-avatar">${currentUser.username.charAt(0).toUpperCase()}</div>`;
                previewHTML += `<span>Posted by ${escapeHtml(currentUser.username)}</span>`;
                previewHTML += '</div>';
                previewHTML += '</div>';

                // Basic content
                if (content) {
                    previewHTML += `<div class="preview-content">${formatText(content)}</div>`;
                }

                previewContent.innerHTML = previewHTML;
            }

            // Basic text formatting (simplified version)
            function formatText(text) {
                if (!text) return '';

                // Escape HTML first
                text = escapeHtml(text);

                // Convert line breaks
                text = text.replace(/\n/g, '<br>');

                // Basic markdown-style formatting
                text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); // Bold
                text = text.replace(/\*(.*?)\*/g, '<em>$1</em>'); // Italic
                text = text.replace(/`(.*?)`/g, '<code>$1</code>'); // Code

                // Simple @mention detection (just highlighting)
                text = text.replace(/@(\w+)/g, '<span style="color: #2563eb; font-weight: 500;">@$1</span>');

                return text;
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Initialize form state
            if (postType.value === 'bug') {
                bugFields.style.display = 'block';
                bugFields.classList.remove('hidden');
                contentLabel.textContent = 'Additional Details or Context';
            }
        });
    </script>

    <!-- This will be used by mentions.js -->
    <div class="mention-dropdown" id="mentionDropdown"></div>
</body>

</html>