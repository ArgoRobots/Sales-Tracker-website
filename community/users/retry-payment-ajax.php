<?php
/**
 * AJAX endpoint to retry payment for failed subscriptions
 * Returns JSON response with success/error status
 */
session_start();
header('Content-Type: application/json');

require_once '../../db_connect.php';
require_once '../../email_sender.php';
require_once '../community_functions.php';
require_once 'user_functions.php';
require_once '../../webhooks/paypal-helper.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get subscription info
$ai_subscription = get_user_ai_subscription($user_id);

// Validate subscription state
if (!$ai_subscription) {
    echo json_encode(['success' => false, 'error' => 'No subscription found']);
    exit;
}

if ($ai_subscription['status'] !== 'payment_failed') {
    echo json_encode(['success' => false, 'error' => 'Subscription is not in a failed payment state']);
    exit;
}

$payment_method = strtolower($ai_subscription['payment_method'] ?? '');
$billing_cycle = $ai_subscription['billing_cycle'] ?? 'monthly';

try {
    $reactivated = false;
    $message = '';

    // Handle PayPal subscriptions
    if ($payment_method === 'paypal' && !empty($ai_subscription['paypal_subscription_id'])) {
        // Try to reactivate the suspended PayPal subscription
        try {
            $reactivated = activatePayPalSubscription(
                $ai_subscription['paypal_subscription_id'],
                'Reactivated by user - retry payment'
            );

            if ($reactivated) {
                $message = 'Your PayPal subscription has been reactivated. Payment will be processed by PayPal.';
            } else {
                // PayPal reactivation failed - subscription may be cancelled not suspended
                echo json_encode([
                    'success' => false,
                    'error' => 'Unable to reactivate your PayPal subscription. The subscription may have been cancelled. Please set up a new subscription.',
                    'action' => 'new_subscription',
                    'redirect' => "../../upgrade/ai/checkout/?method=paypal&billing={$billing_cycle}&change_method=1"
                ]);
                exit;
            }
        } catch (Exception $e) {
            error_log("PayPal reactivation failed: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Failed to reactivate PayPal subscription. Please try setting up a new subscription.',
                'action' => 'new_subscription',
                'redirect' => "../../upgrade/ai/checkout/?method=paypal&billing={$billing_cycle}&change_method=1"
            ]);
            exit;
        }
    }
    // Handle Stripe subscriptions
    else if ($payment_method === 'stripe' && !empty($ai_subscription['stripe_subscription_id'])) {
        // For Stripe, we can try to create a new payment intent
        // but typically the user needs to update their card
        echo json_encode([
            'success' => false,
            'error' => 'Your card was declined. Please update your payment method to continue.',
            'action' => 'update_payment',
            'redirect' => "reactivate-subscription.php"
        ]);
        exit;
    }
    // Handle Square subscriptions
    else if ($payment_method === 'square') {
        echo json_encode([
            'success' => false,
            'error' => 'Your payment method was declined. Please update your payment method to continue.',
            'action' => 'update_payment',
            'redirect' => "reactivate-subscription.php"
        ]);
        exit;
    }
    // Unknown payment method
    else {
        echo json_encode([
            'success' => false,
            'error' => 'Unable to retry payment. Please update your payment method.',
            'action' => 'update_payment',
            'redirect' => "reactivate-subscription.php"
        ]);
        exit;
    }

    // If we got here, reactivation was successful
    if ($reactivated) {
        // Calculate new end date based on billing cycle
        $interval = ($billing_cycle === 'yearly') ? '+1 year' : '+1 month';
        $new_end_date = date('Y-m-d H:i:s', strtotime($interval));

        // Update the subscription status in database and extend the end date
        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET status = 'active', auto_renew = 1, end_date = ?, updated_at = NOW()
            WHERE user_id = ? AND status = 'payment_failed'
        ");
        $stmt->execute([$new_end_date, $user_id]);

        if ($stmt->rowCount() > 0) {
            // Send reactivation email with new end date
            try {
                send_ai_subscription_reactivated_email(
                    $ai_subscription['email'],
                    $ai_subscription['subscription_id'],
                    $new_end_date,
                    $billing_cycle
                );
            } catch (Exception $e) {
                error_log("Failed to send reactivation email: " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => $message ?: 'Your subscription has been reactivated! AI features are now available.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to update subscription status. Please contact support.'
            ]);
        }
    }
} catch (PDOException $e) {
    error_log("Database error in retry-payment-ajax: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'A database error occurred. Please try again or contact support.'
    ]);
}
