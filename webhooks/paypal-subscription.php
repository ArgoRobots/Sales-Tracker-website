<?php
/**
 * PayPal Subscription Webhook Handler
 *
 * This endpoint receives webhook notifications from PayPal for subscription events.
 * Configure this URL in your PayPal Developer Dashboard:
 * https://yourdomain.com/webhooks/paypal-subscription.php
 *
 * Required Events to Subscribe:
 * - BILLING.SUBSCRIPTION.ACTIVATED
 * - BILLING.SUBSCRIPTION.CANCELLED
 * - BILLING.SUBSCRIPTION.EXPIRED
 * - BILLING.SUBSCRIPTION.SUSPENDED
 * - BILLING.SUBSCRIPTION.PAYMENT.FAILED
 * - PAYMENT.SALE.COMPLETED
 * - PAYMENT.SALE.DENIED
 * - PAYMENT.SALE.REFUNDED
 */

// Load dependencies
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../email_sender.php';
require_once __DIR__ . '/paypal-helper.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Get the raw request body
$rawBody = file_get_contents('php://input');

if (empty($rawBody)) {
    http_response_code(400);
    exit('Empty request body');
}

// Parse the webhook payload
$event = json_decode($rawBody, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    exit('Invalid JSON');
}

// Get the webhook ID from environment
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';
$webhookId = $isProduction
    ? ($_ENV['PAYPAL_LIVE_WEBHOOK_ID'] ?? '')
    : ($_ENV['PAYPAL_SANDBOX_WEBHOOK_ID'] ?? '');

// Verify webhook signature (skip in development if webhook ID not configured)
if (!empty($webhookId)) {
    $headers = getallheaders();
    if (!verifyPayPalWebhookSignature($headers, $rawBody, $webhookId)) {
        logPayPalWebhookEvent($event['event_type'] ?? 'UNKNOWN', $event, 'SIGNATURE_VERIFICATION_FAILED');
        http_response_code(401);
        exit('Invalid signature');
    }
} else {
    // Log warning that signature verification is disabled
    error_log("WARNING: PayPal webhook signature verification is disabled. Set PAYPAL_*_WEBHOOK_ID in environment.");
}

// Extract event details
$eventType = $event['event_type'] ?? '';
$resource = $event['resource'] ?? [];

// Log the event
logPayPalWebhookEvent($eventType, $event, 'received');

try {
    switch ($eventType) {
        case 'BILLING.SUBSCRIPTION.ACTIVATED':
            handleSubscriptionActivated($resource);
            break;

        case 'BILLING.SUBSCRIPTION.CANCELLED':
            handleSubscriptionCancelled($resource);
            break;

        case 'BILLING.SUBSCRIPTION.EXPIRED':
            handleSubscriptionExpired($resource);
            break;

        case 'BILLING.SUBSCRIPTION.SUSPENDED':
            handleSubscriptionSuspended($resource);
            break;

        case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
            handlePaymentFailed($resource);
            break;

        case 'PAYMENT.SALE.COMPLETED':
            handlePaymentCompleted($resource);
            break;

        case 'PAYMENT.SALE.DENIED':
            handlePaymentDenied($resource);
            break;

        case 'PAYMENT.SALE.REFUNDED':
            handlePaymentRefunded($resource);
            break;

        default:
            // Log unhandled event types for debugging
            logPayPalWebhookEvent($eventType, $event, 'unhandled');
            break;
    }

    // Respond with success
    http_response_code(200);
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    error_log("PayPal webhook error: " . $e->getMessage());
    logPayPalWebhookEvent($eventType, $event, 'ERROR: ' . $e->getMessage());

    // Still respond with 200 to prevent PayPal from retrying
    // The error is logged for manual investigation
    http_response_code(200);
    echo json_encode(['status' => 'error', 'message' => 'Internal error logged']);
}

/**
 * Handle subscription activated event
 * This is called when a new subscription is created and activated
 */
function handleSubscriptionActivated($resource) {
    global $pdo;

    $paypalSubscriptionId = $resource['id'] ?? '';
    $status = $resource['status'] ?? '';
    $customId = $resource['custom_id'] ?? ''; // We set this as user_ID during creation

    if (empty($paypalSubscriptionId)) {
        throw new Exception("Missing subscription ID in activated event");
    }

    // Extract user_id from custom_id (format: user_123)
    $userId = null;
    if (preg_match('/^user_(\d+)$/', $customId, $matches)) {
        $userId = intval($matches[1]);
    }

    // Check if we already have this subscription in our database
    $stmt = $pdo->prepare("SELECT * FROM ai_subscriptions WHERE paypal_subscription_id = ?");
    $stmt->execute([$paypalSubscriptionId]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subscription) {
        // Subscription exists, update status to active
        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET status = 'active', auto_renew = 1, updated_at = NOW()
            WHERE paypal_subscription_id = ?
        ");
        $stmt->execute([$paypalSubscriptionId]);

        logPayPalWebhookEvent('BILLING.SUBSCRIPTION.ACTIVATED', $resource, 'subscription_reactivated');
    } else {
        // This is a new subscription created outside our normal flow
        // This shouldn't normally happen but handle it gracefully
        logPayPalWebhookEvent('BILLING.SUBSCRIPTION.ACTIVATED', $resource, 'new_subscription_not_in_db');
    }
}

/**
 * Handle subscription cancelled event
 */
function handleSubscriptionCancelled($resource) {
    global $pdo;

    $paypalSubscriptionId = $resource['id'] ?? '';

    if (empty($paypalSubscriptionId)) {
        throw new Exception("Missing subscription ID in cancelled event");
    }

    // Find subscription in our database
    $stmt = $pdo->prepare("SELECT * FROM ai_subscriptions WHERE paypal_subscription_id = ?");
    $stmt->execute([$paypalSubscriptionId]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subscription) {
        // Update subscription status to cancelled
        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET status = 'cancelled',
                auto_renew = 0,
                credit_balance = 0,
                cancelled_at = NOW(),
                updated_at = NOW()
            WHERE paypal_subscription_id = ?
        ");
        $stmt->execute([$paypalSubscriptionId]);

        // Send cancellation email
        try {
            send_ai_subscription_cancelled_email(
                $subscription['email'],
                $subscription['subscription_id'],
                $subscription['end_date']
            );
        } catch (Exception $e) {
            error_log("Failed to send cancellation email: " . $e->getMessage());
        }

        logPayPalWebhookEvent('BILLING.SUBSCRIPTION.CANCELLED', $resource, 'subscription_cancelled');
    } else {
        logPayPalWebhookEvent('BILLING.SUBSCRIPTION.CANCELLED', $resource, 'subscription_not_found');
    }
}

/**
 * Handle subscription expired event
 */
function handleSubscriptionExpired($resource) {
    global $pdo;

    $paypalSubscriptionId = $resource['id'] ?? '';

    if (empty($paypalSubscriptionId)) {
        throw new Exception("Missing subscription ID in expired event");
    }

    // Update subscription status
    $stmt = $pdo->prepare("
        UPDATE ai_subscriptions
        SET status = 'expired', auto_renew = 0, updated_at = NOW()
        WHERE paypal_subscription_id = ?
    ");
    $stmt->execute([$paypalSubscriptionId]);

    logPayPalWebhookEvent('BILLING.SUBSCRIPTION.EXPIRED', $resource, 'subscription_expired');
}

/**
 * Handle subscription suspended event (payment issues)
 */
function handleSubscriptionSuspended($resource) {
    global $pdo;

    $paypalSubscriptionId = $resource['id'] ?? '';

    if (empty($paypalSubscriptionId)) {
        throw new Exception("Missing subscription ID in suspended event");
    }

    // Find subscription
    $stmt = $pdo->prepare("SELECT * FROM ai_subscriptions WHERE paypal_subscription_id = ?");
    $stmt->execute([$paypalSubscriptionId]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subscription) {
        // Update subscription status to payment_failed
        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET status = 'payment_failed', updated_at = NOW()
            WHERE paypal_subscription_id = ?
        ");
        $stmt->execute([$paypalSubscriptionId]);

        // Send payment failed notification
        try {
            send_payment_failed_email(
                $subscription['email'],
                $subscription['subscription_id'],
                'Your PayPal subscription payment has failed. Please update your payment method.'
            );
        } catch (Exception $e) {
            error_log("Failed to send payment failed email: " . $e->getMessage());
        }

        logPayPalWebhookEvent('BILLING.SUBSCRIPTION.SUSPENDED', $resource, 'subscription_suspended');
    }
}

/**
 * Handle payment failed event
 */
function handlePaymentFailed($resource) {
    global $pdo;

    $billingAgreementId = $resource['billing_agreement_id'] ?? '';

    if (empty($billingAgreementId)) {
        // Try to get from parent resource
        $billingAgreementId = $resource['id'] ?? '';
    }

    // Find subscription
    $stmt = $pdo->prepare("SELECT * FROM ai_subscriptions WHERE paypal_subscription_id = ?");
    $stmt->execute([$billingAgreementId]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subscription) {
        // Log the failed payment
        $stmt = $pdo->prepare("
            INSERT INTO ai_subscription_payments (
                subscription_id, amount, currency, payment_method,
                transaction_id, status, payment_type, error_message, created_at
            ) VALUES (?, 0, 'CAD', 'paypal', NULL, 'failed', 'renewal', 'PayPal payment failed', NOW())
        ");
        $stmt->execute([$subscription['subscription_id']]);

        logPayPalWebhookEvent('BILLING.SUBSCRIPTION.PAYMENT.FAILED', $resource, 'payment_failed_logged');
    }
}

/**
 * Handle successful payment (renewal) event
 */
function handlePaymentCompleted($resource) {
    global $pdo;

    $billingAgreementId = $resource['billing_agreement_id'] ?? '';
    $transactionId = $resource['id'] ?? '';
    $amount = $resource['amount']['total'] ?? 0;
    $currency = $resource['amount']['currency'] ?? 'CAD';
    $state = $resource['state'] ?? '';

    if (empty($billingAgreementId) || $state !== 'completed') {
        logPayPalWebhookEvent('PAYMENT.SALE.COMPLETED', $resource, 'skipped_invalid_state');
        return;
    }

    // Find subscription
    $stmt = $pdo->prepare("SELECT * FROM ai_subscriptions WHERE paypal_subscription_id = ?");
    $stmt->execute([$billingAgreementId]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subscription) {
        logPayPalWebhookEvent('PAYMENT.SALE.COMPLETED', $resource, 'subscription_not_found');
        return;
    }

    // Check if this is the initial payment (transaction already exists) or a renewal
    $stmt = $pdo->prepare("SELECT id FROM ai_subscription_payments WHERE transaction_id = ?");
    $stmt->execute([$transactionId]);

    if ($stmt->fetch()) {
        // Transaction already processed
        logPayPalWebhookEvent('PAYMENT.SALE.COMPLETED', $resource, 'duplicate_transaction');
        return;
    }

    // Determine if this is a renewal (subscription already has payments)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM ai_subscription_payments WHERE subscription_id = ?");
    $stmt->execute([$subscription['subscription_id']]);
    $paymentCount = $stmt->fetch()['count'] ?? 0;

    $paymentType = $paymentCount > 0 ? 'renewal' : 'initial';

    // Log the payment
    $stmt = $pdo->prepare("
        INSERT INTO ai_subscription_payments (
            subscription_id, amount, currency, payment_method,
            transaction_id, status, payment_type, created_at
        ) VALUES (?, ?, ?, 'paypal', ?, 'completed', ?, NOW())
    ");
    $stmt->execute([
        $subscription['subscription_id'],
        $amount,
        $currency,
        $transactionId,
        $paymentType
    ]);

    // Update subscription end date if this is a renewal
    if ($paymentType === 'renewal') {
        $billing = $subscription['billing_cycle'];
        $newEndDate = calculateNewEndDate($subscription['end_date'], $billing);

        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET end_date = ?,
                status = 'active',
                updated_at = NOW()
            WHERE subscription_id = ?
        ");
        $stmt->execute([$newEndDate, $subscription['subscription_id']]);

        // Send renewal receipt email
        try {
            send_ai_subscription_receipt(
                $subscription['email'],
                $subscription['subscription_id'],
                $billing,
                $amount,
                $newEndDate,
                $transactionId,
                'paypal'
            );
        } catch (Exception $e) {
            error_log("Failed to send renewal receipt: " . $e->getMessage());
        }

        logPayPalWebhookEvent('PAYMENT.SALE.COMPLETED', $resource, 'renewal_processed');
    } else {
        logPayPalWebhookEvent('PAYMENT.SALE.COMPLETED', $resource, 'initial_payment_logged');
    }
}

/**
 * Handle payment denied event
 */
function handlePaymentDenied($resource) {
    global $pdo;

    $billingAgreementId = $resource['billing_agreement_id'] ?? '';
    $transactionId = $resource['id'] ?? '';

    if (empty($billingAgreementId)) {
        return;
    }

    // Find subscription
    $stmt = $pdo->prepare("SELECT * FROM ai_subscriptions WHERE paypal_subscription_id = ?");
    $stmt->execute([$billingAgreementId]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subscription) {
        // Log the failed payment
        $stmt = $pdo->prepare("
            INSERT INTO ai_subscription_payments (
                subscription_id, amount, currency, payment_method,
                transaction_id, status, payment_type, error_message, created_at
            ) VALUES (?, 0, 'CAD', 'paypal', ?, 'failed', 'renewal', 'Payment denied by PayPal', NOW())
        ");
        $stmt->execute([$subscription['subscription_id'], $transactionId]);

        // Send notification
        try {
            send_payment_failed_email(
                $subscription['email'],
                $subscription['subscription_id'],
                'Your PayPal payment was denied. Please update your payment method.'
            );
        } catch (Exception $e) {
            error_log("Failed to send payment denied email: " . $e->getMessage());
        }

        logPayPalWebhookEvent('PAYMENT.SALE.DENIED', $resource, 'payment_denied_logged');
    }
}

/**
 * Handle payment refunded event
 */
function handlePaymentRefunded($resource) {
    global $pdo;

    $saleId = $resource['sale_id'] ?? '';
    $refundId = $resource['id'] ?? '';
    $amount = $resource['amount']['total'] ?? 0;

    if (empty($saleId)) {
        return;
    }

    // Find the original payment
    $stmt = $pdo->prepare("SELECT * FROM ai_subscription_payments WHERE transaction_id = ?");
    $stmt->execute([$saleId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
        // Log the refund
        $stmt = $pdo->prepare("
            INSERT INTO ai_subscription_payments (
                subscription_id, amount, currency, payment_method,
                transaction_id, status, payment_type, created_at
            ) VALUES (?, ?, 'CAD', 'paypal', ?, 'refunded', 'renewal', NOW())
        ");
        $stmt->execute([$payment['subscription_id'], -$amount, $refundId]);

        logPayPalWebhookEvent('PAYMENT.SALE.REFUNDED', $resource, 'refund_logged');
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
