<?php

/**
 * This script handles retrieving and resending license keys to users
 */
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';
require_once '../../email_sender.php';

// Initialize response variables
$success_message = '';
$error_message = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to database
    $db = get_db_connection();

    // Get existing license key for this user's email (case-insensitive)
    $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE LOWER(email) = LOWER(?)');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // User has an existing license key
        $row = $result->fetch_assoc();
        $license_key = $row['license_key'];

        // Send the existing license key via email
        $email_sent = resend_license_email($email, $license_key);

        if ($email_sent) {
            $success_message = 'Your license key has been sent to your email address.';
        } else {
            $error_message = 'Failed to send email. Please try again later or <a href="../../contact/index.php">contact support</a>.';
        }
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Resend License Key - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
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
            <h1>Resend License Key</h1>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <div class="centered">
                    <a href="profile.php" class="btn btn-blue">Back to Profile</a>
                </div>
            <?php elseif ($error_message): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <div class="centered">
                    <a href="profile.php" class="btn btn-blue">Back to Profile</a>
                </div>
            <?php else: ?>
                <p class="auth-subtitle">We'll send your license key to your registered email address: <strong><?php echo htmlspecialchars($email); ?></strong></p>

                <div class="license-info">
                    <h3>License Key Information</h3>
                    <p>Your license key provides you with unlimited access to all premium features of Argo Books.</p>
                    <p>If you're unable to find your license key, use this form to have it resent to your email address.</p>
                </div>

                <form method="post" class="auth-form">
                    <div class="form-actions centered">
                        <button type="submit" class="btn btn-blue">Send License Key</button>
                        <a href="profile.php" class="btn btn-black">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>