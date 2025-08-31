<?php
session_start();
require_once '../../db_connect.php';
require_once '../community_functions.php';
require_once 'user_functions.php';

// Ensure user is logged in
require_login('', true);

$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match';
    } else {
        $result = update_user_profile($user_id, $username, $email, $password);
        if ($result['success']) {
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            header('Location: profile.php?username=' . urlencode($username));
            exit;
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account - Argo Community</title>
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="../../resources/notifications/notifications.js" defer></script>
    <link rel="stylesheet" href="edit-profile.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
    <link rel="stylesheet" href="../../resources/notifications/notifications.css">
</head>
<body>
<header>
    <div id="includeHeader"></div>
</header>

<div class="edit-profile-container">
    <h1>Edit Account</h1>
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" class="edit-profile-form">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="password">New Password</label>
        <input type="password" id="password" name="password">

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password">

        <div class="form-actions">
            <button type="submit" class="btn btn-blue">Save Changes</button>
            <a href="profile.php?username=<?php echo urlencode($user['username']); ?>" class="btn btn-gray">Cancel</a>
        </div>
    </form>
</div>

<footer class="footer">
    <div id="includeFooter"></div>
</footer>
</body>
</html>
