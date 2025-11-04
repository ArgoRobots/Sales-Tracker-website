<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Load database connection and license functions
require_once '../../db_connect.php';
require_once '../../license_functions.php';
require_once '../../email_sender.php';

// Get raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check for required data
if (!$data || !isset($data['payment_intent_id']) || !isset($data['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required payment information'
    ]);
    exit;
}

// Initialize response
$response = [
    'success' => false,
    'message' => 'Failed to process payment'
];

try {
    $db = get_db_connection();

    // Check if this transaction has already been processed
    $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE transaction_id = ? LIMIT 1');
    $stmt->bind_param('s', $data['payment_intent_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Transaction already processed, return existing license key
        $response = [
            'success' => true,
            'license_key' => $row['license_key'],
            'transaction_id' => $data['payment_intent_id'],
            'order_id' => $data['payment_method_id'] ?? '',
            'message' => 'Payment already processed'
        ];
        $stmt->close();
    } else {
        $stmt->close();

        // Validate payment status
        if (!isset($data['status']) || $data['status'] !== 'succeeded') {
            $response = [
                'success' => false,
                'message' => 'Payment status is not completed'
            ];
        }
        // Validate payment amount
        else if (!isset($data['amount']) || floatval($data['amount']) < 20.00) {
            $response = [
                'success' => false,
                'message' => 'Invalid payment amount'
            ];
        }
        // Process the payment and get/update license key
        else {
            // Check if we already created a license key for this payment intent
            $stmt = $db->prepare('SELECT license_key FROM license_keys 
                                 WHERE payment_intent = ?
                                 LIMIT 1');
            $stmt->bind_param('s', $data['payment_intent_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $license_key = $row['license_key'];
                $stmt->close();
            } else {
                $stmt->close();
                // Create a new license key if we didn't pre-generate one
                $license_key = create_license_key($data['email']);
            }

            if ($license_key) {
                // Update the license key with transaction details
                $stmt = $db->prepare('UPDATE license_keys SET 
                    transaction_id = ?, 
                    order_id = ?, 
                    payment_method = ?,
                    activated = 1,
                    activation_date = CURRENT_TIMESTAMP
                    WHERE license_key = ?');

                $payment_method = 'Stripe';
                $order_id = $data['payment_method_id'] ?? '';
                $stmt->bind_param(
                    'ssss',
                    $data['payment_intent_id'],
                    $order_id,
                    $payment_method,
                    $license_key
                );
                $stmt->execute();
                $stmt->close();

                // Send license key via email
                $email_sent = send_license_email($data['email'], $license_key);

                $response = [
                    'success' => true,
                    'license_key' => $license_key,
                    'transaction_id' => $data['payment_intent_id'],
                    'order_id' => $order_id,
                    'email_sent' => $email_sent,
                    'message' => 'Payment processed successfully'
                ];

                // Log the transaction in a separate table for audit purposes
                $stmt = $db->prepare('INSERT INTO payment_transactions 
                    (transaction_id, order_id, email, amount, currency, payment_method, status, license_key, created_at) 
                    VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, NOW())');

                $currency = $data['currency'] ?? 'CAD';
                $stmt->bind_param(
                    'ssssssss',
                    $data['payment_intent_id'],
                    $order_id,
                    $data['email'],
                    $data['amount'],
                    $currency,
                    $payment_method,
                    $data['status'],
                    $license_key
                );
                $stmt->execute();
                $stmt->close();
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to generate license key'
                ];
            }
        }
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ];

    // Log the error
    error_log('Stripe payment processing error: ' . $e->getMessage());
}

// Send JSON response
echo json_encode($response);