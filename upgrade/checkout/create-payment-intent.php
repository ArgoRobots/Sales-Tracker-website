<?php
require_once '../../vendor/autoload.php';
require_once '../../db_connect.php';
require_once '../../license_functions.php';

// Set headers for JSON response
header('Content-Type: application/json');

try {
    // Get raw POST data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Validate JSON data
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
        exit;
    }

    // Check for required data
    if (!$data || !isset($data['amount']) || !isset($data['currency'])) {
        echo json_encode([
            'error' => 'Missing required payment information'
        ]);
        exit;
    }

    // Initialize Stripe API
    \Stripe\Stripe::setApiKey("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");

    // Pre-generate license key for the customer (we'll store it temporarily)
    $customer_email = isset($data['email']) ? $data['email'] : '';
    if (!empty($customer_email)) {
        // Check if a license key was already pre-generated for this email in the last hour
        // If so, we'll reuse it rather than creating multiple keys
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT license_key FROM license_keys 
                             WHERE email = :email 
                             AND created_at > datetime("now", "-1 hour")
                             AND activated = 0
                             AND transaction_id IS NULL
                             LIMIT 1');
        $stmt->bindValue(':email', $customer_email, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result) {
            $license_key = $result['license_key'];
        } else {
            // Create a new license key
            $license_key = create_license_key($customer_email);
        }
    }

    // Create a PaymentIntent
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $data['amount'],
        'currency' => strtolower($data['currency']),
        'payment_method_types' => ['card'],
        'metadata' => [
            'product' => 'Argo Sales Tracker - Lifetime Access',
            'email' => $data['email'] ?? '',
            'license_key' => $license_key ?? ''
        ],
        'receipt_email' => $data['email'] ?? null,
        'description' => 'Argo Sales Tracker - Lifetime Access'
    ]);

    // Store the payment intent ID in our database
    if (isset($license_key) && !empty($license_key)) {
        $db = get_db_connection();
        $stmt = $db->prepare('UPDATE license_keys SET 
                              payment_intent = :payment_intent
                              WHERE license_key = :license_key');
        $stmt->bindValue(':payment_intent', $payment_intent->id, SQLITE3_TEXT);
        $stmt->bindValue(':license_key', $license_key, SQLITE3_TEXT);
        $stmt->execute();
    }

    // Return the client secret
    echo json_encode([
        'client_secret' => $payment_intent->client_secret,
        'payment_intent_id' => $payment_intent->id
    ]);
} catch (Exception $e) {
    // Send proper JSON error response
    http_response_code(500);
    error_log('Payment processing error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Payment processing error: ' . $e->getMessage()
    ]);

    // Close database connection if it exists
    if (isset($db)) {
    }
}
