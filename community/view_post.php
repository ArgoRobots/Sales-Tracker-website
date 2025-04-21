<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';

require_login();
$current_user = get_current_user();

// Make sure current_user is an array with the expected structure
if (!is_array($current_user)) {
    $current_user = array(
        'id' => $_SESSION['user_id'] ?? 0,
        'username' => $_SESSION['username'] ?? 'Unknown',
        'email' => $_SESSION['email'] ?? '',
        'email_verified' => $_SESSION['email_verified'] ?? 0,
        'role' => $_SESSION['role'] ?? 'user',
        'avatar' => ''
    );
}

// Get post ID from URL parameter
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect to index if no valid post ID
if ($post_id <= 0) {
    header('Location: index.php');
    exit;
}

$post = get_post($post_id);

// Redirect to index if post not found
if (!$post) {
    header('Location: index.php');
    exit;
}

// Increment view count (only once per session to count unique views)
$viewed_posts = isset($_SESSION['viewed_posts']) ? $_SESSION['viewed_posts'] : array();
if (!in_array($post_id, $viewed_posts)) {
    // Update view count in database
    $db = get_db_connection();
    $stmt = $db->prepare('UPDATE community_posts SET views = views + 1 WHERE id = :id');
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    $stmt->execute();

    // Add post to viewed posts in session
    $viewed_posts[] = $post_id;
    $_SESSION['viewed_posts'] = $viewed_posts;

    // Update the post data with new view count
    $post = get_post($post_id);
}

// Get comments for this post
$comments = get_post_comments($post_id);

// Check if user can edit this post (using isset checks)
$can_edit_post = (isset($current_user['role']) && $current_user['role'] === 'admin') ||
    (isset($post['user_id']) && !empty($post['user_id']) &&
        isset($current_user['id']) && $post['user_id'] == $current_user['id']);

// Get user's vote for this post
$user_vote = isset($current_user['email']) ? get_user_vote($post_id, $current_user['email']) : 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title><?php echo htmlspecialchars($post['title']); ?> - Argo Community</title>

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>
    <script src="view-post.js"></script>

    <link rel="stylesheet" href="view-post.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/footer/style.css">

</head>

<body>
    <header>
        <script>
            $(function() {
                $("#includeHeader").load("../resources/header/index.html", function() {
                    adjustLinksAndImages("#includeHeader");
                });
            });
        </script>
        <div id="includeHeader"></div>
    </header>

    <div class="community-header">
        <h1>Argo Sales Tracker Community</h1>
        <p>Report bugs and suggest features to help us improve</p>

        <!-- Email verification reminder if needed -->
        <?php if (isset($current_user['email_verified']) && !$current_user['email_verified']): ?>
            <div class="verification-alert">
                Please verify your email address. <a href="resend_verification.php?auto=1">Resend verification email</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="community-container">
        <a href="index.php" class="btn back-button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Back to All Posts
        </a>

        <div class="post-detail">
            <div class="post-card" data-post-id="<?php echo $post['id']; ?>" data-post-type="<?php echo $post['post_type']; ?>">
                <!-- Move votes to the left side -->
                <div class="post-votes">
                    <button class="vote-btn upvote <?php echo $user_vote === 1 ? 'voted' : ''; ?>" data-post-id="<?php echo $post['id']; ?>" data-vote="up">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-width="2" d="M12 19V5M5 12l7-7 7 7" />
                        </svg>
                    </button>
                    <span class="vote-count"><?php echo $post['votes']; ?></span>
                    <button class="vote-btn downvote <?php echo $user_vote === -1 ? 'voted' : ''; ?>" data-post-id="<?php echo $post['id']; ?>" data-vote="down">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-width="2" d="M12 5v14M5 12l7 7 7-7" />
                        </svg>
                    </button>
                </div>
                <div class="post-content">
                    <div class="post-header">
                        <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="post-meta">
                            <span class="post-type <?php echo $post['post_type']; ?>">
                                <?php echo $post['post_type'] === 'bug' ? 'Bug Report' : 'Feature Request'; ?>
                            </span>
                            <span class="post-status <?php echo $post['status']; ?>">
                                <?php
                                switch ($post['status']) {
                                    case 'open':
                                        echo 'Open';
                                        break;
                                    case 'in_progress':
                                        echo 'In Progress';
                                        break;
                                    case 'completed':
                                        echo 'Completed';
                                        break;
                                    case 'declined':
                                        echo 'Declined';
                                        break;
                                }
                                ?>
                            </span>
                            <?php if ($can_edit_post): ?>
                                <div class="admin-actions">
                                    <select class="status-update" data-post-id="<?php echo $post['id']; ?>">
                                        <option value="open" <?php echo $post['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                        <option value="in_progress" <?php echo $post['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $post['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="declined" <?php echo $post['status'] === 'declined' ? 'selected' : ''; ?>>Declined</option>
                                    </select>
                                    <button class="delete-post-btn" data-post-id="<?php echo $post['id']; ?>">Delete</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="post-body">
                        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    </div>
                    <div class="post-footer">
                        <div class="post-info">
                            <span class="post-author">
                                Posted by
                                <a href="users/profile.php?username=<?php echo urlencode($post['user_name']); ?>" class="user-link">
                                    <?php echo htmlspecialchars($post['user_name']); ?>
                                </a>
                            </span>
                            <span class="post-date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                            <span class="post-views">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <?php echo (isset($post['views']) && (int)$post['views'] > 0) ? (int)$post['views'] : 0; ?> <?php echo ((isset($post['views']) && (int)$post['views'] == 1) ? 'view' : 'views'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="comments-section">
                <h3><?php echo count($comments); ?> Comments</h3>

                <div class="comments-container">
                    <?php foreach ($comments as $comment): ?>
                        <?php
                        // Check if current user can delete this comment
                        $can_delete_comment = (isset($current_user['role']) && $current_user['role'] === 'admin') ||
                            (isset($comment['user_id']) && !empty($comment['user_id']) &&
                                isset($current_user['id']) && $comment['user_id'] == $current_user['id']);
                        ?>
                        <div class="comment" data-comment-id="<?php echo $comment['id']; ?>">
                            <div class="comment-header">
                                <div class="comment-author-info">
                                    <a href="users/profile.php?username=<?php echo urlencode($comment['user_name']); ?>" class="comment-author">
                                        <?php echo htmlspecialchars($comment['user_name']); ?>
                                    </a>
                                    <span class="comment-date"><?php echo date('M j, Y g:i a', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <?php if ($can_delete_comment): ?>
                                    <div class="comment-actions">
                                        <button class="delete-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">Delete</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="comment-form">
                    <h4>Add a Comment</h4>
                    <?php if ($is_logged_in): ?>
                        <form id="add-comment-form" data-post-id="<?php echo $post['id']; ?>">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <div class="form-group">
                                <textarea id="comment_content" name="comment_content" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Comment</button>
                        </form>
                    <?php else: ?>
                        <div class="login-required">
                            <p>Please <a href="users/login.php">log in</a> or <a href="users/register.php">create an account</a> to comment on this post.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <script>
            $(function() {
                $("#includeFooter").load("../resources/footer/index.html", function() {
                    adjustLinksAndImages("#includeFooter");
                });
            });
        </script>
        <div id="includeFooter"></div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Set up voting buttons with visual feedback
            const upvoteBtn = document.querySelector(".upvote");
            const downvoteBtn = document.querySelector(".downvote");

            if (upvoteBtn && downvoteBtn) {
                // If user already voted, show the buttons as active
                if (upvoteBtn.classList.contains('voted')) {
                    upvoteBtn.style.color = "#2563eb";
                } else if (downvoteBtn.classList.contains('voted')) {
                    downvoteBtn.style.color = "#dc2626";
                }
            }
        });
    </script>
</body>

</html>