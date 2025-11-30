<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Check for token in URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    header('Location: forgot_password.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';

    // Basic validation
    if (empty($password) || empty($password_confirm) || empty($token)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match';
    } else {
        // Attempt to reset password
        $result = reset_password($token, $password);

        if ($result) {
            $success = 'Your password has been reset successfully.';
        } else {
            $error = 'Invalid or expired token. Please request a new password reset link.';
        }
    }
}

// Verify token is valid
$db = get_db_connection();
$stmt = $db->prepare('SELECT id FROM community_users WHERE reset_token = ? AND reset_token_expiry > CURRENT_TIMESTAMP');
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$valid_token = ($result->num_rows > 0);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Reset Password - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/link.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h1>Reset Password</h1>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                    <div class="centered">
                        <a href="login.php" class="btn btn-blue">Proceed to Login</a>
                    </div>
                </div>
            <?php elseif ($valid_token): ?>
                <form method="post" class="auth-form">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required>
                        <small>At least 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirm New Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-blue btn-block">Reset Password</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="error-message">
                    Invalid or expired password reset link. Please request a new one.
                </div>
                <div class="auth-links">
                    <p><a href="forgot_password.php" class="btn btn-blue link-no-underline">Request New Link</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>