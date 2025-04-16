<?php
/**
 * Process Stripe payments and generate license keys
 * This script receives Stripe payment intent data and creates a license key
 */

// Set headers for JSON response
header('Content-Type: application/json');

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

// Load database connection and license functions
require_once '../db_connect.php';
require_once '../license_functions.php';

// Initialize response
$response = [
    'success' => false,
    'message' => 'Failed to process payment'
];

try {
    $db = get_db_connection();
    
    // Check if this transaction has already been processed
    $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE transaction_id = :transaction_id LIMIT 1');
    $stmt->bindValue(':transaction_id', $data['payment_intent_id'], SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if ($result) {
        // Transaction already processed, return existing license key
        $response = [
            'success' => true,
            'license_key' => $result['license_key'],
            'message' => 'Payment already processed'
        ];
    } else {
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
        // Process the payment and generate license key
        else {
            // Create a license key
            $license_key = create_license_key($data['email']);
            
            if ($license_key) {
                // Update the license key with transaction details
                $stmt = $db->prepare('UPDATE license_keys SET 
                    transaction_id = :transaction_id, 
                    order_id = :order_id, 
                    payment_method = :payment_method,
                    activated = 1,
                    activation_date = CURRENT_TIMESTAMP
                    WHERE license_key = :license_key');
                
                $stmt->bindValue(':transaction_id', $data['payment_intent_id'], SQLITE3_TEXT);
                $stmt->bindValue(':order_id', $data['payment_method_id'], SQLITE3_TEXT);
                $stmt->bindValue(':payment_method', 'Stripe', SQLITE3_TEXT);
                $stmt->bindValue(':license_key', $license_key, SQLITE3_TEXT);
                $stmt->execute();
                
                // Send license key via email
                require_once '../email_sender.php';
                $email_sent = send_license_email($data['email'], $license_key);
                
                $response = [
                    'success' => true,
                    'license_key' => $license_key,
                    'email_sent' => $email_sent,
                    'message' => 'Payment processed successfully'
                ];
                
                // Log the transaction in a separate table for audit purposes
                $stmt = $db->prepare('INSERT INTO payment_transactions 
                    (transaction_id, order_id, email, amount, currency, payment_method, status, license_key, created_at) 
                    VALUES 
                    (:transaction_id, :order_id, :email, :amount, :currency, :payment_method, :status, :license_key, CURRENT_TIMESTAMP)');
                
                $stmt->bindValue(':transaction_id', $data['payment_intent_id'], SQLITE3_TEXT);
                $stmt->bindValue(':order_id', $data['payment_method_id'] ?? '', SQLITE3_TEXT);
                $stmt->bindValue(':email', $data['email'], SQLITE3_TEXT);
                $stmt->bindValue(':amount', $data['amount'], SQLITE3_TEXT);
                $stmt->bindValue(':currency', $data['currency'] ?? 'CAD', SQLITE3_TEXT);
                $stmt->bindValue(':payment_method', 'Stripe', SQLITE3_TEXT);
                $stmt->bindValue(':status', $data['status'], SQLITE3_TEXT);
                $stmt->bindValue(':license_key', $license_key, SQLITE3_TEXT);
                $stmt->execute();
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