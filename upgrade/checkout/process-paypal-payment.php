<?php
// Set error reporting for debugging
ini_set('display_errors', 0);
error_log('PayPal payment processing started');

// Set headers for JSON response
header('Content-Type: application/json');

// Get raw POST data
$json_data = file_get_contents('php://input');
error_log('Received data: ' . $json_data);

$data = json_decode($json_data, true);

// Check for required data
if (!$data || !isset($data['transaction_id']) || !isset($data['email'])) {
    error_log('Missing required payment information');
    echo json_encode([
        'success' => false,
        'message' => 'Missing required payment information'
    ]);
    exit;
}

// Load database connection and license functions
require_once '../../db_connect.php';
require_once '../../license_functions.php';
require_once '../../email_sender.php';

// Initialize response
$response = [
    'success' => false,
    'message' => 'Failed to process payment'
];

try {
    error_log('Connecting to database');
    $db = get_db_connection();
    
    // Check if this transaction has already been processed
    $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE transaction_id = :transaction_id LIMIT 1');
    $stmt->bindValue(':transaction_id', $data['transaction_id'], SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if ($result) {
        // Transaction already processed, return existing license key
        error_log('Transaction already processed, returning existing license key');
        $response = [
            'success' => true,
            'license_key' => $result['license_key'],
            'message' => 'Payment already processed'
        ];
    } else {
        // Validate payment status
        if (!isset($data['status']) || $data['status'] !== 'COMPLETED') {
            error_log('Payment status is not completed: ' . ($data['status'] ?? 'undefined'));
            $response = [
                'success' => false,
                'message' => 'Payment status is not completed'
            ];
        } 
        // Validate payment amount
        else if (!isset($data['amount']) || floatval($data['amount']) < 20.00) {
            error_log('Invalid payment amount: ' . ($data['amount'] ?? 'undefined'));
            $response = [
                'success' => false,
                'message' => 'Invalid payment amount'
            ];
        } 
        // Process the payment and generate license key
        else {
            error_log('Creating new license key for ' . $data['email']);
            // Create a license key
            $license_key = create_license_key($data['email']);
            
            if ($license_key) {
                error_log('License key created successfully: ' . $license_key);
                // Update the license key with transaction details
                $stmt = $db->prepare('UPDATE license_keys SET 
                    transaction_id = :transaction_id, 
                    order_id = :order_id, 
                    payment_method = :payment_method,
                    activated = 1,
                    activation_date = CURRENT_TIMESTAMP
                    WHERE license_key = :license_key');
                
                $stmt->bindValue(':transaction_id', $data['transaction_id'], SQLITE3_TEXT);
                $stmt->bindValue(':order_id', $data['order_id'], SQLITE3_TEXT);
                $stmt->bindValue(':payment_method', 'PayPal', SQLITE3_TEXT);
                $stmt->bindValue(':license_key', $license_key, SQLITE3_TEXT);
                $stmt->execute();
                
                // Send license key via email
                error_log('Sending license email to ' . $data['email']);
                $email_sent = send_license_email($data['email'], $license_key);
                
                $response = [
                    'success' => true,
                    'license_key' => $license_key,
                    'email_sent' => $email_sent,
                    'message' => 'Payment processed successfully'
                ];
                
                // Log the transaction in a separate table for audit purposes
                error_log('Logging transaction details');
                $stmt = $db->prepare('INSERT INTO payment_transactions 
                    (transaction_id, order_id, email, amount, currency, payment_method, status, license_key, created_at) 
                    VALUES 
                    (:transaction_id, :order_id, :email, :amount, :currency, :payment_method, :status, :license_key, CURRENT_TIMESTAMP)');
                
                $stmt->bindValue(':transaction_id', $data['transaction_id'], SQLITE3_TEXT);
                $stmt->bindValue(':order_id', $data['order_id'] ?? '', SQLITE3_TEXT);
                $stmt->bindValue(':email', $data['email'], SQLITE3_TEXT);
                $stmt->bindValue(':amount', $data['amount'], SQLITE3_TEXT);
                $stmt->bindValue(':currency', $data['currency'] ?? 'CAD', SQLITE3_TEXT);
                $stmt->bindValue(':payment_method', 'PayPal', SQLITE3_TEXT);
                $stmt->bindValue(':status', $data['status'], SQLITE3_TEXT);
                $stmt->bindValue(':license_key', $license_key, SQLITE3_TEXT);
                $stmt->execute();
            } else {
                error_log('Failed to generate license key');
                $response = [
                    'success' => false,
                    'message' => 'Failed to generate license key'
                ];
            }
        }
    }
} catch (Exception $e) {
    error_log('PayPal payment processing error: ' . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ];
}

// Send JSON response
error_log('Sending response: ' . json_encode($response));
echo json_encode($response);