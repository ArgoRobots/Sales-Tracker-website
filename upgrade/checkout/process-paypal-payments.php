<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
error_log('PayPal processing started');

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
if (empty($order_id)) {
    redirect_with_error('Missing order information');
}

// Load required files
require_once '../../db_connect.php';
require_once '../../license_functions.php';
require_once '../../email_sender.php';

// PayPal API credentials
$client_id = 'AaXh6OUCT8DLYES-I_sVj24PeifEmN207ufVjZOavXOufkhMOzTGNB2Tk1YBQ4nYv4CNJDcjqn8fxLln';
$client_secret = 'EHdLlciwyzqlJujTnqir9wFsBHf5MGt0YVK8vS5xDH_UIFxKjy44udzy7FkSZdZdL9POu_tdf-gcydzk';
$paypal_api_url = 'https://api-m.paypal.com';

try {
    // Step 1: Get access token
    $access_token = get_paypal_access_token($client_id, $client_secret, $paypal_api_url);
    if (!$access_token) {
        throw new Exception('Failed to authenticate with PayPal');
    }
    
    // Step 2: Get order details
    $order_details = call_paypal_api(
        "$paypal_api_url/v2/checkout/orders/$order_id", 
        'GET', 
        $access_token
    );
    
    if (!$order_details) {
        throw new Exception('Failed to get order details from PayPal');
    }
    
    // Step 3: Check if payment needs to be captured
    $order_status = $order_details['status'] ?? '';
    error_log("Order status: $order_status");
    
    if ($order_status === 'APPROVED') {
        // Capture the payment to actually transfer funds
        error_log("Capturing payment for order: $order_id");
        $order_details = call_paypal_api(
            "$paypal_api_url/v2/checkout/orders/$order_id/capture",
            'POST',
            $access_token,
            '{}'
        );
        
        if (!$order_details) {
            throw new Exception('Failed to capture payment');
        }
        
        $order_status = $order_details['status'] ?? '';
        error_log("Order status after capture: $order_status");
    }
    
    // Step 4: Verify payment is complete
    if ($order_status !== 'COMPLETED') {
        throw new Exception("Payment not completed. Final status: $order_status");
    }
    
    // Step 5: Extract transaction details
    $payer_email = $order_details['payer']['email_address'] ?? '';
    $transaction_id = $order_id;
    $amount = '20.00';
    $currency = 'CAD';
    
    // Get transaction ID and amount from capture details if available
    if (isset($order_details['purchase_units'][0]['payments']['captures'][0])) {
        $capture = $order_details['purchase_units'][0]['payments']['captures'][0];
        $transaction_id = $capture['id'] ?? $order_id;
        $amount = $capture['amount']['value'] ?? $amount;
        $currency = $capture['amount']['currency_code'] ?? $currency;
    }
    
    error_log("Payment verified: Email=$payer_email, Transaction=$transaction_id, Amount=$amount $currency");
    
    // Step 6: Process license
    process_license($transaction_id, $order_id, $payer_email, $amount, $currency, $order_status);
    
} catch (Exception $e) {
    error_log('PayPal error: ' . $e->getMessage());
    redirect_with_error('Payment processing error: ' . $e->getMessage(), $order_id);
}

/**
 * Get PayPal access token
 */
function get_paypal_access_token($client_id, $client_secret, $api_url) {
    $ch = curl_init("$api_url/v1/oauth2/token");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "$client_id:$client_secret",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => "grant_type=client_credentials"
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200 || !$response) {
        error_log("PayPal auth error ($http_code): " . curl_error($ch));
        return null;
    }
    
    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

/**
 * Generic PayPal API call function
 */
function call_paypal_api($url, $method, $access_token, $data = null) {
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

/**
 * Process license creation and storage
 */
function process_license($transaction_id, $order_id, $email, $amount, $currency, $status) {
    $db = get_db_connection();
    
    // Check for existing license
    $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE transaction_id = :transaction_id LIMIT 1');
    $stmt->bindValue(':transaction_id', $transaction_id, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if ($result) {
        // Use existing license
        $license_key = $result['license_key'];
        error_log("Using existing license: $license_key");
    } else {
        // Create new license
        $license_key = create_license_key($email);
        error_log("Created new license: $license_key");
        
        // Store transaction details
        $stmt = $db->prepare('UPDATE license_keys SET 
            transaction_id = :transaction_id, 
            order_id = :order_id, 
            payment_method = :payment_method,
            activated = 1,
            activation_date = CURRENT_TIMESTAMP
            WHERE license_key = :license_key');
        
        $stmt->bindValue(':transaction_id', $transaction_id, SQLITE3_TEXT);
        $stmt->bindValue(':order_id', $order_id, SQLITE3_TEXT);
        $stmt->bindValue(':payment_method', 'PayPal', SQLITE3_TEXT);
        $stmt->bindValue(':license_key', $license_key, SQLITE3_TEXT);
        $stmt->execute();
        
        // Send license email
        send_license_email($email, $license_key);
        
        // Log transaction
        $stmt = $db->prepare('INSERT INTO payment_transactions 
            (transaction_id, order_id, email, amount, currency, payment_method, status, license_key, created_at) 
            VALUES 
            (:transaction_id, :order_id, :email, :amount, :currency, :payment_method, :status, :license_key, CURRENT_TIMESTAMP)');
        
        $stmt->bindValue(':transaction_id', $transaction_id, SQLITE3_TEXT);
        $stmt->bindValue(':order_id', $order_id, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':amount', $amount, SQLITE3_TEXT);
        $stmt->bindValue(':currency', $currency, SQLITE3_TEXT);
        $stmt->bindValue(':payment_method', 'PayPal', SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':license_key', $license_key, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    // Redirect to thank you page
    $redirect_url = "../thank-you/index.html?" . http_build_query([
        'order_id' => $order_id,
        'transaction_id' => $transaction_id,
        'license' => $license_key,
        'email' => $email,
        'method' => 'paypal'
    ]);
    
    error_log("Redirecting to: $redirect_url");
    header("Location: $redirect_url");
    exit;
}

/**
 * Redirect with error message
 */
function redirect_with_error($message, $order_id = '') {
    $url = "../thank-you/index.html?error=" . urlencode($message);
    if ($order_id) {
        $url .= "&order_id=" . urlencode($order_id);
    }
    header("Location: $url");
    exit;
}