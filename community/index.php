<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

// Get all posts with vote counts
$posts = get_all_posts();

// Check if user is an admin
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Get current user email if available (for voting)
$current_user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Argo Sales Tracker - Community</title>

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>
    <script src="community.js" defer></script>
    
    <link rel="stylesheet" href="community-style.css">
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

    <div class="community-header">
        <h1>Argo Sales Tracker Community</h1>
        <p>Report bugs and suggest features to help us improve</p>
    </div>

    <div class="community-wrapper">
        <div class="community-content">
            <div class="community-actions">
                <div class="action-left">
                    <a href="create_post.php" class="btn btn-blue">Create New Post</a>
                    
                    <!-- New Category Filter -->
                    <div class="filter-dropdown">
                        <select id="category-filter" class="filter-select">
                            <option value="all">All Categories</option>
                            <option value="bug">Bug Reports</option>
                            <option value="feature">Feature Requests</option>
                        </select>
                    </div>
                </div>
                
                <div class="search-box">
                    <input type="text" id="search-posts" placeholder="Search...">
                    <button id="search-btn" class="btn search-icon-btn" aria-label="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Add bulk selection controls -->
            <div class="bulk-actions" style="display: none;">
                <div class="selection-controls">
                    <label class="select-all-container">
                        <input type="checkbox" id="select-all-posts">
                        <span class="checkbox-label">Select All</span>
                    </label>
                    <span class="selected-count">0 selected</span>
                </div>
                <button id="delete-selected" class="btn btn-danger" disabled>Delete Selected</button>
            </div>

            <!-- Loading indicator for infinite scroll -->
            <div id="loading-indicator" style="display: none;">
                <div class="spinner"></div>
                <p>Loading more posts...</p>
            </div>

            <!-- Template for post selection checkbox -->
            <div class="post-select">
                <input type="checkbox" class="post-checkbox">
            </div>

            <div id="posts-container" class="posts-container">
                <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <h3>No posts yet!</h3>
                    <p>Be the first to create a post in our community.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                    <div class="post-card" data-post-id="<?php echo $post['id']; ?>" data-post-type="<?php echo $post['post_type']; ?>">
                        <div class="post-votes">
                            <button class="vote-btn upvote" data-post-id="<?php echo $post['id']; ?>" data-vote="up" <?php echo empty($current_user_email) ? 'disabled' : ''; ?>>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-width="2" d="M12 19V5M5 12l7-7 7 7"/>
                                </svg>
                            </button>
                            <span class="vote-count"><?php echo $post['votes']; ?></span>
                            <button class="vote-btn downvote" data-post-id="<?php echo $post['id']; ?>" data-vote="down" <?php echo empty($current_user_email) ? 'disabled' : ''; ?>>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-width="2" d="M12 5v14M5 12l7 7 7-7"/>
                                </svg>
                            </button>
                        </div>
                        <div class="post-content">
                            <a href="view_post.php?id=<?php echo $post['id']; ?>" class="post-link">
                                <div class="post-header">
                                    <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
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
                            </a>
                            <div class="post-footer">
                                <div class="post-info">
                                    <span class="post-author">Posted by <?php echo htmlspecialchars($post['user_name']); ?></span>
                                    <span class="post-date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                                <div class="post-actions">
                                    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="view-comments-btn">
                                        Comments (<?php echo get_comment_count($post['id']); ?>)
                                    </a>
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
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
    </footer>
</body>
</html>