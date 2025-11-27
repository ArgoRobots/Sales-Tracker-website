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
if (!$data || !isset($data['source_id']) || !isset($data['email'])) {
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
    // Get Square API credentials based on environment
    $is_production = $_ENV['APP_ENV'] === 'production';
    $square_access_token = $is_production
        ? $_ENV['SQUARE_LIVE_ACCESS_TOKEN']
        : $_ENV['SQUARE_SANDBOX_ACCESS_TOKEN'];
    $square_location_id = $is_production
        ? $_ENV['SQUARE_LIVE_LOCATION_ID']
        : $_ENV['SQUARE_SANDBOX_LOCATION_ID'];
    $api_base_url = $is_production
        ? 'https://connect.squareup.com/v2'
        : 'https://connect.squareupsandbox.com/v2';

    // Check if we've already processed this payment (idempotency)
    if (isset($data['idempotency_key'])) {
        $existing = check_transaction_processed($data['idempotency_key']);
        if ($existing) {
            echo json_encode([
                'success' => true,
                'license_key' => $existing['license_key'],
                'message' => 'Payment already processed'
            ]);
            exit;
        }
    }

    // Create payment request for Square
    $idempotency_key = $data['idempotency_key'] ?? uniqid();
    $payment_data = [
        'source_id' => $data['source_id'],
        'idempotency_key' => $idempotency_key,
        'amount_money' => [
            'amount' => 2000, // $20.00 in cents
            'currency' => 'CAD'
        ],
        'autocomplete' => true,
        'location_id' => $square_location_id,
        'reference_id' => $data['reference_id'] ?? 'Argo_Books_License',
        'note' => 'Argo Books - Premium License'
    ];

    // Process the payment through Square API
    $ch = curl_init("$api_base_url/payments");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($payment_data),
        CURLOPT_HTTPHEADER => [
            "Square-Version: 2025-10-16",
            "Authorization: Bearer $square_access_token",
            "Content-Type: application/json"
        ]
    ]);

    $response_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    if ($http_code >= 200 && $http_code < 300) {
        $payment_result = json_decode($response_data, true);

        if (isset($payment_result['payment'])) {
            $payment = $payment_result['payment'];
            $transaction_id = $payment['id'];
            $status = $payment['status'];
            $amount = $payment['amount_money']['amount'] / 100;
            $currency = $payment['amount_money']['currency'];

            if ($status === 'COMPLETED') {
                // Use shared helper for license creation and logging
                $response = process_payment_completion([
                    'email' => $data['email'],
                    'transaction_id' => $transaction_id,
                    'order_id' => $data['reference_id'] ?? '',
                    'amount' => $amount,
                    'currency' => $currency,
                    'payment_method' => 'Square',
                    'status' => $status,
                    'user_id' => $user_id
                ]);
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Payment not completed. Status: ' . $status
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid response from Square API'
            ];
        }
    } else {
        $errors = json_decode($response_data, true);
        $error_msg = '';

        if (isset($errors['errors']) && is_array($errors['errors'])) {
            foreach ($errors['errors'] as $error) {
                $error_msg .= isset($error['detail']) ? $error['detail'] . ' ' : '';
            }
        } else {
            $error_msg = 'HTTP error ' . $http_code;
            if ($curl_error) {
                $error_msg .= ': ' . $curl_error;
            }
        }

        error_log("Square payment error: $error_msg");
        $response = [
            'success' => false,
            'message' => 'Square payment error: ' . $error_msg
        ];
    }
} catch (Exception $e) {
    error_log('Square payment processing error: ' . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ];
}

// Send JSON response
echo json_encode($response);
