<?php
require_once '../../vendor/autoload.php';
 
// Set headers for JSON response
header('Content-Type: application/json');

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

    // Initialize Stripe API
    // \Stripe\Stripe::setApiKey("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");
    \Stripe\Stripe::setApiKey("sk_test_51PKOfZFxK6AutkEZ4xcoounqIYHbDUhY1RfpCK3OkJsb6UZDAgqYx153rFjckFTJ2hcaUEaZ3RLa0mGzUXevPCKB00EFmAuOTx");

    // Create a PaymentIntent
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $data['amount'],
        'currency' => strtolower($data['currency']),
        'payment_method_types' => ['card'],
        'metadata' => [
            'product' => 'Argo Sales Tracker - Lifetime Access',
            'email' => $data['email'] ?? ''
        ],
        'receipt_email' => $data['email'] ?? null,
        'description' => 'Argo Sales Tracker - Lifetime Access'
    ]);

    // Return the client secret
    echo json_encode([
        'client_secret' => $payment_intent->client_secret,
        'payment_intent_id' => $payment_intent->id
    ]);
    
} catch (Exception $e) {
    // Send proper JSON error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Payment processing error: ' . $e->getMessage()
    ]);
}
?>