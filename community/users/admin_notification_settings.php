<?php
session_start();
require_once '../../db_connect.php';
require_once '../community_functions.php';
require_once 'user_functions.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

$db = get_db_connection();

// Load current notification settings
$stmt = $db->prepare('SELECT * FROM admin_notification_settings WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();

// If no settings exist, create default ones
if (!$settings) {
    $settings = [
        'notify_new_posts' => 1,
        'notify_new_comments' => 1,
        'notification_email' => $_SESSION['email']
    ];

    // Create settings row
    $stmt = $db->prepare('INSERT INTO admin_notification_settings 
                         (user_id, notify_new_posts, notify_new_comments, notification_email) 
                         VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iiis', $user_id, $settings['notify_new_posts'], $settings['notify_new_comments'], $settings['notification_email']);
    $stmt->execute();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $notify_new_posts = isset($_POST['notify_new_posts']) ? 1 : 0;
    $notify_new_comments = isset($_POST['notify_new_comments']) ? 1 : 0;
    $notification_email = isset($_POST['notification_email']) ? trim($_POST['notification_email']) : '';

    // Validate email
    if (empty($notification_email) || !filter_var($notification_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } else {
        // Update settings
        $stmt = $db->prepare('UPDATE admin_notification_settings 
                             SET notify_new_posts = ?, 
                                 notify_new_comments = ?, 
                                 notification_email = ?,
                                 updated_at = CURRENT_TIMESTAMP 
                             WHERE user_id = ?');
        $stmt->bind_param('iisi', $notify_new_posts, $notify_new_comments, $notification_email, $user_id);

        if ($stmt->execute()) {
            $success_message = 'Notification settings updated successfully.';
            // Update settings array to reflect changes
            $settings['notify_new_posts'] = $notify_new_posts;
            $settings['notify_new_comments'] = $notify_new_comments;
            $settings['notification_email'] = $notification_email;
        } else {
            $error_message = 'Error updating notification settings: ' . $db->error;
        }
    }
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Admin Notification Settings - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="admin_notification_settings-style.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/checkbox.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="wrapper">
        <div class="admin-settings-container">
            <div class="admin-settings-header">
                <h1>Admin Notification Settings</h1>
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

            <form method="post" class="admin-settings-form">
                <div class="settings-section">
                    <h2>Email Notifications</h2>

                    <div class="checkbox">
                        <input type="checkbox" id="notify_new_posts" name="notify_new_posts" <?php echo $settings['notify_new_posts'] ? 'checked' : ''; ?>>
                        <label for="notify_new_posts">New Post Notifications</label>
                    </div>
                    <p class="setting-description">Receive an email notification when a new post is created in the community.</p>

                    <div class="checkbox">
                        <input type="checkbox" id="notify_new_comments" name="notify_new_comments" <?php echo $settings['notify_new_comments'] ? 'checked' : ''; ?>>
                        <label for="notify_new_comments">New Comment Notifications</label>
                    </div>
                    <p class="setting-description">Receive an email notification when someone comments on any post in the community.</p>

                    <div class="email-field">
                        <label for="notification_email">Notification Email</label>
                        <input type="email" id="notification_email" name="notification_email" value="<?php echo htmlspecialchars($settings['notification_email']); ?>" required>
                        <p class="hint">Email address where notifications will be sent</p>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="profile.php" class="btn btn-black">Back to Profile</a>
                    <button type="submit" class="btn btn-blue">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>