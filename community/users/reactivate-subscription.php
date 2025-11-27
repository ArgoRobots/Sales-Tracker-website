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

// Check if this is a PayPal subscription that was cancelled
// PayPal subscriptions cannot be reactivated via API once cancelled - user must create a new subscription
$is_cancelled_paypal = ($ai_subscription['payment_method'] === 'paypal'
    && !empty($ai_subscription['paypal_subscription_id'])
    && $ai_subscription['status'] === 'cancelled');

$error_message = '';

// Handle reactivation confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reactivate'])) {
    // For cancelled PayPal subscriptions, redirect to checkout to create a new subscription
    if ($is_cancelled_paypal) {
        header('Location: ../../upgrade/ai/checkout/?method=paypal&billing=' . ($ai_subscription['billing_cycle'] ?? 'monthly') . '&change_method=1');
        exit;
    }

    try {
        // For PayPal subscriptions that are suspended (payment_failed), try to reactivate on PayPal's side
        $paypalReactivated = true;
        if ($ai_subscription['payment_method'] === 'paypal'
            && !empty($ai_subscription['paypal_subscription_id'])
            && $ai_subscription['status'] === 'payment_failed') {
            try {
                $paypalReactivated = activatePayPalSubscription(
                    $ai_subscription['paypal_subscription_id'],
                    'Reactivated by user from account settings'
                );
            } catch (Exception $e) {
                error_log("Failed to reactivate PayPal subscription: " . $e->getMessage());
                $paypalReactivated = false;
            }
        }

        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET status = 'active', auto_renew = 1, cancelled_at = NULL, updated_at = NOW()
            WHERE user_id = ? AND status IN ('cancelled', 'payment_failed')
            AND end_date > NOW()
        ");
        $stmt->execute([$user_id]);

        if ($stmt->rowCount() > 0) {
            // Send reactivation email
            try {
                send_ai_subscription_reactivated_email(
                    $ai_subscription['email'],
                    $ai_subscription['subscription_id'],
                    $ai_subscription['end_date'],
                    $ai_subscription['billing_cycle'] ?? 'monthly'
                );
            } catch (Exception $e) {
                error_log("Failed to send reactivation email: " . $e->getMessage());
            }

            $successMsg = 'Your subscription has been reactivated! AI features are now available.';
            if (!$paypalReactivated) {
                $successMsg .= ' Note: We could not automatically reactivate your PayPal subscription. You may need to update your payment method if the next renewal fails.';
            }
            $_SESSION['subscription_success'] = $successMsg;
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
                <h3>Payment Options</h3>

                <div class="change-payment-section">
                    <p class="change-payment-label">Billing cycle:</p>
                    <div class="billing-cycle-options">
                        <div class="billing-cycle-btn <?php echo $billing_cycle === 'monthly' ? 'current' : ''; ?>" data-billing="monthly">
                            <span class="billing-cycle-name">Monthly</span>
                            <span class="billing-cycle-price">$5/month</span>
                        </div>
                        <div class="billing-cycle-btn <?php echo $billing_cycle === 'yearly' ? 'current' : ''; ?>" data-billing="yearly">
                            <span class="billing-cycle-name">Yearly</span>
                            <span class="billing-cycle-price">$50/year</span>
                        </div>
                    </div>
                </div>

                <div class="change-payment-section">
                    <p class="change-payment-label">Payment provider:</p>
                    <div class="payment-provider-options">
                        <div class="payment-provider-btn <?php echo strtolower($ai_subscription['payment_method']) === 'stripe' ? 'current' : ''; ?>" data-method="stripe" data-name="Stripe">
                            <img src="../../images/Stripe-logo.svg" alt="Stripe">
                        </div>
                        <div class="payment-provider-btn <?php echo strtolower($ai_subscription['payment_method']) === 'paypal' ? 'current' : ''; ?>" data-method="paypal" data-name="PayPal">
                            <img src="../../images/PayPal-logo.svg" alt="PayPal">
                        </div>
                        <div class="payment-provider-btn <?php echo strtolower($ai_subscription['payment_method']) === 'square' ? 'current' : ''; ?>" data-method="square" data-name="Square">
                            <img src="../../images/Square-logo.svg" alt="Square">
                        </div>
                    </div>
                </div>
            </div>

            <div class="confirm-actions">
                <form method="post" id="reactivate-form">
                    <input type="hidden" name="confirm_reactivate" value="1">
                    <button type="submit" id="reactivate-btn" class="btn btn-purple">Reactivate with <?php echo $payment_method; ?></button>
                </form>
                <a href="ai-subscription.php" class="btn btn-outline">Go Back</a>
            </div>

            <script>
            (function() {
                const originalMethod = '<?php echo strtolower($ai_subscription['payment_method']); ?>';
                const originalBilling = '<?php echo $billing_cycle; ?>';
                let selectedMethod = originalMethod;
                let selectedBilling = originalBilling;

                const methodNames = { stripe: 'Stripe', paypal: 'PayPal', square: 'Square' };
                const reactivateBtn = document.getElementById('reactivate-btn');
                const reactivateForm = document.getElementById('reactivate-form');

                function updateButton() {
                    const methodName = methodNames[selectedMethod] || 'Unknown';
                    const billingName = selectedBilling === 'yearly' ? 'Yearly' : 'Monthly';
                    const hasChanges = selectedMethod !== originalMethod || selectedBilling !== originalBilling;

                    if (hasChanges) {
                        reactivateBtn.textContent = `Update to ${methodName} (${billingName})`;
                        reactivateBtn.type = 'button';
                        reactivateBtn.onclick = function() {
                            window.location.href = `../../upgrade/ai/checkout/?method=${selectedMethod}&billing=${selectedBilling}&change_method=1`;
                        };
                    } else {
                        reactivateBtn.textContent = `Reactivate with ${methodName}`;
                        reactivateBtn.type = 'submit';
                        reactivateBtn.onclick = null;
                    }
                }

                // Billing cycle selection
                document.querySelectorAll('.billing-cycle-btn').forEach(btn => {
                    btn.style.cursor = 'pointer';
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.billing-cycle-btn').forEach(b => b.classList.remove('current'));
                        this.classList.add('current');
                        selectedBilling = this.dataset.billing;
                        updateButton();
                    });
                });

                // Payment provider selection
                document.querySelectorAll('.payment-provider-btn').forEach(btn => {
                    btn.style.cursor = 'pointer';
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.payment-provider-btn').forEach(b => b.classList.remove('current'));
                        this.classList.add('current');
                        selectedMethod = this.dataset.method;
                        updateButton();
                    });
                });
            })();
            </script>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>
