<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';
require_once '../community_functions.php';

// Require user to be logged in
require_login();

// Get requested user profile
$requested_username = isset($_GET['username']) ? trim($_GET['username']) : '';
$is_own_profile = false;
$user = null;

if (empty($requested_username)) {
    // If no username specified, show current user's profile
    $user = get_user($_SESSION['user_id']);
    $is_own_profile = true;
} else {
    // Get user by username
    $user = get_user_by_username($requested_username);
    $is_own_profile = ($user && $user['id'] == $_SESSION['user_id']);
}

// If user not found, redirect to community index
if (!$user) {
    header('Location: ../index.php');
    exit;
}

// Get profile data including post and comment counts
$profile = get_user_profile($user['id']);

// Get user's latest activity
$activity = get_user_activity($user['id'], 5);

// Handle profile update (if own profile)
$success_message = '';
$error_message = '';

if ($is_own_profile && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile picture upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $avatar_result = upload_avatar($_SESSION['user_id'], $_FILES['avatar']);
        if ($avatar_result) {
            $user['avatar'] = $avatar_result;
        } else {
            $error_message = 'Failed to upload profile picture. Please ensure it is a valid image (JPG, PNG, GIF) under 2MB.';
        }
    }

    // Handle profile info update
    $update_data = [
        'display_name' => isset($_POST['display_name']) ? trim($_POST['display_name']) : $user['display_name'],
        'bio' => isset($_POST['bio']) ? trim($_POST['bio']) : ($user['bio'] ?? '')
    ];

    $update_result = update_profile($user['id'], $update_data);

    if ($update_result) {
        $success_message = 'Profile updated successfully.';

        // Update session data
        $_SESSION['display_name'] = $update_data['display_name'];

        // Refresh user data
        $user = get_user($user['id']);
    } else {
        $error_message = 'Failed to update profile. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title><?php echo htmlspecialchars($user['display_name']); ?>'s Profile - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="profile-style.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <script>
            $(function() {
                $("#includeHeader").load("../../resources/header/index.html", function() {
                    adjustLinksAndImages("#includeHeader");
                });
            });
        </script>
        <div id="includeHeader"></div>
    </header>

    <div class="profile-container">
        <div class="profile-header">
            <a href="../index.php" class="btn back-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back to All Posts
            </a>

            <h1>
                <?php echo htmlspecialchars($user['display_name']); ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <span class="admin-badge">Admin</span>
                <?php endif; ?>
            </h1>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-grid">
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="<?php echo htmlspecialchars($user['display_name']); ?>'s avatar">
                        <?php else: ?>
                            <div class="profile-avatar-placeholder">
                                <?php echo strtoupper(substr($user['display_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['display_name']); ?></h2>
                        <div class="profile-meta">
                            <p class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="profile-joined">
                                Joined <?php echo date('F Y', strtotime($user['created_at'])); ?>
                            </p>
                        </div>

                        <?php if (!empty($user['bio'])): ?>
                            <div class="profile-bio">
                                <p><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-value"><?php echo $profile['']; ?></span>
                                <span class="stat-label">Reputation</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value"><?php echo $profile['post_count']; ?></span>
                                <span class="stat-label">Posts</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value"><?php echo $profile['comment_count']; ?></span>
                                <span class="stat-label">Comments</span>
                            </div>
                        </div>
                    </div>

                    <?php if ($is_own_profile): ?>
                        <div class="profile-actions">
                            <button class="btn btn-blue" id="edit-profile-btn">Edit Profile</button>

                            <?php if (!$_SESSION['email_verified']): ?>
                                <a href="resend_verification.php" class="btn btn-secondary">Verify Email</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-content">
                <?php if ($is_own_profile): ?>
                    <div class="edit-profile-form" id="edit-profile-form" style="display: none;">
                        <h2>Edit Profile</h2>
                        <form method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="display_name">Display Name</label>
                                <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($user['display_name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="avatar">Profile Picture</label>
                                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">
                                <small>Maximum file size: 2MB. Supported formats: JPG, PNG, GIF</small>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" id="cancel-edit-btn">Cancel</button>
                                <button type="submit" class="btn btn-blue">Save Changes</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="activity-section">
                    <h2>Recent Activity</h2>

                    <?php if (empty($activity)): ?>
                        <div class="empty-state">
                            <p>No activity yet</p>
                        </div>
                    <?php else: ?>
                        <div class="activity-list">
                            <?php foreach ($activity as $item): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?php echo $item['activity_type']; ?>">
                                        <?php if ($item['activity_type'] === 'post'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                            </svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                            </svg>
                                        <?php endif; ?>
                                    </div>

                                    <div class="activity-content">
                                        <div class="activity-header">
                                            <?php if ($item['activity_type'] === 'post'): ?>
                                                <span class="activity-type">Created a post</span>
                                                <a href="view_post.php?id=<?php echo $item['id']; ?>" class="activity-title">
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="activity-type">Commented on</span>
                                                <a href="view_post.php?id=<?php echo $item['post_id']; ?>" class="activity-title">
                                                    <?php echo htmlspecialchars($item['post_title']); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <div class="activity-excerpt">
                                            <?php
                                            $content = htmlspecialchars($item['content']);
                                            echo strlen($content) > 150 ? substr($content, 0, 150) . '...' : $content;
                                            ?>
                                        </div>

                                        <div class="activity-meta">
                                            <span class="activity-date">
                                                <?php echo date('M j, Y g:i a', strtotime($item['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <script>
            $(function() {
                $("#includeFooter").load("../../resources/footer/index.html", function() {
                    adjustLinksAndImages("#includeFooter");
                });
            });
        </script>
        <div id="includeFooter"></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit profile form toggle
            const editProfileBtn = document.getElementById('edit-profile-btn');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');
            const editProfileForm = document.getElementById('edit-profile-form');

            if (editProfileBtn && cancelEditBtn && editProfileForm) {
                editProfileBtn.addEventListener('click', function() {
                    editProfileForm.style.display = 'block';
                    editProfileBtn.style.display = 'none';
                });

                cancelEditBtn.addEventListener('click', function() {
                    editProfileForm.style.display = 'none';
                    editProfileBtn.style.display = 'block';
                });
            }
        });
    </script>
</body>

</html>