<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';

// Handle post submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $user_email = isset($_POST['user_email']) ? trim($_POST['user_email']) : '';
    $title = isset($_POST['post_title']) ? trim($_POST['post_title']) : '';
    $content = isset($_POST['post_content']) ? trim($_POST['post_content']) : '';
    $post_type = isset($_POST['post_type']) ? trim($_POST['post_type']) : '';
    
    // Basic validation
    if (empty($user_name) || empty($user_email) || empty($title) || empty($content) || empty($post_type)) {
        $error_message = 'All fields are required';
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } elseif (!in_array($post_type, ['bug', 'feature'])) {
        $error_message = 'Invalid post type';
    } elseif (strlen($title) > 255) {
        $error_message = 'Title is too long (maximum 255 characters)';
    } elseif (strlen($content) > 10000) {
        $error_message = 'Content is too long (maximum 10,000 characters)';
    } else {
        // Store user's email in session
        $_SESSION['user_email'] = $user_email;
        
        // Add the post
        $post_id = add_post($user_name, $user_email, $title, $content, $post_type);
        
        if ($post_id) {
            $success_message = 'Your post has been submitted successfully. Redirecting to post...';
            // Redirect to the new post after 2 seconds
            header("refresh:2;url=view_post.php?id=$post_id");
        } else {
            $error_message = 'Error adding post to the database';
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
    
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/footer/style.css">
    <link rel="stylesheet" href="create-post.css">
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
    </div>

    <div class="community-wrapper">
        <div class="post-form-container">
            <div class="navigation">
                <a href="index.php" class="btn btn-blue">← Back to Community</a>
            </div>
            
            <?php if ($success_message): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="post-form">
                <h2>Create New Post</h2>
                <form id="community-post-form" method="post" action="create_post.php">
                    <div class="form-group">
                        <label for="post_title">Title</label>
                        <input type="text" id="post_title" name="post_title" required>
                    </div>
                    <div class="form-group">
                        <label for="post_type">Post Type</label>
                        <select id="post_type" name="post_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="bug">Bug Report</option>
                            <option value="feature">Feature Request</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="post_content">Description</label>
                        <textarea id="post_content" name="post_content" required></textarea>
                    </div>
                    <div class="form-actions">
                        <a href="index.php" class="btn btn-black">Cancel</a>
                        <button type="submit" class="btn btn-blue">Submit Post</button>
                    </div>
                </form>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fill form fields from localStorage if available
            const savedName = localStorage.getItem('community_user_name');
            const savedEmail = localStorage.getItem('community_user_email');
            
            const userNameInput = document.getElementById('user_name');
            const userEmailInput = document.getElementById('user_email');
            
            if (savedName && userNameInput) {
                userNameInput.value = savedName;
            }
            
            if (savedEmail && userEmailInput) {
                userEmailInput.value = savedEmail;
            }
            
            // Store user info when form is submitted
            const form = document.getElementById('community-post-form');
            if (form) {
                form.addEventListener('submit', function() {
                    if (userNameInput && userEmailInput) {
                        localStorage.setItem('community_user_name', userNameInput.value);
                        localStorage.setItem('community_user_email', userEmailInput.value);
                    }
                });
            }
        });
    </script>
</body>
</html>