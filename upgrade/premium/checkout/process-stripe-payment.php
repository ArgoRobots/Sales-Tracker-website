<?php
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Load payment helper
require_once 'payment-helper.php';

// Get user_id if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

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
    // Process the payment
    else {
        $response = process_payment_completion([
            'email' => $data['email'],
            'transaction_id' => $data['payment_intent_id'],
            'order_id' => $data['payment_method_id'] ?? '',
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'CAD',
            'payment_method' => 'Stripe',
            'status' => $data['status'],
            'user_id' => $user_id,
            'payment_intent_id' => $data['payment_intent_id']
        ]);
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ];
    error_log('Stripe payment processing error: ' . $e->getMessage());
}

// Send JSON response
echo json_encode($response);
