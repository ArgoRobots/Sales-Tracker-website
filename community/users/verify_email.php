<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$success = false;
$message = '';

if (!empty($token)) {
    $success = verify_email($token);

    if ($success) {
        $message = 'Your email has been verified successfully. You can now log in to your account.';

        // If user is already logged in, update their session
        if (isset($_SESSION['user_id'])) {
            $_SESSION['email_verified'] = 1;
        }
    } else {
        $message = 'Invalid or expired verification token. Please request a new verification email.';
    }
} else {
    $message = 'No verification token provided.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Email Verification - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
    <link rel="stylesheet" href="auth-style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h1>Email Verification</h1>

            <div class="<?php echo $success ? 'success-message' : 'error-message'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

            <div class="auth-links centered">
                <?php if ($success): ?>
                    <a href="login.php" class="btn btn-blue">Log In</a>
                <?php else: ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <p>
                            <a href="resend_verification.php" class="btn btn-secondary">Resend Verification Email</a>
                        </p>
                        <p>
                            <a href="index.php" class="btn btn-blue">Back to Homepage</a>
                        </p>
                    <?php else: ?>
                        <p>
                            <a href="login.php" class="btn btn-blue">Back to Login</a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>