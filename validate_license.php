<?php
// Set headers for API response
header('Content-Type: application/json');

require_once 'license_functions.php';
require_once 'db_connect.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Invalid request method'
];

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the license key from the request
    $data = json_decode(file_get_contents('php://input'), true);

    // Check for AI subscription validation
    if (isset($data['subscription_id'])) {
        $subscription_id = trim($data['subscription_id']);
        $response = validate_ai_subscription($subscription_id);
    }
    // Check for Premium license key validation
    elseif (isset($data['license_key'])) {
        $license_key = trim($data['license_key']);

        // Verify the license key
        if (verify_license_key($license_key)) {
            // Get license details to check if it's already activated
            $license_details = get_license_details($license_key);

            if ($license_details['activated']) {
                $response = [
                    'success' => true,
                    'activated' => true,
                    'type' => 'premium',
                    'message' => 'License key is valid and already activated.',
                    'activation_date' => $license_details['activation_date']
                ];
            } else {
                // Activate the license
                $ip_address = $_SERVER['REMOTE_ADDR'];
                if (activate_license_key($license_key, $ip_address)) {
                    $response = [
                        'success' => true,
                        'activated' => true,
                        'type' => 'premium',
                        'message' => 'License key activated successfully.',
                        'activation_date' => date('Y-m-d H:i:s')
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Failed to activate license key.'
                    ];
                }
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid license key.'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'License key or subscription ID is required.'
        ];
    }
}

/**
 * Validate an AI subscription
 * @param string $subscription_id The subscription ID to validate
 * @return array Response array with validation result
 */
function validate_ai_subscription($subscription_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT subscription_id, user_id, email, billing_cycle, status,
                   start_date, end_date, created_at
            FROM ai_subscriptions
            WHERE subscription_id = ?
        ");
        $stmt->execute([$subscription_id]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subscription) {
            return [
                'success' => false,
                'message' => 'Invalid subscription ID.'
            ];
        }

        // Check subscription status
        $now = new DateTime();
        $end_date = new DateTime($subscription['end_date']);

        if ($subscription['status'] === 'active' && $end_date > $now) {
            return [
                'success' => true,
                'type' => 'ai_subscription',
                'status' => 'active',
                'message' => 'AI subscription is valid and active.',
                'subscription_id' => $subscription['subscription_id'],
                'billing_cycle' => $subscription['billing_cycle'],
                'end_date' => $subscription['end_date'],
                'days_remaining' => $now->diff($end_date)->days
            ];
        } elseif ($subscription['status'] === 'cancelled' && $end_date > $now) {
            return [
                'success' => true,
                'type' => 'ai_subscription',
                'status' => 'cancelled',
                'message' => 'AI subscription is cancelled but still active until end of billing period.',
                'subscription_id' => $subscription['subscription_id'],
                'billing_cycle' => $subscription['billing_cycle'],
                'end_date' => $subscription['end_date'],
                'days_remaining' => $now->diff($end_date)->days
            ];
        } else {
            // Subscription expired - update status if needed
            if ($subscription['status'] !== 'expired') {
                $stmt = $pdo->prepare("UPDATE ai_subscriptions SET status = 'expired' WHERE subscription_id = ?");
                $stmt->execute([$subscription_id]);
            }

            return [
                'success' => false,
                'type' => 'ai_subscription',
                'status' => 'expired',
                'message' => 'AI subscription has expired.',
                'subscription_id' => $subscription['subscription_id'],
                'end_date' => $subscription['end_date']
            ];
        }
    } catch (PDOException $e) {
        error_log("AI Subscription validation error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error validating subscription. Please try again.'
        ];
    }
}

// Return the JSON response
echo json_encode($response);