<?php
session_start();
require_once '../db_connect.php';
require_once 'community_functions.php';
require_once 'users/user_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: users/login.php');
    exit;
}

// Get post ID from URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect to index if no valid post ID
if ($post_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get the post
$post = get_post($post_id);

// Redirect if post not found
if (!$post) {
    header('Location: index.php');
    exit;
}

// Check if user has permission to edit
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

// Determine if user can edit this post
$can_edit_post = ($role === 'admin') ||
    (isset($post['user_id']) && $post['user_id'] == $user_id);

if (!$can_edit_post) {
    // Redirect to view post if no permission
    header("Location: view_post.php?id=$post_id");
    exit;
}

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $post_type = isset($_POST['post_type']) ? trim($_POST['post_type']) : '';

    // Basic validation
    if (empty($title) || empty($content) || empty($post_type)) {
        $error_message = 'All fields are required';
    } elseif (strlen($title) > 255) {
        $error_message = 'Title is too long (maximum 255 characters)';
    } elseif (strlen($content) > 10000) {
        $error_message = 'Content is too long (maximum 10,000 characters)';
    } elseif (!in_array($post_type, ['bug', 'feature'])) {
        $error_message = 'Invalid post type';
    } else {
        // Update the post
        $db = get_db_connection();

        $stmt = $db->prepare('UPDATE community_posts 
                             SET title = :title, content = :content, post_type = :post_type, updated_at = CURRENT_TIMESTAMP 
                             WHERE id = :id');
        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
        $stmt->bindValue(':post_type', $post_type, SQLITE3_TEXT);
        $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            // Redirect immediately to view page with success message
            header("Location: view_post.php?id=$post_id&updated=1");
            exit;
        } else {
            $error_message = 'Error updating the post';
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
    <title>Edit Post - Argo Community</title>

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/footer/style.css">
    <link rel="stylesheet" href="create-post.css">
    <style>
        .post-form h2 {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
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
    </div>

    <div class="community-wrapper">
        <div class="post-form-container">
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
                <h2>Edit Post</h2>

                <form method="post" action="edit_post.php?id=<?php echo $post_id; ?>">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="post_type">Post Type</label>
                        <select id="post_type" name="post_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="bug" <?php echo $post['post_type'] === 'bug' ? 'selected' : ''; ?>>Bug Report</option>
                            <option value="feature" <?php echo $post['post_type'] === 'feature' ? 'selected' : ''; ?>>Feature Request</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="view_post.php?id=<?php echo $post_id; ?>" class="btn btn-black">Cancel</a>
                        <button type="submit" class="btn btn-blue">Save Changes</button>
                    </div>
                </form>
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
</body>

</html>