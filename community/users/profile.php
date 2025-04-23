<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';
require_once '../community_functions.php';

require_login('', true);

// Get requested user profile
$requested_username = isset($_GET['username']) ? trim($_GET['username']) : '';
$is_own_profile = false;
$user = null;
$user_not_found = false;

if (empty($requested_username)) {
    // If no username specified, show current user's profile
    $user = get_user($_SESSION['user_id']);
    $is_own_profile = true;
} else {
    $db = get_db_connection();

    $stmt = $db->prepare("SELECT * FROM community_users WHERE username = :username");
    $stmt->bindValue(':username', $requested_username, SQLITE3_TEXT);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        $is_own_profile = ($user['id'] == $_SESSION['user_id']);
    } else {
        $user_not_found = true;
    }
}

// If user found, get profile data
if ($user) {
    // Get user profile data (posts and comments count)
    $db = get_db_connection();

    // Create profile data manually since get_user_profile might be failing
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT p.id) AS post_count,
            COUNT(DISTINCT c.id) AS comment_count
        FROM 
            community_users u
        LEFT JOIN 
            community_posts p ON u.id = p.user_id
        LEFT JOIN 
            community_comments c ON u.id = c.user_id
        WHERE 
            u.id = :user_id
    ");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $profile = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$profile) {
        // Create default profile if query fails
        $profile = [
            'post_count' => 0,
            'comment_count' => 0
        ];
    }

    // Get recent activity
    $activity = get_user_activity($user['id'], 5);
    if (!$activity) {
        $activity = [];
    }

    // Handle profile update (if own profile)
    $success_message = '';
    $error_message = '';

    if ($is_own_profile && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle profile picture upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $avatar_result = upload_avatar($_SESSION['user_id'], $_FILES['avatar']);
            if ($avatar_result) {
                $user['avatar'] = $avatar_result;
                $success_message = 'Profile picture updated successfully.';
            } else {
                $error_message = 'Failed to upload profile picture. Please ensure it is a valid image (JPG, PNG, GIF) under 2MB.';
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
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title><?php echo $user_not_found ? 'User Not Found' : htmlspecialchars($user['username']) . "'s Profile"; ?> - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="profile.js" defer></script>

    <link rel="stylesheet" href="profile-style.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <?php if ($user_not_found): ?>
        <!-- User Not Found Display -->
        <div class="profile-container">
            <div class="user-not-found">
                <div class="not-found-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h2>The user "<?php echo htmlspecialchars($requested_username); ?>" could not be found</h2>
                <p>The username you are looking for does not exist or may have been removed.</p>
                <div class="not-found-actions">
                    <a href="../index.php" class="btn btn-blue">Return to Community</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Normal Profile Display -->
        <div class="profile-container">
            <div class="profile-header">
                <h1>
                    <?php echo htmlspecialchars($user['username']); ?>
                    <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
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
                        <form method="post" enctype="multipart/form-data" id="avatar-form">
                            <div class="profile-avatar <?php echo $is_own_profile ? 'editable' : ''; ?>" id="profile-avatar">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>'s avatar" id="avatar-preview">
                                <?php else: ?>
                                    <div class="profile-avatar-placeholder" id="avatar-placeholder">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($is_own_profile): ?>
                                    <div class="avatar-overlay">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                            <circle cx="12" cy="13" r="4"></circle>
                                        </svg>
                                        <span>Change Photo</span>
                                    </div>
                                    <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif" style="display: none;">
                                <?php endif; ?>
                            </div>
                        </form>

                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
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
                                    <span class="stat-value"><?php echo $profile['post_count']; ?></span>
                                    <span class="stat-label">Posts</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $profile['comment_count']; ?></span>
                                    <span class="stat-label">Comments</span>
                                </div>
                            </div>
                        </div>

                        <?php if ($is_own_profile && !$_SESSION['email_verified']): ?>
                            <div class="profile-actions">
                                <a href="verify_email.php" class="btn btn-secondary">Verify Email</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="profile-content">
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
                                                    <a href="../view_post.php?id=<?php echo $item['id']; ?>" class="activity-title">
                                                        <?php echo htmlspecialchars($item['title']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="activity-type">Commented on</span>
                                                    <a href="../view_post.php?id=<?php echo $item['post_id']; ?>" class="activity-title">
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
    <?php endif; ?>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>