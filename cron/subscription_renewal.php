<?php
/**
 * AI Subscription Renewal Cron Job
 *
 * This script should be run daily via cron to check for and process subscription renewals.
 * Example cron entry (run daily at 2 AM):
 * 0 2 * * * /usr/bin/php /path/to/subscription_renewal.php
 *
 * Alternatively, can be called via web with a secret key for testing:
 * subscription_renewal.php?key=YOUR_CRON_SECRET
 */

// Prevent timeout for long-running process
set_time_limit(300);

// Load environment variables before authentication check
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Only allow CLI or authenticated web requests
$isCli = php_sapi_name() === 'cli';
$isAuthenticated = isset($_GET['key']) && $_GET['key'] === ($_ENV['CRON_SECRET'] ?? '');

if (!$isCli && !$isAuthenticated) {
    http_response_code(403);
    die('Access denied');
}

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../email_sender.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Configure logging
function logMessage($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message\n";

    // Log to file
    $logFile = __DIR__ . '/logs/subscription_renewal_' . date('Y-m-d') . '.log';
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Also output to CLI
    if (php_sapi_name() === 'cli') {
        echo $logEntry;
    }
}

logMessage('Starting subscription renewal check...');

// Get environment configuration
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

// Initialize Stripe
$stripeSecretKey = $isProduction
    ? $_ENV['STRIPE_LIVE_SECRET_KEY']
    : $_ENV['STRIPE_SANDBOX_SECRET_KEY'];
\Stripe\Stripe::setApiKey($stripeSecretKey);

// Square configuration
$squareAccessToken = $isProduction
    ? $_ENV['SQUARE_LIVE_ACCESS_TOKEN']
    : $_ENV['SQUARE_SANDBOX_ACCESS_TOKEN'];
$squareEnvironment = $isProduction ? 'production' : 'sandbox';

// Find subscriptions due for renewal (within next 24 hours or already past due)
try {
    $stmt = $pdo->prepare("
        SELECT
            s.*,
            u.username,
            u.email as user_email
        FROM ai_subscriptions s
        JOIN community_users u ON s.user_id = u.id
        WHERE s.status = 'active'
        AND s.end_date <= DATE_ADD(NOW(), INTERVAL 1 DAY)
        AND s.auto_renew = 1
        ORDER BY s.end_date ASC
    ");
    $stmt->execute();
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    logMessage("Found " . count($subscriptions) . " subscriptions due for renewal");

} catch (PDOException $e) {
    logMessage("Database error fetching subscriptions: " . $e->getMessage(), 'ERROR');
    exit(1);
}

$successCount = 0;
$failedCount = 0;
$skippedCount = 0;

foreach ($subscriptions as $subscription) {
    $subscriptionId = $subscription['subscription_id'];
    $userId = $subscription['user_id'];
    $email = $subscription['email'];
    $billing = $subscription['billing_cycle'];
    $paymentMethod = $subscription['payment_method'];
    $paymentToken = $subscription['payment_token'];
    $creditBalance = floatval($subscription['credit_balance'] ?? 0);

    logMessage("Processing renewal for subscription: $subscriptionId (User: $userId, Method: $paymentMethod, Credit: $$creditBalance)");

    // Calculate renewal amount
    $baseMonthly = 5.00;
    $baseYearly = 50.00;
    $amount = ($billing === 'yearly') ? $baseYearly : $baseMonthly;

    // Check if renewal can be covered by credit
    $useCredit = false;
    $creditUsed = 0;
    $amountToCharge = $amount;

    if ($creditBalance > 0) {
        if ($creditBalance >= $amount) {
            // Full renewal covered by credit - no charge needed
            $useCredit = true;
            $creditUsed = $amount;
            $amountToCharge = 0;
            logMessage("Renewal for $subscriptionId covered by credit balance ($$creditBalance)");
        } else {
            // Partial credit - charge the difference
            $creditUsed = $creditBalance;
            $amountToCharge = $amount - $creditBalance;
            logMessage("Partial credit ($$creditBalance) applied for $subscriptionId, charging $$amountToCharge");
        }
    }

    // Skip payment processing if fully covered by credit
    if ($amountToCharge <= 0 && $useCredit) {
        try {
            // Update subscription dates
            $newEndDate = calculateNewEndDate($subscription['end_date'], $billing);
            $newCreditBalance = $creditBalance - $creditUsed;

            $stmt = $pdo->prepare("
                UPDATE ai_subscriptions
                SET end_date = ?,
                    credit_balance = ?,
                    updated_at = NOW()
                WHERE subscription_id = ?
            ");
            $stmt->execute([$newEndDate, $newCreditBalance, $subscriptionId]);

            // Log the credit-based payment (no actual charge)
            $stmt = $pdo->prepare("
                INSERT INTO ai_subscription_payments (
                    subscription_id, amount, currency, payment_method,
                    transaction_id, status, payment_type, created_at
                ) VALUES (?, 0, 'CAD', ?, ?, 'completed', 'credit', NOW())
            ");
            $creditTransactionId = 'CREDIT_RENEWAL_' . strtoupper(bin2hex(random_bytes(8)));
            $stmt->execute([$subscriptionId, $paymentMethod, $creditTransactionId]);

            // DO NOT send receipt email for $0 credit-based renewals
            logMessage("Successfully renewed $subscriptionId using credit - new end date: $newEndDate, remaining credit: $$newCreditBalance");
            $successCount++;
            continue;
        } catch (Exception $e) {
            logMessage("Failed to process credit-based renewal for $subscriptionId: " . $e->getMessage(), 'ERROR');
            $failedCount++;
            continue;
        }
    }

    // Skip if no payment token stored and we need to charge
    if (empty($paymentToken) && $amountToCharge > 0) {
        logMessage("No payment token stored for $subscriptionId - skipping", 'WARNING');
        $skippedCount++;
        continue;
    }

    // Process payment based on method
    $paymentResult = null;
    $transactionId = null;

    try {
        switch ($paymentMethod) {
            case 'stripe':
                $paymentResult = processStripeRenewal($paymentToken, $amountToCharge, $subscriptionId, $email);
                break;
            case 'square':
                $paymentResult = processSquareRenewal($paymentToken, $amountToCharge, $subscriptionId, $email, $squareAccessToken, $squareEnvironment);
                break;
            case 'paypal':
                // Check if this is a PayPal Subscription (managed by PayPal)
                if (!empty($subscription['paypal_subscription_id'])) {
                    // PayPal Subscriptions are automatically renewed by PayPal
                    // Renewals are handled via PayPal webhooks
                    logMessage("PayPal subscription {$subscription['paypal_subscription_id']} - managed by PayPal webhooks", 'INFO');
                    $skippedCount++;
                    continue 2;
                }
                // One-time PayPal payment - no recurring billing available
                logMessage("PayPal one-time payment - no recurring billing token available", 'WARNING');
                $skippedCount++;
                continue 2;
            default:
                logMessage("Unknown payment method: $paymentMethod", 'WARNING');
                $skippedCount++;
                continue 2;
        }

        if ($paymentResult['success']) {
            $transactionId = $paymentResult['transaction_id'];

            // Update subscription dates and credit balance
            $newEndDate = calculateNewEndDate($subscription['end_date'], $billing);
            $newCreditBalance = $creditBalance - $creditUsed; // Deduct any used credit

            $stmt = $pdo->prepare("
                UPDATE ai_subscriptions
                SET end_date = ?,
                    credit_balance = ?,
                    updated_at = NOW()
                WHERE subscription_id = ?
            ");
            $stmt->execute([$newEndDate, $newCreditBalance, $subscriptionId]);

            // Log payment (log the actual amount charged, not the full renewal amount)
            $stmt = $pdo->prepare("
                INSERT INTO ai_subscription_payments (
                    subscription_id, amount, currency, payment_method,
                    transaction_id, status, payment_type, created_at
                ) VALUES (?, ?, 'CAD', ?, ?, 'completed', 'renewal', NOW())
            ");
            $stmt->execute([$subscriptionId, $amountToCharge, $paymentMethod, $transactionId]);

            // Send receipt email (only for actual charges, not credit-covered renewals)
            if ($amountToCharge > 0) {
                sendRenewalReceiptEmail(
                    $email,
                    $subscriptionId,
                    $billing,
                    $amountToCharge,
                    $newEndDate,
                    $transactionId,
                    $paymentMethod
                );
            }

            $creditMessage = $creditUsed > 0 ? " ($$creditUsed credit applied)" : "";
            logMessage("Successfully renewed $subscriptionId - new end date: $newEndDate, charged: $$amountToCharge$creditMessage");
            $successCount++;

        } else {
            throw new Exception($paymentResult['error'] ?? 'Payment failed');
        }

    } catch (Exception $e) {
        logMessage("Failed to renew $subscriptionId: " . $e->getMessage(), 'ERROR');

        // Log failed attempt
        $stmt = $pdo->prepare("
            INSERT INTO ai_subscription_payments (
                subscription_id, amount, currency, payment_method,
                transaction_id, status, payment_type, error_message, created_at
            ) VALUES (?, ?, 'CAD', ?, NULL, 'failed', 'renewal', ?, NOW())
        ");
        $stmt->execute([$subscriptionId, $amount, $paymentMethod, $e->getMessage()]);

        // Send payment failed notification
        sendPaymentFailedEmail($email, $subscriptionId, $e->getMessage());

        // If multiple failures, consider suspending
        $failureCount = getRecentFailureCount($pdo, $subscriptionId);
        if ($failureCount >= 3) {
            $stmt = $pdo->prepare("
                UPDATE ai_subscriptions
                SET status = 'payment_failed',
                    updated_at = NOW()
                WHERE subscription_id = ?
            ");
            $stmt->execute([$subscriptionId]);
            logMessage("Subscription $subscriptionId suspended after $failureCount failures", 'WARNING');
        }

        $failedCount++;
    }
}

logMessage("Renewal processing complete. Success: $successCount, Failed: $failedCount, Skipped: $skippedCount");

// Also check for subscriptions that should be marked as expired
try {
    $stmt = $pdo->prepare("
        UPDATE ai_subscriptions
        SET status = 'expired',
            updated_at = NOW()
        WHERE status = 'active'
        AND auto_renew = 0
        AND end_date < NOW()
    ");
    $stmt->execute();
    $expiredCount = $stmt->rowCount();

    if ($expiredCount > 0) {
        logMessage("Marked $expiredCount subscriptions as expired (auto-renew disabled)");
    }
} catch (PDOException $e) {
    logMessage("Error marking expired subscriptions: " . $e->getMessage(), 'ERROR');
}

/**
 * Process Stripe renewal payment
 */
function processStripeRenewal($paymentMethodId, $amount, $subscriptionId, $email) {
    try {
        // Create payment intent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => intval($amount * 100), // Stripe uses cents
            'currency' => 'cad',
            'payment_method' => $paymentMethodId,
            'confirm' => true,
            'off_session' => true,
            'description' => "AI Subscription Renewal - $subscriptionId",
            'receipt_email' => $email,
            'metadata' => [
                'subscription_id' => $subscriptionId,
                'type' => 'renewal'
            ]
        ]);

        if ($paymentIntent->status === 'succeeded') {
            return [
                'success' => true,
                'transaction_id' => $paymentIntent->id
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Payment not completed: ' . $paymentIntent->status
            ];
        }
    } catch (\Stripe\Exception\CardException $e) {
        return [
            'success' => false,
            'error' => 'Card declined: ' . $e->getMessage()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Process Square renewal payment
 */
function processSquareRenewal($cardId, $amount, $subscriptionId, $email, $accessToken, $environment) {
    try {
        $client = new \Square\SquareClient([
            'accessToken' => $accessToken,
            'environment' => $environment
        ]);

        $locationId = ($environment === 'production')
            ? $_ENV['SQUARE_LIVE_LOCATION_ID']
            : $_ENV['SQUARE_SANDBOX_LOCATION_ID'];

        $amountMoney = new \Square\Models\Money();
        $amountMoney->setAmount(intval($amount * 100)); // Square uses cents
        $amountMoney->setCurrency('CAD');

        $createPaymentRequest = new \Square\Models\CreatePaymentRequest(
            $cardId,
            uniqid('renewal_', true)
        );
        $createPaymentRequest->setAmountMoney($amountMoney);
        $createPaymentRequest->setLocationId($locationId);
        $createPaymentRequest->setNote("AI Subscription Renewal - $subscriptionId");
        $createPaymentRequest->setAutocomplete(true);

        $response = $client->getPaymentsApi()->createPayment($createPaymentRequest);

        if ($response->isSuccess()) {
            $payment = $response->getResult()->getPayment();
            return [
                'success' => true,
                'transaction_id' => $payment->getId()
            ];
        } else {
            $errors = $response->getErrors();
            $errorMessage = $errors[0]->getDetail() ?? 'Unknown error';
            return [
                'success' => false,
                'error' => $errorMessage
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Calculate new subscription end date
 */
function calculateNewEndDate($currentEndDate, $billing) {
    $endDateTime = new DateTime($currentEndDate);

    if ($billing === 'yearly') {
        $endDateTime->add(new DateInterval('P1Y'));
    } else {
        $endDateTime->add(new DateInterval('P1M'));
    }

    return $endDateTime->format('Y-m-d H:i:s');
}

/**
 * Get count of recent payment failures
 */
function getRecentFailureCount($pdo, $subscriptionId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM ai_subscription_payments
        WHERE subscription_id = ?
        AND status = 'failed'
        AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$subscriptionId]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Send renewal receipt email
 */
function sendRenewalReceiptEmail($email, $subscriptionId, $billing, $amount, $nextRenewal, $transactionId, $paymentMethod) {
    $css = file_get_contents(__DIR__ . '/../email.css');
    $subject = "Payment Receipt - Argo AI Subscription";

    $billingText = $billing === 'yearly' ? 'yearly' : 'monthly';
    $renewalDate = date('F j, Y', strtotime($nextRenewal));
    $paymentDate = date('F j, Y');
    $paymentMethodText = ucfirst($paymentMethod);

    $email_html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Payment Receipt</title>
    <style>
        {$css}
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
        </div>

        <div class="content">
            <h1>Payment Receipt</h1>
            <p>Thank you for your continued subscription to Argo AI!</p>

            <div class="subscription-box" style="background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb;">
                <h3 style="margin-top: 0;">Payment Details</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Date</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{$paymentDate}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Description</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">AI Subscription ({$billingText})</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Amount</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">\${$amount} CAD</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Payment Method</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{$paymentMethodText}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Transaction ID</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 12px;">{$transactionId}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong>Next Renewal</strong></td>
                        <td style="padding: 8px 0; text-align: right;">{$renewalDate}</td>
                    </tr>
                </table>
            </div>

            <p>Your subscription has been renewed and will continue until {$renewalDate}.</p>

            <p>You can manage your subscription anytime from your <a href="https://argorobots.com/community/users/ai-subscription.php">account settings</a>.</p>

            <div class="footer" style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
                <p>Subscription ID: {$subscriptionId}</p>
                <p>Thank you for using Argo Books!</p>
                <p><a href="https://argorobots.com">argorobots.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Books <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($email, $subject, $email_html, implode("\r\n", $headers));
}

/**
 * Send payment failed notification
 */
function sendPaymentFailedEmail($email, $subscriptionId, $errorMessage) {
    $css = file_get_contents(__DIR__ . '/../email.css');
    $subject = "Payment Failed - Argo AI Subscription";

    $email_html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Payment Failed</title>
    <style>
        {$css}
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
            <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
        </div>

        <div class="content">
            <h1>Payment Failed</h1>
            <p>We were unable to process your subscription renewal payment.</p>

            <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; color: #991b1b;"><strong>Subscription ID:</strong> {$subscriptionId}</p>
            </div>

            <p><strong>What to do next:</strong></p>
            <ul>
                <li>Check that your payment method is up to date</li>
                <li>Ensure there are sufficient funds available</li>
                <li>Update your payment information in your account settings</li>
            </ul>

            <p>If the payment continues to fail, your subscription may be suspended. Please update your payment method to avoid interruption of service.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="https://argorobots.com/community/users/ai-subscription.php" style="display: inline-block; background: #8b5cf6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">Update Payment Method</a>
            </div>

            <p>If you need assistance, please <a href="https://argorobots.com/contact-us/">contact our support team</a>.</p>

            <div class="footer" style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
                <p>Argo Books &copy; 2025. All rights reserved.</p>
                <p><a href="https://argorobots.com">argorobots.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Books <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($email, $subject, $email_html, implode("\r\n", $headers));
}
