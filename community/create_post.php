<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';
include_once 'rate_limit.php';

require_login('', true);
$current_user = get_current_user_ID();

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
                $stmt = $db->prepare('UPDATE community_posts SET user_id = :user_id WHERE id = :post_id');
                $stmt->bindValue(':user_id', $current_user['id'], SQLITE3_INTEGER);
                $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
                $stmt->execute();

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

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="create-post.css">
    <link rel="stylesheet" href="rate-limit.css">
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
                <h2>Create New Post</h2>

                <?php if ($html_message): ?>
                    <?php echo $html_message; ?>
                <?php endif; ?>

                <form id="community-post-form" method="post" action="create_post.php">
                    <div class="form-group">
                        <label for="post_title">Title</label>
                        <input type="text" id="post_title" name="post_title" value="<?php echo isset($_POST['post_title']) ? htmlspecialchars($_POST['post_title']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="post_type">Post Type</label>
                        <select id="post_type" name="post_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="bug" <?php echo (isset($_POST['post_type']) && $_POST['post_type'] === 'bug') ? 'selected' : ''; ?>>Bug Report</option>
                            <option value="feature" <?php echo (isset($_POST['post_type']) && $_POST['post_type'] === 'feature') ? 'selected' : ''; ?>>Feature Request</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="post_content">Description</label>
                        <textarea id="post_content" name="post_content" required><?php echo isset($_POST['post_content']) ? htmlspecialchars($_POST['post_content']) : ''; ?></textarea>
                    </div>
                    <div class="form-actions">
                        <a href="index.php" class="btn btn-black">Cancel</a>
                        <button type="submit" class="btn btn-blue <?php if ($html_message) echo 'btn-disabled'; ?>" <?php if ($html_message) echo 'disabled'; ?>>Submit Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const countdownElements = document.querySelectorAll('.countdown-timer');

            countdownElements.forEach(el => {
                const targetTime = parseInt(el.dataset.resetTimestamp, 10);

                function updateCountdown() {
                    const now = Math.floor(Date.now() / 1000);
                    const remaining = targetTime - now;

                    if (remaining <= 0) {
                        el.textContent = 'now';
                        return;
                    }

                    const minutes = Math.floor(remaining / 60);
                    const seconds = remaining % 60;
                    el.textContent = `${minutes}m ${seconds}s`;
                }

                updateCountdown();
                const interval = setInterval(() => {
                    updateCountdown();
                    if (Math.floor(Date.now() / 1000) >= targetTime) {
                        clearInterval(interval);
                    }
                }, 1000);
            });
        });
    </script>
</body>

</html>