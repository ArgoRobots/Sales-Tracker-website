<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

// Get post ID from URL parameter
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect to index if no valid post ID
if ($post_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get the post data
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

// Check if user is an admin
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Current user settings - no longer using email for voting
$current_user_email = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title><?php echo htmlspecialchars($post['title']); ?> - Argo Community</title>

    <script src="view-post.js"></script>
    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="view-post.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/footer/style.css">
    
</head>
<body>
    <header>
        <script>
            $(function () {
                $("#includeHeader").load("../resources/header/index.html", function () {
                    adjustLinksAndImages("#includeHeader");
                });
            });
        </script>
        <div id="includeHeader"></div>
    </header>

    <div class="community-container">
        <div class="community-header ">
            <h1>Argo Sales Tracker Community</h1>
            <p>Report bugs and suggest features to help us improve</p>
        </div>

        <div class="main-content">
            <a href="index.php" class="btn btn-secondary back-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to All Posts
            </a>
            
            <div class="post-detail">
                <div class="post-card" data-post-id="<?php echo $post['id']; ?>" data-post-type="<?php echo $post['post_type']; ?>">
                    <!-- Move votes to the left side -->
                    <div class="post-votes">
                        <button class="vote-btn upvote" data-post-id="<?php echo $post['id']; ?>" data-vote="up">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-width="2" d="M12 19V5M5 12l7-7 7 7"/>
                            </svg>
                        </button>
                        <span class="vote-count"><?php echo $post['votes']; ?></span>
                        <button class="vote-btn downvote" data-post-id="<?php echo $post['id']; ?>" data-vote="down">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-width="2" d="M12 5v14M5 12l7 7 7-7"/>
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
                                        switch($post['status']) {
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
                            </div>
                        </div>
                        <div class="post-body">
                            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        </div>
                        <div class="post-footer">
                            <div class="post-info">
                                <span class="post-author">Posted by <?php echo htmlspecialchars($post['user_name']); ?></span>
                                <span class="post-date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                <span class="post-views">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <?php echo (isset($post['views']) && (int)$post['views'] > 0) ? (int)$post['views'] : 0; ?> <?php echo ((isset($post['views']) && (int)$post['views'] == 1) ? 'view' : 'views'); ?>
                                </span>
                            </div>
                            <?php if ($is_admin): ?>
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
                </div>
                
                <div class="comments-section">
                    <h3><?php echo count($comments); ?> Comments</h3>
                    
                    <div class="comments-container">
                        <?php if (empty($comments)): ?>
                            <p class="no-comments">No comments yet. Be the first to comment!</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment" data-comment-id="<?php echo $comment['id']; ?>">
                                    <div class="comment-header">
                                        <span class="comment-author"><?php echo htmlspecialchars($comment['user_name']); ?></span>
                                        <span class="comment-date"><?php echo date('M j, Y g:i a', strtotime($comment['created_at'])); ?></span>
                                    </div>
                                    <div class="comment-content">
                                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                    </div>
                                    <?php if ($is_admin): ?>
                                    <div class="comment-actions">
                                        <button class="delete-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">Delete</button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="comment-form">
                        <h4>Add a Comment</h4>
                        <form id="add-comment-form" data-post-id="<?php echo $post['id']; ?>">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <div class="form-group">
                                <label for="comment_content">Comment</label>
                                <textarea id="comment_content" name="comment_content" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Comment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <script>
            $(function () {
                $("#includeFooter").load("../resources/footer/index.html", function () {
                    adjustLinksAndImages("#includeFooter");
                });
            });
        </script>
        <div id="includeFooter"></div>
</body>
</html>