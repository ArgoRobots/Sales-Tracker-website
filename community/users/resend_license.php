<?php

/**
 * This script handles retrieving and resending license keys and subscription IDs to users
 */
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';
require_once '../../email_sender.php';

// Initialize response variables
$license_success = '';
$license_error = '';
$subscription_success = '';
$subscription_error = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'] ?? '';

// Check if user has premium license
$db = get_db_connection();
$stmt = $db->prepare('SELECT license_key, email FROM license_keys WHERE (user_id = ? OR LOWER(email) = LOWER(?)) AND license_key IS NOT NULL AND license_key != "" LIMIT 1');
$stmt->bind_param('is', $user_id, $email);
$stmt->execute();
$result = $stmt->get_result();
$premium_license = $result->fetch_assoc();
$has_premium_license = ($premium_license !== null);
$stmt->close();

// Check if user has AI subscription
$ai_subscription = get_user_ai_subscription($user_id);
$has_ai_subscription = ($ai_subscription !== null);

// Handle form submission for premium license
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_license']) && $has_premium_license) {
    $license_key = $premium_license['license_key'];
    $license_email = $premium_license['email'];

    // Send the existing license key via email (use license email, fallback to session email)
    $send_to = !empty($license_email) ? $license_email : $email;
    $email_sent = resend_license_email($send_to, $license_key);

    if ($email_sent) {
        $license_success = 'Your license key has been sent to your email address.';
    } else {
        $license_error = 'Failed to send email. Please try again later or <a href="../../contact-us/">contact support</a>.';
    }
}

// Handle form submission for subscription ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_subscription']) && $has_ai_subscription) {
    $subscription_id = $ai_subscription['subscription_id'];
    $billing_cycle = $ai_subscription['billing_cycle'];
    $end_date = $ai_subscription['end_date'];
    $subscription_email = $ai_subscription['email'] ?? $email;

    // Send the subscription ID via email
    $send_to = !empty($subscription_email) ? $subscription_email : $email;
    $email_sent = resend_subscription_id_email($send_to, $subscription_id, $billing_cycle, $end_date);

    if ($email_sent) {
        $subscription_success = 'Your subscription ID has been sent to your email address.';
    } else {
        $subscription_error = 'Failed to send email. Please try again later or <a href="../../contact-us/">contact support</a>.';
    }
}

// If user has neither, redirect to profile
if (!$has_premium_license && !$has_ai_subscription) {
    header('Location: profile.php');
    exit;
}

// Determine page title based on what user has
$page_title = 'Resend ';
if ($has_premium_license && $has_ai_subscription) {
    $page_title .= 'License Key / Subscription ID';
} elseif ($has_premium_license) {
    $page_title .= 'License Key';
} else {
    $page_title .= 'Subscription ID';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title><?php echo htmlspecialchars($page_title); ?> - Argo Community</title>

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
        .resend-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .resend-section.premium {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-color: #93c5fd;
        }
        .resend-section.subscription {
            background: linear-gradient(135deg, #f5f3ff, #ede9fe);
            border-color: #c4b5fd;
        }
        .resend-section h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        .resend-section.premium h3 {
            color: #1d4ed8;
        }
        .resend-section.subscription h3 {
            color: #7c3aed;
        }
        .resend-section p {
            margin-bottom: 15px;
            color: #6b7280;
        }
        .section-divider {
            margin: 30px 0;
            text-align: center;
            position: relative;
        }
        .section-divider::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            border-top: 1px solid #e5e7eb;
        }
        .section-divider span {
            background: #fff;
            padding: 0 15px;
            position: relative;
            color: #9ca3af;
            font-size: 14px;
        }
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .error-message a {
            color: #721c24;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>

            <p class="auth-subtitle">We'll send your information to your registered email address: <strong><?php echo htmlspecialchars($email); ?></strong></p>

            <?php if ($has_premium_license): ?>
                <div class="resend-section premium">
                    <h3>Premium License Key</h3>

                    <?php if ($license_success): ?>
                        <div class="success-message">
                            <?php echo htmlspecialchars($license_success); ?>
                        </div>
                    <?php elseif ($license_error): ?>
                        <div class="error-message">
                            <?php echo $license_error; ?>
                        </div>
                    <?php else: ?>
                        <p>Your premium license key provides unlimited access to all premium features of Argo Books.</p>
                        <form method="post">
                            <input type="hidden" name="resend_license" value="1">
                            <button type="submit" class="btn btn-blue">Send License Key</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($has_premium_license && $has_ai_subscription): ?>
                <div class="section-divider">
                    <span>AND</span>
                </div>
            <?php endif; ?>

            <?php if ($has_ai_subscription): ?>
                <div class="resend-section subscription">
                    <h3>AI Subscription ID</h3>

                    <?php if ($subscription_success): ?>
                        <div class="success-message">
                            <?php echo htmlspecialchars($subscription_success); ?>
                        </div>
                    <?php elseif ($subscription_error): ?>
                        <div class="error-message">
                            <?php echo $subscription_error; ?>
                        </div>
                    <?php else: ?>
                        <p>Your AI subscription ID is a unique identifier for your subscription. You may need it when contacting support.</p>
                        <form method="post">
                            <input type="hidden" name="resend_subscription" value="1">
                            <button type="submit" class="btn btn-purple">Send Subscription ID</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="centered" style="margin-top: 20px;">
                <a href="profile.php" class="btn btn-black">Back to Profile</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>
