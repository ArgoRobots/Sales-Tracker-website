<?php

/**
 * This script handles retrieving and resending AI subscription IDs to users
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

// Get subscription info
$ai_subscription = get_user_ai_subscription($user_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ai_subscription) {
    $subscription_id = $ai_subscription['subscription_id'];
    $billing_cycle = $ai_subscription['billing_cycle'];
    $end_date = $ai_subscription['end_date'];
    $subscription_email = $ai_subscription['email'] ?? $email;

    // Send the subscription ID via email
    $send_to = !empty($subscription_email) ? $subscription_email : $email;
    $email_sent = resend_subscription_id_email($send_to, $subscription_id, $billing_cycle, $end_date);

    if ($email_sent) {
        $success_message = 'Your subscription ID has been sent to your email address.';
    } else {
        $error_message = 'Failed to send email. Please try again later or <a href="../../contact-us/">contact support</a>.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Resend Subscription ID - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="../../resources/scripts/cursor-orb.js" defer></script>

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
    <style>
        .subscription-info {
            background: linear-gradient(135deg, #f5f3ff, #ede9fe);
            border: 1px solid #c4b5fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .subscription-info h3 {
            color: #7c3aed;
            margin-top: 0;
        }
    </style>
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h1>Resend Subscription ID</h1>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <div class="centered">
                    <a href="ai-subscription.php" class="btn btn-purple">Back to Subscription</a>
                </div>
            <?php elseif ($error_message): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
                <div class="centered">
                    <a href="ai-subscription.php" class="btn btn-purple">Back to Subscription</a>
                </div>
            <?php elseif (!$ai_subscription): ?>
                <div class="error-message">
                    You don't have an active AI subscription.
                </div>
                <div class="centered">
                    <a href="../../upgrade/ai/" class="btn btn-purple">Subscribe to AI Features</a>
                </div>
            <?php else: ?>
                <p class="auth-subtitle">We'll send your AI subscription ID to your registered email address: <strong><?php echo htmlspecialchars($email); ?></strong></p>

                <div class="subscription-info">
                    <h3>Subscription ID Information</h3>
                    <p>Your subscription ID is a unique identifier for your AI subscription. Keep it safe for your records.</p>
                    <p>You may need this ID when contacting support about billing or subscription issues.</p>
                </div>

                <form method="post" class="auth-form">
                    <div class="form-actions centered">
                        <button type="submit" class="btn btn-purple">Send Subscription ID</button>
                        <a href="ai-subscription.php" class="btn btn-black">Cancel</a>
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
