<?php
session_start();

require_once '../../../vendor/autoload.php';
require_once '../../../db_connect.php';
require_once '../../../license_functions.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Get user_id if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

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

    // Get Stripe API key based on environment
    $is_production = $_ENV['APP_ENV'] === 'production';
    $stripe_secret_key = $is_production
        ? $_ENV['STRIPE_LIVE_SECRET_KEY']
        : $_ENV['STRIPE_SANDBOX_SECRET_KEY'];

    // Initialize Stripe API
    \Stripe\Stripe::setApiKey($stripe_secret_key);

    // Pre-generate license key for the customer (we'll store it temporarily)
    $customer_email = isset($data['email']) ? $data['email'] : '';
    if (!empty($customer_email)) {
        // Check if a license key was already pre-generated for this email in the last hour
        // If so, we'll reuse it rather than creating multiple keys
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT license_key FROM license_keys 
                             WHERE email = ? 
                             AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                             AND activated = 0
                             AND transaction_id IS NULL
                             LIMIT 1');
        $stmt->bind_param('s', $customer_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $license_key = $row['license_key'];
        } else {
            // Create a new license key
            $license_key = create_license_key($customer_email, $user_id);
        }
        $stmt->close();
    }

    // Create a PaymentIntent
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $data['amount'],
        'currency' => strtolower($data['currency']),
        'payment_method_types' => ['card'],
        'metadata' => [
            'product' => 'Argo Books - Premium License',
            'email' => $data['email'] ?? '',
            'license_key' => $license_key ?? ''
        ],
        'receipt_email' => $data['email'] ?? null,
        'description' => 'Argo Books - Premium License'
    ]);

    // Store the payment intent ID in our database
    if (isset($license_key) && !empty($license_key)) {
        $stmt = $db->prepare('UPDATE license_keys SET 
                              payment_intent = ?
                              WHERE license_key = ?');
        $stmt->bind_param('ss', $payment_intent->id, $license_key);
        $stmt->execute();
        $stmt->close();
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
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }
}
