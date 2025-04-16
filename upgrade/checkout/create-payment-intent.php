<?php
/**
 * create-payment-intent.php - Creates a Stripe Payment Intent
 * This file receives an amount and returns a payment intent client secret
 */

require '../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set headers for JSON response
header('Content-Type: application/json');

// Get raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check for required data
if (!$data || !isset($data['amount']) || !isset($data['currency'])) {
    echo json_encode([
        'error' => 'Missing required payment information'
    ]);
    exit;
}

$stripe_secret_key = $_ENV['STRIPE_SECRET_KEY'];

try {
    // Initialize Stripe API
    require_once '../../vendor/autoload.php';
    \Stripe\Stripe::setApiKey($stripe_secret_key);

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
    echo json_encode([
        'error' => $e->getMessage()
    ]);
    
    // Log the error
    error_log('Stripe payment intent creation error: ' . $e->getMessage());
}
