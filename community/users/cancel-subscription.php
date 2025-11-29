<?php
session_start();
require_once '../../db_connect.php';
require_once '../../email_sender.php';
require_once '../community_functions.php';
require_once 'user_functions.php';
require_once '../../webhooks/paypal-helper.php';

// Ensure user is logged in
require_login();

$user_id = $_SESSION['user_id'];

// Get subscription info
$ai_subscription = get_user_ai_subscription($user_id);

// Redirect if no active subscription
if (!$ai_subscription || $ai_subscription['status'] !== 'active') {
    header('Location: ai-subscription.php');
    exit;
}

$error_message = '';

// Check credit status for warning messages
$originalCredit = floatval($ai_subscription['original_credit'] ?? 0);
$creditBalance = floatval($ai_subscription['credit_balance'] ?? 0);
$hasUnusedCredit = ($originalCredit > 0 && $creditBalance > 0);
$hasUsedCredit = ($originalCredit > 0 && $creditBalance < $originalCredit);
$creditUsed = $originalCredit - $creditBalance;

// Handle cancellation confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    try {
        // Get subscription details before cancelling
        $stmt = $pdo->prepare("
            SELECT subscription_id, email, end_date, credit_balance, original_credit, payment_method, paypal_subscription_id
            FROM ai_subscriptions
            WHERE user_id = ? AND status = 'active'
        ");
        $stmt->execute([$user_id]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        // If this is a PayPal subscription, cancel it on PayPal's side first
        $paypalCancelFailed = false;
        if ($subscription && $subscription['payment_method'] === 'paypal' && !empty($subscription['paypal_subscription_id'])) {
            try {
                $cancelled = cancelPayPalSubscription($subscription['paypal_subscription_id'], 'Cancelled by user from account settings');
                if (!$cancelled) {
                    $paypalCancelFailed = true;
                    error_log("Failed to cancel PayPal subscription: " . $subscription['paypal_subscription_id']);
                }
            } catch (Exception $e) {
                $paypalCancelFailed = true;
                error_log("Error cancelling PayPal subscription: " . $e->getMessage());
            }
        }

        // Cancel the subscription and invalidate any remaining credit
        // Credit is forfeited upon cancellation
        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET status = 'cancelled', auto_renew = 0, credit_balance = 0, cancelled_at = NOW(), updated_at = NOW()
            WHERE user_id = ? AND status = 'active'
        ");
        $stmt->execute([$user_id]);

        // Send cancellation email
        if ($subscription) {
            try {
                send_ai_subscription_cancelled_email(
                    $subscription['email'],
                    $subscription['subscription_id'],
                    $subscription['end_date']
                );
            } catch (Exception $e) {
                error_log("Failed to send cancellation email: " . $e->getMessage());
            }
        }

        $successMsg = 'Your AI subscription has been cancelled. You will retain access until the end of your billing period.';

        // Add PayPal warning if cancellation on their side failed
        if ($paypalCancelFailed) {
            $successMsg .= ' Note: We could not automatically cancel your PayPal subscription. Please also cancel it directly from your PayPal account to prevent future charges.';
        }

        // Add credit forfeiture notice if applicable
        $forfeitedCredit = floatval($subscription['credit_balance'] ?? 0);
        if ($forfeitedCredit > 0) {
            $successMsg .= ' Your remaining $' . number_format($forfeitedCredit, 2) . ' credit has been forfeited.';
        }

        // Add discount eligibility notice if credit was used
        $subOriginalCredit = floatval($subscription['original_credit'] ?? 0);
        if ($subOriginalCredit > 0 && $forfeitedCredit < $subOriginalCredit) {
            $successMsg .= ' Note: The premium user discount cannot be applied again if you resubscribe.';
        }

        $_SESSION['subscription_success'] = $successMsg;
        header('Location: ai-subscription.php');
        exit;
    } catch (PDOException $e) {
        $error_message = 'Failed to cancel subscription. Please contact support.';
    }
}

$end_date = date('F j, Y', strtotime($ai_subscription['end_date']));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cancel AI Subscription - Argo Community">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Cancel Subscription - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="../../resources/scripts/cursor-orb.js" defer></script>

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
        <div class="confirm-card cancel-card">
            <div class="confirm-icon cancel-icon">
                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>

            <h1>Cancel Your Subscription?</h1>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <p class="confirm-description">
                You're about to cancel your Argo AI subscription. Please review the following before confirming:
            </p>

            <div class="info-box warning-box">
                <h3>What happens when you cancel:</h3>
                <ul>
                    <li>You will retain access to AI features until <strong><?php echo $end_date; ?></strong></li>
                    <li>After this date, AI features will be disabled</li>
                    <li>Your subscription will not auto-renew</li>
                    <li>You can resubscribe anytime to restore access</li>
                    <?php if ($hasUnusedCredit): ?>
                    <li class="credit-warning"><strong>Your remaining $<?php echo number_format($creditBalance, 2); ?> credit will be forfeited</strong></li>
                    <?php endif; ?>
                </ul>
            </div>

            <?php if ($hasUnusedCredit): ?>
            <div class="info-box discount-warning-box">
                <h3>Credit Will Be Lost</h3>
                <p>You have $<?php echo number_format($creditBalance, 2); ?> in unused premium user discount credit.</p>
                <p><strong>This credit will be forfeited if you cancel.</strong> If you resubscribe later, the discount will not be available again.</p>
            </div>
            <?php endif; ?>

            <div class="info-box features-box">
                <h3>Features you'll lose access to:</h3>
                <ul>
                    <li>AI-powered receipt scanning</li>
                    <li>Predictive sales analysis</li>
                    <li>AI business insights</li>
                    <li>Natural language search</li>
                </ul>
            </div>

            <div class="confirm-actions">
                <form method="post">
                    <input type="hidden" name="confirm_cancel" value="1">
                    <button type="submit" class="btn btn-red">Yes, Cancel My Subscription</button>
                </form>
                <a href="ai-subscription.php" class="btn btn-outline">No, Keep My Subscription</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>
