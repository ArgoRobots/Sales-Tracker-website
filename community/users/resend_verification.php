<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';

// Check if user has a temporary ID or is logged in
$user_id = $_SESSION['temp_user_id'] ?? ($_SESSION['user_id'] ?? 0);

// Redirect if no user ID found
if ($user_id <= 0) {
    header('Location: login.php');
    exit;
}

$success = false;
$message = '';
$auto_redirect = isset($_GET['auto']) && $_GET['auto'] == 1;

// Process resend request
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $auto_redirect) {
    // Get user data
    $user = get_user($user_id);

    if ($user) {
        // Resend verification code
        $verification_success = resend_verification_code($user_id);

        if ($verification_success) {
            $success = true;
            $message = 'A new verification code has been sent to your email address.';
        } else {
            $message = 'Failed to send verification code. Please try again later.';
        }
    } else {
        $message = 'User not found. Please log out and try again.';
    }

    // Auto redirect to verification page
    if ($auto_redirect && $success) {
        header('Location: verify_code.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Resend Verification Code - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">

    <style>
        .btn-large {
            padding: 11px 0;
        }
    </style>
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h1>Resend Verification Code</h1>
            <p class="auth-subtitle">We'll send a new verification code to your registered email address</p>

            <?php if ($message): ?>
                <div class="<?php echo $success ? 'success-message' : 'error-message'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="form-actions centered">
                    <?php if (!$success): ?>
                        <button type="submit" class="btn btn-large btn-blue">Send Verification Code</button>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['temp_user_id'])): ?>
                        <a href="verify_code.php" class="btn btn-black">Back to Verification</a>
                    <?php else: ?>
                        <a href="profile.php" class="btn btn-black">Back to Profile</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>