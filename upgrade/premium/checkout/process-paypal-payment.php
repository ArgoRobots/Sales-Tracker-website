<?php
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Enable detailed error logging for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load required files
require_once '../../../db_connect.php';
require_once '../../../license_functions.php';
require_once '../../../email_sender.php';

// Get user_id if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check for required data
if (!$data || !isset($data['orderID']) || !isset($data['payer_email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required payment information'
    ]);
    exit;
}

// Get PayPal API credentials based on environment
$is_production = $_ENV['APP_ENV'] === 'production';
$client_id = $is_production
    ? $_ENV['PAYPAL_LIVE_CLIENT_ID']
    : $_ENV['PAYPAL_SANDBOX_CLIENT_ID'];
$client_secret = $is_production
    ? $_ENV['PAYPAL_LIVE_CLIENT_SECRET']
    : $_ENV['PAYPAL_SANDBOX_CLIENT_SECRET'];
$paypal_api_url = $is_production
    ? 'https://api-m.paypal.com'
    : 'https://api-m.sandbox.paypal.com';

// Initialize response
$response = [
    'success' => false,
    'message' => 'Failed to process payment'
];

try {
    $order_id = $data['orderID'];
    $payer_email = $data['payer_email'];

    // Step 1: Get access token
    $access_token = get_paypal_access_token($client_id, $client_secret, $paypal_api_url);
    if (!$access_token) {
        throw new Exception('Failed to authenticate with PayPal');
    }

    // Step 2: Get order details from PayPal to verify the payment
    $order_details = call_paypal_api(
        "$paypal_api_url/v2/checkout/orders/$order_id",
        'GET',
        $access_token
    );

    if (!$order_details) {
        throw new Exception('Failed to get order details from PayPal');
    }

    // Step 3: Verify order status
    $order_status = $order_details['status'] ?? '';

    // For PayPal, the order should already be captured by the frontend
    // But let's verify it's completed
    if ($order_status !== 'COMPLETED') {
        throw new Exception("Payment not completed. Status: $order_status");
    }

    // Step 4: Extract transaction details
    $transaction_id = $order_id;
    $amount = $data['amount'] ?? '20.00';
    $currency = $data['currency'] ?? 'CAD';

    // Get more specific transaction ID from capture details if available
    if (isset($order_details['purchase_units'][0]['payments']['captures'][0])) {
        $capture = $order_details['purchase_units'][0]['payments']['captures'][0];
        $transaction_id = $capture['id'] ?? $order_id;
        $amount = $capture['amount']['value'] ?? $amount;
        $currency = $capture['amount']['currency_code'] ?? $currency;
    }

    // Step 5: Database connection
    $db = get_db_connection();

    // Check if this transaction has already been processed
    $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE transaction_id = ? LIMIT 1');
    $stmt->bind_param('s', $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Transaction already processed, return existing license key
        $response = [
            'success' => true,
            'license_key' => $row['license_key'],
            'transaction_id' => $transaction_id,
            'order_id' => $order_id,
            'message' => 'Payment already processed'
        ];
        $stmt->close();
    } else {
        $stmt->close();

        // Create new license key
        $license_key = create_license_key($payer_email, $user_id);

        if ($license_key) {
            // Update the license key with transaction details
            $stmt = $db->prepare('UPDATE license_keys SET 
                transaction_id = ?, 
                order_id = ?, 
                payment_method = ?,
                activated = 1,
                activation_date = CURRENT_TIMESTAMP
                WHERE license_key = ?');

            $payment_method = 'PayPal';
            $stmt->bind_param('ssss', $transaction_id, $order_id, $payment_method, $license_key);
            $stmt->execute();
            $stmt->close();

            // Send license email
            $email_sent = send_license_email($payer_email, $license_key);

            // Log transaction for record keeping
            $stmt = $db->prepare('INSERT INTO payment_transactions 
                (transaction_id, order_id, email, amount, currency, payment_method, status, license_key, created_at) 
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, NOW())');

            $stmt->bind_param(
                'ssssssss',
                $transaction_id,
                $order_id,
                $payer_email,
                $amount,
                $currency,
                $payment_method,
                $order_status,
                $license_key
            );
            $stmt->execute();
            $stmt->close();

            $response = [
                'success' => true,
                'license_key' => $license_key,
                'transaction_id' => $transaction_id,
                'order_id' => $order_id,
                'email_sent' => $email_sent,
                'message' => 'Payment processed successfully'
            ];

            error_log("PayPal payment transaction recorded in database");
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to generate license key'
            ];
            error_log("Failed to generate license key");
        }
    }

    // Close database connection
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }
} catch (Exception $e) {
    error_log('PayPal payment processing error: ' . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Payment processing error: ' . $e->getMessage()
    ];

    // Close database connection if it exists
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }
}

// Send JSON response
echo json_encode($response);

/**
 * Get PayPal access token
 */
function get_paypal_access_token($client_id, $client_secret, $api_url)
{
    $ch = curl_init("$api_url/v1/oauth2/token");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "$client_id:$client_secret",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => "grant_type=client_credentials"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code !== 200 || !$response) {
        error_log("PayPal auth error ($http_code): $curl_error");
        return null;
    }

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

/**
 * Generic PayPal API call function
 */
function call_paypal_api($url, $method, $access_token, $data = null)
{
    $ch = curl_init($url);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $access_token",
            "Content-Type: application/json"
        ]
    ];

    if ($data && $method !== 'GET') {
        $options[CURLOPT_POSTFIELDS] = $data;
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if (($http_code < 200 || $http_code >= 300) || !$response) {
        error_log("PayPal API error ($http_code): $error, Response: $response");
        return null;
    }

    return json_decode($response, true);
}
