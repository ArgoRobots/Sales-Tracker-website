<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : 0;
$username = $is_logged_in ? ($_SESSION['username'] ?? 'Unknown') : '';
$email = $is_logged_in ? ($_SESSION['email'] ?? '') : '';
$role = $is_logged_in ? ($_SESSION['role'] ?? 'user') : '';

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

// Check if user can edit this post
$can_edit_post = ($role === 'admin') ||
    (isset($post['user_id']) && !empty($post['user_id']) && $post['user_id'] == $user_id);

// Get user's vote for this post
$user_vote = $is_logged_in ? get_user_vote($post_id, $email) : 0;

// Check for status messages in URL parameters
$status_message = '';
if (isset($_GET['created']) && $_GET['created'] == '1') {
    $status_message = 'Post created successfully!';
} elseif (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $status_message = 'Post updated successfully!';
}

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
        <?php if ($is_logged_in && isset($_SESSION['email_verified']) && !$_SESSION['email_verified']): ?>
            <div class="verification-alert">
                Please verify your email address. <a href="resend_verification.php?auto=1">Resend verification email</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($status_message): ?>
        <div class="community-wrapper">
            <div class="success-message">
                <?php echo htmlspecialchars($status_message); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="community-container">
        <div class="page-header">
            <a href="index.php" class="btn back-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back to All Posts
            </a>

            <div class="post-status-controls">
                <span class="post-status-label">Status:</span>
                <span class="post-status post-status-large <?php echo $post['status']; ?>">
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
                    <select class="status-update" data-post-id="<?php echo $post['id']; ?>">
                        <option value="open" <?php echo $post['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="in_progress" <?php echo $post['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $post['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="declined" <?php echo $post['status'] === 'declined' ? 'selected' : ''; ?>>Declined</option>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <div class="post-detail">
            <div class="post-card" data-post-id="<?php echo $post['id']; ?>" data-post-type="<?php echo $post['post_type']; ?>">

                <!-- Post votes -->
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

                <!-- Post content -->
                <div class="post-content">
                    <div class="post-header">
                        <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="post-meta">
                            <span class="post-type <?php echo $post['post_type']; ?>">
                                <?php echo $post['post_type'] === 'bug' ? 'Bug Report' : 'Feature Request'; ?>
                            </span>

                            <!-- Edit Post button -->
                            <?php if ($can_edit_post): ?>
                                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="edit-post-btn">Edit Post</a>
                            <?php endif; ?>

                            <!-- Delete post button -->
                            <button class="delete-post-btn" data-post-id="<?php echo $post['id']; ?>">Delete</button>
                        </div>
                    </div>
                    <div class="post-body">
                        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    </div>

                    <!-- Post footer -->
                    <div class="post-footer">
                        <div class="post-info">
                            <span class="post-author">
                                Posted by
                                <a href="users/profile.php?username=<?php echo urlencode($post['user_name']); ?>" class="user-link">
                                    <?php echo htmlspecialchars($post['user_name']); ?>
                                </a>
                            </span>
                            <span class="post-date"><?php echo date('M j, Y g:i a', strtotime($post['created_at'])); ?></span>
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

            <!-- Comments -->
            <div class="comments-section">
                <h3><?php echo count($comments); ?> Comments</h3>

                <div class="comments-container">
                    <?php foreach ($comments as $comment): ?>
                        <?php
                        // Check if current user can delete this comment
                        $can_delete_comment = ($role === 'admin') ||
                            (isset($comment['user_id']) && !empty($comment['user_id']) && $comment['user_id'] == $user_id);

                        // Get user's vote for this comment
                        $comment_vote = $is_logged_in ? get_user_comment_vote($comment['id'], $email) : 0;

                        // Ensure votes value exists
                        $comment_votes = isset($comment['votes']) ? (int)$comment['votes'] : 0;

                        // Check if current user can edit this comment
                        $can_edit_comment = ($role === 'admin') ||
                            (isset($comment['user_id']) && !empty($comment['user_id']) && $comment['user_id'] == $user_id);
                        ?>
                        <div class="comment" data-comment-id="<?php echo $comment['id']; ?>">
                            <!-- Vertical vote controls on left -->
                            <div class="comment-votes">
                                <button class="comment-vote-btn upvote <?php echo $comment_vote === 1 ? 'voted' : ''; ?>"
                                    data-comment-id="<?php echo $comment['id']; ?>"
                                    data-vote="up"
                                    <?php echo !$is_logged_in ? 'disabled title="Please log in to vote"' : ''; ?>>
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-width="2" d="M12 19V5M5 12l7-7 7 7" />
                                    </svg>
                                </button>
                                <span class="comment-vote-count"><?php echo $comment_votes; ?></span>
                                <button class="comment-vote-btn downvote <?php echo $comment_vote === -1 ? 'voted' : ''; ?>"
                                    data-comment-id="<?php echo $comment['id']; ?>"
                                    data-vote="down"
                                    <?php echo !$is_logged_in ? 'disabled title="Please log in to vote"' : ''; ?>>
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-width="2" d="M12 5v14M5 12l7 7 7-7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Main comment content area -->
                            <div class="comment-main">
                                <div class="comment-header">
                                    <div class="comment-author-info">
                                        <a href="users/profile.php?username=<?php echo urlencode($comment['user_name']); ?>" class="user-link"><?php echo htmlspecialchars($comment['user_name']); ?></a>
                                        <span class="comment-date"><?php echo date('M j, Y g:i a', strtotime($comment['created_at'])); ?></span>
                                    </div>
                                    <div class="comment-controls">
                                        <?php if ($can_edit_comment || $can_delete_comment): ?>
                                            <div class="comment-actions">
                                                <?php if ($can_edit_comment): ?>
                                                    <button class="edit-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">Edit</button>
                                                <?php endif; ?>

                                                <?php if ($can_delete_comment): ?>
                                                    <button class="delete-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">Delete</button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="comment-form">
                    <?php if ($is_logged_in): ?>
                        <div class="comments-disabled-message" style="<?php echo ($post['status'] === 'completed' || $post['status'] === 'declined') ? '' : 'display: none;'; ?>">
                            <p>
                                <?php
                                if ($post['status'] === 'completed') {
                                    echo 'Comments are disabled for completed posts.';
                                } elseif ($post['status'] === 'declined') {
                                    echo 'Comments are disabled for declined posts.';
                                }
                                ?>
                            </p>
                        </div>

                        <?php if ($post['status'] !== 'completed' && $post['status'] !== 'declined'): ?>
                            <form id="add-comment-form" data-post-id="<?php echo $post['id']; ?>">
                                <h4>Add a Comment</h4>
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <div class="form-group">
                                    <textarea id="comment_content" name="comment_content" rows="4" required></textarea>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Submit Comment</button>
                                </div>
                            </form>
                        <?php endif; ?>
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

            // Set color for comment vote buttons that are active
            document.querySelectorAll('.comment-vote-btn.voted').forEach(btn => {
                if (btn.classList.contains('upvote')) {
                    btn.style.color = "#2563eb";
                } else if (btn.classList.contains('downvote')) {
                    btn.style.color = "#dc2626";
                }
            });
        });
    </script>
</body>

</html>