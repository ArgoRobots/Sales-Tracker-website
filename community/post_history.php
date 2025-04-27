<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';

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

// Fetch edit history ordered by newest first
$db = get_db_connection();
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

    <link rel="stylesheet" href="view-post.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/footer/style.css">
    <style>
        .community-wrapper {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 10px;
        }

        .history-container {
            max-width: 800px;
            margin: 0 auto 50px auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .history-entry:not(:last-child) {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .history-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #6b7280;
            font-size: 14px;
        }

        .current-badge {
            display: inline-block;
            background-color: #16a34a;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }

        .diff-highlight {
            background-color: #fef9c3;
            padding: 2px 0;
        }

        .history-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--blueHeader);
        }

        .history-content {
            line-height: 1.6;
        }

        .no-history {
            text-align: center;
            padding: 30px;
            color: #6b7280;
        }
    </style>
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
                <?php foreach ($history as $index => $version): ?>
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
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>