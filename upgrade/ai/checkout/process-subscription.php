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

// Get environment configuration
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

if (empty($email) || $amount <= 0 || $userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields or invalid user']);
    exit();
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

try {
    $stmt = $pdo->prepare("
        SELECT * FROM ai_subscriptions
        WHERE user_id = ? AND status IN ('active', 'cancelled', 'payment_failed')
        AND end_date > NOW()
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$userId]);
    $existingSubscription = $stmt->fetch(PDO::FETCH_ASSOC);
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

    // Check if this is a PayPal subscription
    $paypalSubscriptionId = null;

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

            // Initialize Square
            $squareAccessToken = $isProduction
                ? $_ENV['SQUARE_LIVE_ACCESS_TOKEN']
                : $_ENV['SQUARE_SANDBOX_ACCESS_TOKEN'];
            $squareEnvironment = $isProduction ? 'production' : 'sandbox';
            $squareLocationId = $isProduction
                ? $_ENV['SQUARE_LIVE_LOCATION_ID']
                : $_ENV['SQUARE_SANDBOX_LOCATION_ID'];

            try {
                $client = new \Square\SquareClient([
                    'accessToken' => $squareAccessToken,
                    'environment' => $squareEnvironment
                ]);

                // Create customer for recurring billing
                $customerApi = $client->getCustomersApi();
                $searchRequest = new \Square\Models\SearchCustomersRequest();
                $query = new \Square\Models\CustomerQuery();
                $filter = new \Square\Models\CustomerFilter();
                $emailFilter = new \Square\Models\CustomerTextFilter();
                $emailFilter->setExact($email);
                $filter->setEmailAddress($emailFilter);
                $query->setFilter($filter);
                $searchRequest->setQuery($query);

                $searchResponse = $customerApi->searchCustomers($searchRequest);
                $customerId = null;

                if ($searchResponse->isSuccess() && count($searchResponse->getResult()->getCustomers() ?? []) > 0) {
                    $customerId = $searchResponse->getResult()->getCustomers()[0]->getId();
                } else {
                    // Create new customer
                    $customerRequest = new \Square\Models\CreateCustomerRequest();
                    $customerRequest->setEmailAddress($email);
                    $customerRequest->setReferenceId("user_$userId");
                    $createResponse = $customerApi->createCustomer($customerRequest);

                    if ($createResponse->isSuccess()) {
                        $customerId = $createResponse->getResult()->getCustomer()->getId();
                    }
                }

                // Create card on file for recurring billing
                $cardsApi = $client->getCardsApi();
                $cardRequest = new \Square\Models\CreateCardRequest(
                    uniqid('card_', true),
                    $sourceId,
                    new \Square\Models\Card()
                );
                $cardRequest->getCard()->setCustomerId($customerId);

                $cardResponse = $cardsApi->createCard($cardRequest);
                $cardId = null;

                if ($cardResponse->isSuccess()) {
                    $cardId = $cardResponse->getResult()->getCard()->getId();
                }

                // Process initial payment
                $paymentsApi = $client->getPaymentsApi();
                $amountMoney = new \Square\Models\Money();
                $amountMoney->setAmount(intval($amount * 100));
                $amountMoney->setCurrency('CAD');

                $paymentRequest = new \Square\Models\CreatePaymentRequest(
                    $cardId ?? $sourceId,
                    uniqid('payment_', true)
                );
                $paymentRequest->setAmountMoney($amountMoney);
                $paymentRequest->setLocationId($squareLocationId);
                $paymentRequest->setCustomerId($customerId);
                $paymentRequest->setNote("AI Subscription - Initial Payment ($billing)");
                $paymentRequest->setAutocomplete(true);

                $paymentResponse = $paymentsApi->createPayment($paymentRequest);

                if ($paymentResponse->isSuccess()) {
                    $payment = $paymentResponse->getResult()->getPayment();
                    $transactionId = $payment->getId();
                    $paymentToken = $cardId; // Store card ID for recurring billing
                } else {
                    $errors = $paymentResponse->getErrors();
                    throw new Exception($errors[0]->getDetail() ?? 'Payment failed');
                }

            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Square error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Payment processing failed']);
                exit();
            }
            break;
    }

    // Check if updating existing subscription's payment method
    if ($existingSubscription && $isUpdatingPaymentMethod) {
        // Update existing subscription with new payment method
        $subscriptionId = $existingSubscription['subscription_id'];

        $stmt = $pdo->prepare("
            UPDATE ai_subscriptions
            SET payment_method = ?,
                payment_token = ?,
                transaction_id = ?,
                status = 'active',
                auto_renew = 1,
                cancelled_at = NULL,
                updated_at = NOW()
            WHERE subscription_id = ?
        ");
        $stmt->execute([
            $paymentMethod,
            $paymentToken,
            $transactionId,
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

        echo json_encode([
            'success' => true,
            'subscription_id' => $subscriptionId,
            'message' => 'Payment method updated successfully'
        ]);

    } else {
        // Create new subscription
        $stmt = $pdo->prepare("
            INSERT INTO ai_subscriptions (
                subscription_id, user_id, email, billing_cycle, amount, currency,
                start_date, end_date, status, payment_method, transaction_id,
                premium_license_key, discount_applied, payment_token, auto_renew, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, 'active', ?, ?,
                ?, ?, ?, 1, NOW()
            )
        ");

        $stmt->execute([
            $subscriptionId,
            $userId,
            $email,
            $billing,
            $amount,
            $currency,
            $startDate,
            $endDate,
            $paymentMethod,
            $transactionId,
            $premiumLicenseKey ?: null,
            $hasDiscount ? 1 : 0,
            $paymentToken
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
        $stmt = $pdo->prepare("
            INSERT INTO ai_subscription_payments (
                subscription_id, amount, currency, payment_method,
                transaction_id, status, payment_type, created_at
            ) VALUES (?, ?, ?, ?, ?, 'completed', 'initial', NOW())
        ");

        $stmt->execute([
            $subscriptionId,
            $amount,
            $currency,
            $paymentMethod,
            $transactionId
        ]);

        $pdo->commit();

        // Send receipt email
        try {
            send_ai_subscription_receipt($email, $subscriptionId, $billing, $amount, $endDate, $transactionId, $paymentMethod);
        } catch (Exception $e) {
            // Log email error but don't fail the transaction
            error_log("Failed to send AI subscription email: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'subscription_id' => $subscriptionId,
            'message' => 'Subscription created successfully'
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
