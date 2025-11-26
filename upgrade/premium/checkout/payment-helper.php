<?php
/**
 * Payment Helper Functions
 * Shared functionality for processing premium license payments across all payment providers
 */

require_once __DIR__ . '/../../../db_connect.php';
require_once __DIR__ . '/../../../license_functions.php';
require_once __DIR__ . '/../../../email_sender.php';

/**
 * Check if a transaction has already been processed (idempotency check)
 *
 * @param string $transaction_id The transaction ID to check
 * @return array|null Returns license key data if already processed, null otherwise
 */
function check_transaction_processed($transaction_id) {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE transaction_id = ? LIMIT 1');
    $stmt->bind_param('s', $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row;
}

/**
 * Check if a license key was pre-generated for a Stripe payment intent
 *
 * @param string $payment_intent_id The Stripe payment intent ID
 * @return string|null Returns license key if found, null otherwise
 */
function get_pregenerated_license($payment_intent_id) {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE payment_intent = ? LIMIT 1');
    $stmt->bind_param('s', $payment_intent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['license_key'] : null;
}

/**
 * Activate a license key with transaction details
 *
 * @param string $license_key The license key to activate
 * @param string $transaction_id The payment transaction ID
 * @param string $order_id The order/reference ID
 * @param string $payment_method The payment method (Stripe, Square, PayPal)
 * @return bool Success status
 */
function activate_license_key($license_key, $transaction_id, $order_id, $payment_method) {
    $db = get_db_connection();
    $stmt = $db->prepare('UPDATE license_keys SET
        transaction_id = ?,
        order_id = ?,
        payment_method = ?,
        activated = 1,
        activation_date = CURRENT_TIMESTAMP
        WHERE license_key = ?');

    $stmt->bind_param('ssss', $transaction_id, $order_id, $payment_method, $license_key);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Log a payment transaction for audit purposes
 *
 * @param string $transaction_id The payment transaction ID
 * @param string $order_id The order/reference ID
 * @param string $email Customer email
 * @param float $amount Payment amount
 * @param string $currency Currency code (e.g., CAD)
 * @param string $payment_method Payment method name
 * @param string $status Payment status
 * @param string $license_key The associated license key
 * @return bool Success status
 */
function log_payment_transaction($transaction_id, $order_id, $email, $amount, $currency, $payment_method, $status, $license_key) {
    $db = get_db_connection();
    $stmt = $db->prepare('INSERT INTO payment_transactions
        (transaction_id, order_id, email, amount, currency, payment_method, status, license_key, created_at)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, NOW())');

    $stmt->bind_param(
        'sssdssss',
        $transaction_id,
        $order_id,
        $email,
        $amount,
        $currency,
        $payment_method,
        $status,
        $license_key
    );
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Process a completed payment and create/activate license key
 * This is the main entry point for finalizing a payment
 *
 * @param array $params Payment parameters:
 *   - email: Customer email (required)
 *   - transaction_id: Payment transaction ID (required)
 *   - order_id: Order/reference ID (optional)
 *   - amount: Payment amount (required)
 *   - currency: Currency code (default: CAD)
 *   - payment_method: Payment method name (required)
 *   - status: Payment status (required)
 *   - user_id: User ID if logged in (optional)
 *   - payment_intent_id: Stripe payment intent for pre-generated keys (optional)
 * @return array Response with success status and license key or error message
 */
function process_payment_completion($params) {
    $email = $params['email'] ?? '';
    $transaction_id = $params['transaction_id'] ?? '';
    $order_id = $params['order_id'] ?? '';
    $amount = $params['amount'] ?? 0;
    $currency = $params['currency'] ?? 'CAD';
    $payment_method = $params['payment_method'] ?? '';
    $status = $params['status'] ?? 'completed';
    $user_id = $params['user_id'] ?? null;
    $payment_intent_id = $params['payment_intent_id'] ?? null;

    // Check if transaction already processed (idempotency)
    $existing = check_transaction_processed($transaction_id);
    if ($existing) {
        return [
            'success' => true,
            'license_key' => $existing['license_key'],
            'transaction_id' => $transaction_id,
            'order_id' => $order_id,
            'message' => 'Payment already processed'
        ];
    }

    // Get or create license key
    $license_key = null;

    // For Stripe, check for pre-generated license key
    if ($payment_intent_id) {
        $license_key = get_pregenerated_license($payment_intent_id);
    }

    // Create new license key if needed
    if (!$license_key) {
        $license_key = create_license_key($email, $user_id);
    }

    if (!$license_key) {
        return [
            'success' => false,
            'message' => 'Failed to generate license key'
        ];
    }

    // Activate the license key
    activate_license_key($license_key, $transaction_id, $order_id, $payment_method);

    // Send license email
    $email_sent = send_license_email($email, $license_key);

    // Log the transaction
    log_payment_transaction(
        $transaction_id,
        $order_id,
        $email,
        $amount,
        $currency,
        $payment_method,
        $status,
        $license_key
    );

    return [
        'success' => true,
        'license_key' => $license_key,
        'transaction_id' => $transaction_id,
        'order_id' => $order_id,
        'email_sent' => $email_sent,
        'message' => 'Payment processed successfully'
    ];
}
