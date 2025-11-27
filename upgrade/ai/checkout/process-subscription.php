<?php
/**
 * AI Subscription Payment Processor
 * Handles subscription creation for AI features with recurring billing support
 */

header('Content-Type: application/json');

require_once '../../../db_connect.php';
require_once '../../../email_sender.php';
require_once '../../../vendor/autoload.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit();
}

// Extract common fields
$email = $input['email'] ?? $input['payer_email'] ?? '';
$amount = floatval($input['amount'] ?? 0);
$currency = $input['currency'] ?? 'CAD';
$billing = $input['billing'] ?? 'monthly';
$hasDiscount = $input['hasDiscount'] ?? false;
$premiumLicenseKey = $input['premiumLicenseKey'] ?? '';
$paymentMethod = $input['payment_method'] ?? 'unknown';
$userId = intval($input['user_id'] ?? 0);

// Credit configuration for monthly subscriptions with discount
$discountAmount = 20.00;
$monthlyPrice = 5.00;
$isMonthlyWithCredit = ($billing === 'monthly' && $hasDiscount);
$creditBalance = 0;
$originalCredit = 0;

// Get environment configuration
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

// For monthly with credit, amount can be 0 (first months covered by credit)
if (empty($email) || $userId <= 0 || ($amount <= 0 && !$isMonthlyWithCredit)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields or invalid user']);
    exit();
}

// Verify license key for discount if claimed
if ($hasDiscount && !empty($premiumLicenseKey)) {
    try {
        $stmt = $pdo->prepare("SELECT id, activated FROM license_keys WHERE license_key = ? AND activated = 1");
        $stmt->execute([$premiumLicenseKey]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid or inactive license key for discount']);
            exit();
        }
    } catch (PDOException $e) {
        // License verification failed - proceed without discount
        $hasDiscount = false;
        $isMonthlyWithCredit = false;
    }
}

// Set credit balance for monthly subscriptions with verified discount
if ($isMonthlyWithCredit) {
    $originalCredit = $discountAmount;
    // Deduct first month from credit balance
    $creditBalance = $discountAmount - $monthlyPrice; // $20 - $5 = $15 remaining
    // Set amount to 0 for initial payment - will use credit
    $amount = 0;
}

// Verify user exists
try {
    $stmt = $pdo->prepare("SELECT id FROM community_users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid user']);
        exit();
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

// Check if user is updating payment method for existing subscription
$isUpdatingPaymentMethod = $input['update_payment_method'] ?? false;
$existingSubscription = null;
$subscriptionStillValid = false;

try {
    $stmt = $pdo->prepare("
        SELECT * FROM ai_subscriptions
        WHERE user_id = ? AND status IN ('active', 'cancelled', 'payment_failed')
        AND end_date > NOW()
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$userId]);
    $existingSubscription = $stmt->fetch(PDO::FETCH_ASSOC);
    $subscriptionStillValid = ($existingSubscription !== false);
} catch (PDOException $e) {
    // Continue with new subscription if check fails
}

// Generate subscription ID
function generateSubscriptionId() {
    return 'AI-' . strtoupper(bin2hex(random_bytes(8)));
}

try {
    $pdo->beginTransaction();

    // Generate subscription ID
    $subscriptionId = generateSubscriptionId();

    // Calculate subscription dates
    $startDate = date('Y-m-d H:i:s');
    if ($billing === 'yearly') {
        $endDate = date('Y-m-d H:i:s', strtotime('+1 year'));
    } else {
        $endDate = date('Y-m-d H:i:s', strtotime('+1 month'));
    }

    // Process payment and get transaction details based on payment method
    $transactionId = '';
    $paymentToken = null; // Token for recurring billing
    $stripeCustomerId = null; // Stripe customer ID for recurring billing

    // Check if this is a PayPal subscription
    $paypalSubscriptionId = null;

    // Skip payment processing for:
    // 1. Monthly subscriptions with credit (no charge needed)
    // 2. Updating payment method when subscription is still within paid period
    $skipPaymentProcessing = $isMonthlyWithCredit || ($isUpdatingPaymentMethod && $subscriptionStillValid);

    switch ($paymentMethod) {
        case 'paypal':
            $isPayPalSubscription = $input['is_paypal_subscription'] ?? false;

            if ($isPayPalSubscription) {
                // PayPal Subscriptions API - recurring billing handled by PayPal
                $paypalSubscriptionId = $input['paypal_subscription_id'] ?? $input['subscriptionID'] ?? '';
                $transactionId = $paypalSubscriptionId;
                $paymentToken = $paypalSubscriptionId; // Store subscription ID as token
            } else {
                // One-time PayPal payment (fallback)
                $transactionId = $input['orderID'] ?? '';
                $paymentToken = null;
            }
            break;

        case 'stripe':
            $paymentMethodId = $input['payment_method_id'] ?? '';
            $transactionId = $paymentMethodId;

            // Initialize Stripe
            $stripeSecretKey = $isProduction
                ? $_ENV['STRIPE_LIVE_SECRET_KEY']
                : $_ENV['STRIPE_SANDBOX_SECRET_KEY'];
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            try {
                // Create or get customer
                $customers = \Stripe\Customer::all(['email' => $email, 'limit' => 1]);
                if (count($customers->data) > 0) {
                    $customer = $customers->data[0];
                } else {
                    $customer = \Stripe\Customer::create([
                        'email' => $email,
                        'metadata' => ['user_id' => $userId]
                    ]);
                }

                // Attach payment method to customer for recurring billing
                $paymentMethodObj = \Stripe\PaymentMethod::retrieve($paymentMethodId);
                $paymentMethodObj->attach(['customer' => $customer->id]);

                // Set as default payment method
                \Stripe\Customer::update($customer->id, [
                    'invoice_settings' => ['default_payment_method' => $paymentMethodId]
                ]);

                // Store customer ID for recurring billing
                $stripeCustomerId = $customer->id;

                // For monthly with credit, just store the payment method without charging
                if ($skipPaymentProcessing) {
                    $transactionId = 'CREDIT_' . strtoupper(bin2hex(random_bytes(8)));
                    $paymentToken = $paymentMethodId; // Store for future renewals when credit depleted
                } else {
                    // Create payment intent for the initial charge
                    $paymentIntent = \Stripe\PaymentIntent::create([
                        'amount' => intval($amount * 100),
                        'currency' => 'cad',
                        'customer' => $customer->id,
                        'payment_method' => $paymentMethodId,
                        'off_session' => true,
                        'confirm' => true,
                        'description' => "AI Subscription - Initial Payment ($billing)",
                        'receipt_email' => $email,
                        'metadata' => [
                            'subscription_id' => $subscriptionId,
                            'user_id' => $userId,
                            'billing_cycle' => $billing
                        ]
                    ]);

                    if ($paymentIntent->status !== 'succeeded') {
                        throw new Exception('Payment not completed');
                    }

                    $transactionId = $paymentIntent->id;
                    $paymentToken = $paymentMethodId; // Store for recurring billing
                }

            } catch (\Stripe\Exception\CardException $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => 'Card declined: ' . $e->getMessage()]);
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Stripe error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Payment processing failed']);
                exit();
            }
            break;

        case 'square':
            $sourceId = $input['source_id'] ?? '';

            // Initialize Square API credentials
            $squareAccessToken = $isProduction
                ? $_ENV['SQUARE_LIVE_ACCESS_TOKEN']
                : $_ENV['SQUARE_SANDBOX_ACCESS_TOKEN'];
            $squareLocationId = $isProduction
                ? $_ENV['SQUARE_LIVE_LOCATION_ID']
                : $_ENV['SQUARE_SANDBOX_LOCATION_ID'];
            $squareApiBaseUrl = $isProduction
                ? 'https://connect.squareup.com/v2'
                : 'https://connect.squareupsandbox.com/v2';

            // Helper function for Square API calls
            $squareApiCall = function($endpoint, $method, $data = null) use ($squareApiBaseUrl, $squareAccessToken) {
                $ch = curl_init("$squareApiBaseUrl/$endpoint");
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => $method,
                    CURLOPT_HTTPHEADER => [
                        "Square-Version: 2025-10-16",
                        "Authorization: Bearer $squareAccessToken",
                        "Content-Type: application/json"
                    ]
                ]);
                if ($data !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                return ['response' => json_decode($response, true), 'http_code' => $httpCode];
            };

            try {
                // Search for existing customer by email
                $searchData = [
                    'query' => [
                        'filter' => [
                            'email_address' => [
                                'exact' => $email
                            ]
                        ]
                    ]
                ];
                $searchResult = $squareApiCall('customers/search', 'POST', $searchData);
                $customerId = null;

                if ($searchResult['http_code'] >= 200 && $searchResult['http_code'] < 300
                    && !empty($searchResult['response']['customers'])) {
                    $customerId = $searchResult['response']['customers'][0]['id'];
                } else {
                    // Create new customer
                    $customerData = [
                        'idempotency_key' => uniqid('cust_', true),
                        'email_address' => $email,
                        'reference_id' => "user_$userId"
                    ];
                    $createResult = $squareApiCall('customers', 'POST', $customerData);

                    if ($createResult['http_code'] >= 200 && $createResult['http_code'] < 300
                        && isset($createResult['response']['customer'])) {
                        $customerId = $createResult['response']['customer']['id'];
                    }
                }

                // Create card on file for recurring billing
                $cardData = [
                    'idempotency_key' => uniqid('card_', true),
                    'source_id' => $sourceId,
                    'card' => [
                        'customer_id' => $customerId
                    ]
                ];
                $cardResult = $squareApiCall('cards', 'POST', $cardData);
                $cardId = null;

                if ($cardResult['http_code'] >= 200 && $cardResult['http_code'] < 300
                    && isset($cardResult['response']['card'])) {
                    $cardId = $cardResult['response']['card']['id'];
                } else {
                    $errorDetail = $cardResult['response']['errors'][0]['detail'] ?? 'Failed to save card';
                    throw new Exception($errorDetail);
                }

                // For monthly with credit, just store the card without charging
                if ($skipPaymentProcessing) {
                    $transactionId = 'CREDIT_' . strtoupper(bin2hex(random_bytes(8)));
                    $paymentToken = $cardId; // Store card ID for future renewals when credit depleted
                } else {
                    // Process initial payment
                    $paymentData = [
                        'idempotency_key' => uniqid('payment_', true),
                        'source_id' => $cardId ?? $sourceId,
                        'amount_money' => [
                            'amount' => intval($amount * 100),
                            'currency' => 'CAD'
                        ],
                        'location_id' => $squareLocationId,
                        'customer_id' => $customerId,
                        'note' => "AI Subscription - Initial Payment ($billing)",
                        'autocomplete' => true
                    ];
                    $paymentResult = $squareApiCall('payments', 'POST', $paymentData);

                    if ($paymentResult['http_code'] >= 200 && $paymentResult['http_code'] < 300
                        && isset($paymentResult['response']['payment'])) {
                        $transactionId = $paymentResult['response']['payment']['id'];
                        $paymentToken = $cardId; // Store card ID for recurring billing
                    } else {
                        $errorDetail = $paymentResult['response']['errors'][0]['detail'] ?? 'Payment failed';
                        throw new Exception($errorDetail);
                    }
                }

            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Square error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Payment processing failed: ' . $e->getMessage()]);
                exit();
            }
            break;
    }

    // Check if updating existing subscription's payment method
    if ($existingSubscription && $isUpdatingPaymentMethod) {
        // Update existing subscription with new payment method and billing cycle
        $subscriptionId = $existingSubscription['subscription_id'];

        // Calculate new amount based on billing cycle
        $newAmount = ($billing === 'yearly') ? 50.00 : 5.00;

        // Determine the new end date:
        // - If subscription still valid (end_date > now) AND not charged: keep existing end_date
        // - If charged (new subscription period): calculate new end_date from now
        $existingEndDate = $existingSubscription['end_date'];
        $newEndDate = $existingEndDate; // Default: keep existing

        if (!$skipPaymentProcessing) {
            // User was charged, so start a new billing period
            $newEndDate = ($billing === 'yearly')
                ? date('Y-m-d H:i:s', strtotime('+1 year'))
                : date('Y-m-d H:i:s', strtotime('+1 month'));
        }

        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET payment_method = ?,
                payment_token = ?,
                stripe_customer_id = ?,
                transaction_id = ?,
                billing_cycle = ?,
                amount = ?,
                end_date = ?,
                status = 'active',
                auto_renew = 1,
                cancelled_at = NULL,
                updated_at = NOW()
            WHERE subscription_id = ?
        ");
        $stmt->execute([
            $paymentMethod,
            $paymentToken,
            $stripeCustomerId,
            $transactionId,
            $billing,
            $newAmount,
            $newEndDate,
            $subscriptionId
        ]);

        // Update with PayPal subscription ID if applicable
        if ($paypalSubscriptionId) {
            try {
                $stmt = $pdo->prepare("UPDATE ai_subscriptions SET paypal_subscription_id = ? WHERE subscription_id = ?");
                $stmt->execute([$paypalSubscriptionId, $subscriptionId]);
            } catch (PDOException $e) {
                error_log("Could not set paypal_subscription_id: " . $e->getMessage());
            }
        }

        $pdo->commit();

        // Build appropriate success message
        $formattedEndDate = date('F j, Y', strtotime($newEndDate));
        if ($skipPaymentProcessing) {
            $chargeMessage = "Your subscription has been updated. You will not be charged until $formattedEndDate.";
        } else {
            $chargeMessage = "Payment successful! Your subscription is now active until $formattedEndDate.";
        }

        echo json_encode([
            'success' => true,
            'subscription_id' => $subscriptionId,
            'message' => $chargeMessage,
            'next_billing_date' => $newEndDate
        ]);

    } else {
        // Create new subscription
        $stmt = $pdo->prepare("
            INSERT INTO ai_subscriptions (
                subscription_id, user_id, email, billing_cycle, amount, currency,
                start_date, end_date, status, payment_method, transaction_id,
                premium_license_key, discount_applied, credit_balance, original_credit,
                payment_token, stripe_customer_id, auto_renew, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, 'active', ?, ?,
                ?, ?, ?, ?,
                ?, ?, 1, NOW()
            )
        ");

        // For monthly with credit, store actual monthly price as amount (for display)
        $storedAmount = $isMonthlyWithCredit ? $monthlyPrice : $amount;

        $stmt->execute([
            $subscriptionId,
            $userId,
            $email,
            $billing,
            $storedAmount,
            $currency,
            $startDate,
            $endDate,
            $paymentMethod,
            $transactionId,
            $premiumLicenseKey ?: null,
            $hasDiscount ? 1 : 0,
            $creditBalance,
            $originalCredit,
            $paymentToken,
            $stripeCustomerId
        ]);

        // Update with PayPal subscription ID if applicable (column may not exist in older schema)
        if ($paypalSubscriptionId) {
            try {
                $stmt = $pdo->prepare("UPDATE ai_subscriptions SET paypal_subscription_id = ? WHERE subscription_id = ?");
                $stmt->execute([$paypalSubscriptionId, $subscriptionId]);
            } catch (PDOException $e) {
                // Column may not exist yet - log but don't fail
                error_log("Could not set paypal_subscription_id (column may not exist): " . $e->getMessage());
            }
        }

        // Log the payment transaction
        // For monthly with credit, log $0 payment with 'credit' payment type
        $paymentLogAmount = $isMonthlyWithCredit ? 0 : $amount;
        $paymentType = $isMonthlyWithCredit ? 'credit' : 'initial';

        $stmt = $pdo->prepare("
            INSERT INTO ai_subscription_payments (
                subscription_id, amount, currency, payment_method,
                transaction_id, status, payment_type, created_at
            ) VALUES (?, ?, ?, ?, ?, 'completed', ?, NOW())
        ");

        $stmt->execute([
            $subscriptionId,
            $paymentLogAmount,
            $currency,
            $paymentMethod,
            $transactionId,
            $paymentType
        ]);

        $pdo->commit();

        // Send receipt email (skip for monthly with credit - no charge was made)
        if (!$isMonthlyWithCredit) {
            try {
                send_ai_subscription_receipt($email, $subscriptionId, $billing, $amount, $endDate, $transactionId, $paymentMethod);
            } catch (Exception $e) {
                // Log email error but don't fail the transaction
                error_log("Failed to send AI subscription email: " . $e->getMessage());
            }
        }

        $responseMessage = $isMonthlyWithCredit
            ? 'Subscription created with $' . number_format($originalCredit, 2) . ' credit applied. Your first ' . floor($originalCredit / $monthlyPrice) . ' months are covered! Remaining credit: $' . number_format($creditBalance, 2)
            : 'Subscription created successfully';

        echo json_encode([
            'success' => true,
            'subscription_id' => $subscriptionId,
            'message' => $responseMessage,
            'credit_applied' => $isMonthlyWithCredit ? $originalCredit : 0
        ]);
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("AI Subscription Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to create subscription. Please contact support.'
    ]);
}
