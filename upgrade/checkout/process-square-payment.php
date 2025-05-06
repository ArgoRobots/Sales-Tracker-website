<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Enable detailed error logging for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load database connection and license functions
require_once '../../db_connect.php';
require_once '../../license_functions.php';
require_once '../../email_sender.php';

// Log incoming request data
$request_log = 'Square payment request received at: ' . date('Y-m-d H:i:s') . "\n";
$request_log .= 'Request IP: ' . $_SERVER['REMOTE_ADDR'] . "\n";
error_log($request_log);

// Get raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Log the received data (but mask sensitive information)
$masked_data = $data;
if (isset($masked_data['source_id'])) {
    $masked_data['source_id'] = substr($masked_data['source_id'], 0, 4) . '...' . substr($masked_data['source_id'], -4);
}
error_log('Received payment data: ' . json_encode($masked_data));

// Check for required data
if (!$data || !isset($data['source_id']) || !isset($data['email'])) {
    $error_msg = 'Missing required payment information';
    error_log('Payment error: ' . $error_msg);
    echo json_encode([
        'success' => false,
        'message' => $error_msg
    ]);
    exit;
}

// Initialize response
$response = [
    'success' => false,
    'message' => 'Failed to process payment'
];

try {
    // Square API credentials
    $square_access_token = 'EAAAl8Gk1ywNhT-TTxh1v47ZXHH5x39glYRkUSVk3_jGNXxbTaLhaTnlz0zd7nH6';
    $square_location_id = 'L30NT6Z9HKW81';
    $is_production = true;

    // Base URL for Square API
    $api_base_url = $is_production ?
        'https://connect.squareup.com/v2' :
        'https://connect.squareupsandbox.com/v2';

    error_log('Using Square ' . ($is_production ? 'Production' : 'Sandbox') . ' environment');
    error_log('Using location ID: ' . $square_location_id);

    // Database connection
    $db = get_db_connection();

    // Check if we've already processed this payment (idempotency)
    if (isset($data['idempotency_key'])) {
        $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE transaction_id = ? LIMIT 1');
        $stmt->bind_param('s', $data['idempotency_key']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Transaction already processed, return existing license key
            $response = [
                'success' => true,
                'license_key' => $row['license_key'],
                'message' => 'Payment already processed'
            ];
            echo json_encode($response);
            exit;
        }
        $stmt->close();
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
        'reference_id' => $data['reference_id'] ?? 'Argo_Sales_Tracker_License',
        'note' => 'Argo Sales Tracker - Lifetime Access'
    ];

    // Log the payment request (without sensitive data)
    $masked_payment_data = $payment_data;
    $masked_payment_data['source_id'] = substr($payment_data['source_id'], 0, 4) . '...' . substr($payment_data['source_id'], -4);
    error_log('Square payment request: ' . json_encode($masked_payment_data));

    // Process the payment through Square API using cURL
    $ch = curl_init("$api_base_url/payments");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($payment_data),
        CURLOPT_HTTPHEADER => [
            "Square-Version: 2023-06-08",
            "Authorization: Bearer $square_access_token",
            "Content-Type: application/json"
        ]
    ]);

    $response_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Log the response and HTTP code for debugging
    error_log("Square API response (HTTP $http_code): " . $response_data);

    if ($http_code >= 200 && $http_code < 300) {
        $payment_result = json_decode($response_data, true);

        if (isset($payment_result['payment'])) {
            $payment = $payment_result['payment'];
            $transaction_id = $payment['id'];
            $status = $payment['status'];
            $amount = $payment['amount_money']['amount'] / 100; // Convert cents to dollars
            $currency = $payment['amount_money']['currency'];

            error_log("Payment completed with status: $status, transaction ID: $transaction_id");

            // Verify payment was approved
            if ($status === 'COMPLETED') {
                // Create a new license key
                $license_key = create_license_key($data['email']);
                error_log("Generated license key: $license_key for email: " . $data['email']);

                if ($license_key) {
                    // Update the license key with transaction details
                    $stmt = $db->prepare('UPDATE license_keys SET 
                        transaction_id = ?,
                        order_id = ?,
                        payment_method = ?,
                        activated = 1,
                        activation_date = CURRENT_TIMESTAMP
                        WHERE license_key = ?');

                    $payment_method = 'Square';
                    $order_id = $data['reference_id'] ?? '';
                    $stmt->bind_param('ssss', $transaction_id, $order_id, $payment_method, $license_key);
                    $stmt->execute();
                    $stmt->close();

                    // Send license key via email
                    $email_sent = send_license_email($data['email'], $license_key);
                    error_log("License email sent to " . $data['email'] . ": " . ($email_sent ? 'Success' : 'Failed'));

                    $response = [
                        'success' => true,
                        'license_key' => $license_key,
                        'transaction_id' => $transaction_id,
                        'order_id' => $data['reference_id'] ?? '',
                        'email_sent' => $email_sent,
                        'message' => 'Payment processed successfully'
                    ];

                    // Log the transaction for record keeping
                    $stmt = $db->prepare('INSERT INTO payment_transactions 
                        (transaction_id, order_id, email, amount, currency, payment_method, status, license_key, created_at) 
                        VALUES 
                        (?, ?, ?, ?, ?, ?, ?, ?, NOW())');

                    $stmt->bind_param(
                        'sssdssss',
                        $transaction_id,
                        $order_id,
                        $data['email'],
                        $amount,
                        $currency,
                        $payment_method,
                        $status,
                        $license_key
                    );
                    $stmt->execute();
                    $stmt->close();

                    error_log("Payment transaction recorded in database");
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Failed to generate license key'
                    ];
                    error_log("Failed to generate license key");
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Payment not completed. Status: ' . $status
                ];
                error_log("Payment not completed. Status: $status");
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid response from Square API'
            ];
            error_log("Invalid response from Square API: $response_data");
        }
    } else {
        $errors = json_decode($response_data, true);
        $error_msg = '';
        $detailed_error = 'Unknown error';

        if (isset($errors['errors']) && is_array($errors['errors'])) {
            foreach ($errors['errors'] as $error) {
                $error_msg .= isset($error['detail']) ? $error['detail'] . ' ' : '';
                $detailed_error = json_encode($error);
            }
        } else {
            $error_msg = 'HTTP error ' . $http_code;
            if ($curl_error) {
                $error_msg .= ': ' . $curl_error;
            }
            $detailed_error = $response_data;
        }

        error_log("Square payment error: $error_msg");
        error_log("Detailed error: $detailed_error");

        $response = [
            'success' => false,
            'message' => 'Square payment error: ' . $error_msg,
            'debug_info' => $is_production ? null : [
                'http_code' => $http_code,
                'detailed_error' => $detailed_error
            ]
        ];
    }
} catch (Exception $e) {
    $error_message = 'Square payment processing error: ' . $e->getMessage();
    error_log($error_message);
    error_log('Exception trace: ' . $e->getTraceAsString());

    $response = [
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ];
}

// Send JSON response
echo json_encode($response);
