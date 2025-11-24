<?php
session_start();
require_once '../../db_connect.php';
require_once '../community_functions.php';
require_once 'user_functions.php';

// Ensure user is logged in
require_login();

$user_id = $_SESSION['user_id'];

// Get subscription info
$ai_subscription = get_user_ai_subscription($user_id);

// Redirect if no subscription or not in a reactivatable state
if (!$ai_subscription || !in_array($ai_subscription['status'], ['cancelled', 'payment_failed'])) {
    header('Location: ai-subscription.php');
    exit;
}

// Check if subscription is expired
$is_expired = strtotime($ai_subscription['end_date']) < time();
if ($is_expired) {
    $_SESSION['subscription_error'] = 'Your subscription has expired. Please subscribe again to access AI features.';
    header('Location: ai-subscription.php');
    exit;
}

$error_message = '';

// Handle reactivation confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reactivate'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET status = 'active', auto_renew = 1, cancelled_at = NULL, updated_at = NOW()
            WHERE user_id = ? AND status IN ('cancelled', 'payment_failed')
            AND end_date > NOW()
        ");
        $stmt->execute([$user_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['subscription_success'] = 'Your subscription has been reactivated! AI features are now available.';
        } else {
            $_SESSION['subscription_error'] = 'Could not reactivate subscription. It may have expired.';
        }
        header('Location: ai-subscription.php');
        exit;
    } catch (PDOException $e) {
        $error_message = 'Failed to reactivate subscription. Please contact support.';
    }
}

$end_date = date('F j, Y', strtotime($ai_subscription['end_date']));
$status = $ai_subscription['status'];
$payment_method = ucfirst($ai_subscription['payment_method'] ?? 'Unknown');
$billing_cycle = $ai_subscription['billing_cycle'] ?? 'monthly';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reactivate AI Subscription - Argo Community">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Reactivate Subscription - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="ai-subscription.css">
    <link rel="stylesheet" href="subscription-confirm.css">
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

    <div class="confirm-page-container">
        <div class="confirm-card reactivate-card">
            <div class="confirm-icon reactivate-icon">
                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 11-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                </svg>
            </div>

            <h1>Reactivate Your Subscription?</h1>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if ($status === 'payment_failed'): ?>
                <div class="alert alert-warning">
                    Your previous payment failed. Reactivating will attempt to charge your payment method on file.
                </div>
            <?php endif; ?>

            <p class="confirm-description">
                You're about to reactivate your Argo AI subscription. Here's what you need to know:
            </p>

            <div class="info-box success-box">
                <h3>What happens when you reactivate:</h3>
                <ul>
                    <li>AI features will be immediately available</li>
                    <li>Auto-renewal will be enabled</li>
                    <li>Your next billing date remains <strong><?php echo $end_date; ?></strong></li>
                    <li>No additional charges until your next billing date</li>
                </ul>
            </div>

            <div class="info-box payment-method-box">
                <h3>Current Payment Method</h3>
                <div class="current-payment-method">
                    <div class="payment-method-icon">
                        <?php if (strtolower($ai_subscription['payment_method']) === 'stripe'): ?>
                            <img src="../../images/Stripe-logo.svg" alt="Stripe">
                        <?php elseif (strtolower($ai_subscription['payment_method']) === 'paypal'): ?>
                            <img src="../../images/PayPal-logo.svg" alt="PayPal">
                        <?php elseif (strtolower($ai_subscription['payment_method']) === 'square'): ?>
                            <img src="../../images/Square-logo.svg" alt="Square">
                        <?php else: ?>
                            <img src="../../images/Stripe-logo.svg" alt="Payment">
                        <?php endif; ?>
                    </div>
                    <div class="payment-method-details">
                        <span class="payment-method-name"><?php echo $payment_method; ?> (<?php echo ucfirst($billing_cycle); ?>)</span>
                        <span class="payment-method-note">This payment method will be charged on <?php echo $end_date; ?></span>
                    </div>
                </div>

                <div class="change-payment-section">
                    <p class="change-payment-label">Change billing cycle:</p>
                    <div class="billing-cycle-options">
                        <a href="../../upgrade/ai/checkout/?method=<?php echo strtolower($ai_subscription['payment_method']); ?>&billing=monthly&change_method=1" class="billing-cycle-btn <?php echo $billing_cycle === 'monthly' ? 'current' : ''; ?>">
                            <span class="billing-cycle-name">Monthly</span>
                            <span class="billing-cycle-price">$5/month</span>
                        </a>
                        <a href="../../upgrade/ai/checkout/?method=<?php echo strtolower($ai_subscription['payment_method']); ?>&billing=yearly&change_method=1" class="billing-cycle-btn <?php echo $billing_cycle === 'yearly' ? 'current' : ''; ?>">
                            <span class="billing-cycle-name">Yearly</span>
                            <span class="billing-cycle-price">$50/year</span>
                        </a>
                    </div>
                </div>

                <div class="change-payment-section">
                    <p class="change-payment-label">Change payment provider:</p>
                    <div class="payment-provider-options">
                        <a href="../../upgrade/ai/checkout/?method=stripe&billing=<?php echo $billing_cycle; ?>&change_method=1" class="payment-provider-btn <?php echo strtolower($ai_subscription['payment_method']) === 'stripe' ? 'current' : ''; ?>">
                            <img src="../../images/Stripe-logo.svg" alt="Stripe">
                        </a>
                        <a href="../../upgrade/ai/checkout/?method=paypal&billing=<?php echo $billing_cycle; ?>&change_method=1" class="payment-provider-btn <?php echo strtolower($ai_subscription['payment_method']) === 'paypal' ? 'current' : ''; ?>">
                            <img src="../../images/PayPal-logo.svg" alt="PayPal">
                        </a>
                        <a href="../../upgrade/ai/checkout/?method=square&billing=<?php echo $billing_cycle; ?>&change_method=1" class="payment-provider-btn <?php echo strtolower($ai_subscription['payment_method']) === 'square' ? 'current' : ''; ?>">
                            <img src="../../images/Square-logo.svg" alt="Square">
                        </a>
                    </div>
                </div>
            </div>

            <div class="confirm-actions">
                <form method="post">
                    <input type="hidden" name="confirm_reactivate" value="1">
                    <button type="submit" class="btn btn-purple">Reactivate with <?php echo $payment_method; ?></button>
                </form>
                <a href="ai-subscription.php" class="btn btn-outline">Go Back</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>
