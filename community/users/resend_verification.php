<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';
require_once '../../email_sender.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$success = false;
$message = '';
$auto_redirect = isset($_GET['auto']) && $_GET['auto'] == 1;

// Process resend request
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $auto_redirect) {
    $user_id = $_SESSION['user_id'];

    // Get user data
    $user = get_user($user_id);

    if ($user) {
        // Generate a license key (this would normally come from your license system)
        $license_key = generate_license_key($user['email']);

        // Send license email
        $license_sent = false;
        if ($license_key) {
            // Note: This requires the email_sender.php functions
            $license_sent = send_license_email($user['email'], $license_key);
        }

        // If email is not verified, also resend verification email
        if (!$user['email_verified']) {
            // Resend verification email
            $verification_sent = resend_verification_email($user_id);

            if ($verification_sent && $license_sent) {
                $success = true;
                $message = 'Your license key has been sent to your email. We have also sent a verification email to complete your account activation.';
            } elseif ($license_sent) {
                $success = true;
                $message = 'Your license key has been sent to your email. However, we could not send the verification email. Please try again later.';
            } else {
                $message = 'Failed to send emails. Please try again later.';
            }
        } else {
            // Email already verified, just send license
            if ($license_sent) {
                $success = true;
                $message = 'Your license key has been sent to your email address.';
            } else {
                $message = 'Failed to send license email. Please try again later.';
            }
        }
    } else {
        $message = 'User not found. Please log out and log back in.';
    }

    // Auto redirect to profile page
    if ($auto_redirect) {
        header('Location: profile.php?license_sent=1');
        exit;
    }
}

// Function to generate a mock license key
function generate_license_key($email)
{
    // In a real implementation, this would connect to your licensing system
    // Here we're just creating a dummy key based on email and timestamp
    $email_hash = substr(md5($email), 0, 8);
    $timestamp = substr(md5(time()), 0, 8);
    return strtoupper($email_hash . '-' . $timestamp . '-ARGO');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Get License - Argo Community</title>

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
            padding: 10px 0;
        }
    </style>
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h1>Get Your License</h1>
            <p class="auth-subtitle">Your license key will be resent to your registered email address</p>

            <?php if ($message): ?>
                <div class="<?php echo $success ? 'success-message' : 'error-message'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>


            <form method="post" class="auth-form">
                <div class="form-actions centered"> <?php if (!$success): ?>
                        <button type="submit" class="btn btn-large btn-blue">Send License Key</button>
                    <?php endif; ?>
                    <a href="profile.php" class="btn btn-black">Back to Profile</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>