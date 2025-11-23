<?php
/**
 * AI Subscription Payment Processor
 * Handles subscription creation for AI features
 */

header('Content-Type: application/json');

require_once '../../db_connect.php';
require_once '../../email_sender.php';

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

    // Determine transaction ID based on payment method
    $transactionId = '';
    switch ($paymentMethod) {
        case 'paypal':
            $transactionId = $input['orderID'] ?? '';
            break;
        case 'stripe':
            $transactionId = $input['payment_method_id'] ?? '';
            break;
        case 'square':
            $transactionId = $input['source_id'] ?? '';
            break;
    }

    // Insert subscription record
    $stmt = $pdo->prepare("
        INSERT INTO ai_subscriptions (
            subscription_id, user_id, email, billing_cycle, amount, currency,
            start_date, end_date, status, payment_method, transaction_id,
            premium_license_key, discount_applied, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, 'active', ?, ?,
            ?, ?, NOW()
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
        $hasDiscount ? 1 : 0
    ]);

    // Log the payment transaction
    $stmt = $pdo->prepare("
        INSERT INTO ai_subscription_payments (
            subscription_id, amount, currency, payment_method,
            transaction_id, status, created_at
        ) VALUES (?, ?, ?, ?, ?, 'completed', NOW())
    ");

    $stmt->execute([
        $subscriptionId,
        $amount,
        $currency,
        $paymentMethod,
        $transactionId
    ]);

    $pdo->commit();

    // Send confirmation email
    try {
        sendAISubscriptionEmail($email, $subscriptionId, $billing, $amount, $endDate);
    } catch (Exception $e) {
        // Log email error but don't fail the transaction
        error_log("Failed to send AI subscription email: " . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'subscription_id' => $subscriptionId,
        'message' => 'Subscription created successfully'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("AI Subscription Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to create subscription. Please contact support.'
    ]);
}

/**
 * Send AI subscription confirmation email
 */
function sendAISubscriptionEmail($email, $subscriptionId, $billing, $amount, $endDate) {
    $subject = "Your Argo AI Subscription is Active!";

    $billingText = $billing === 'yearly' ? 'yearly' : 'monthly';
    $renewalDate = date('F j, Y', strtotime($endDate));

    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 30px; text-align: center; border-radius: 12px 12px 0 0; }
            .content { background: #f8fafc; padding: 30px; border-radius: 0 0 12px 12px; }
            .subscription-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb; }
            .feature-list { list-style: none; padding: 0; }
            .feature-list li { padding: 8px 0; padding-left: 24px; position: relative; }
            .feature-list li:before { content: '✓'; position: absolute; left: 0; color: #8b5cf6; font-weight: bold; }
            .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to Argo AI!</h1>
            </div>
            <div class='content'>
                <p>Hi there,</p>
                <p>Thank you for subscribing to Argo AI features! Your subscription is now active.</p>

                <div class='subscription-box'>
                    <h3>Subscription Details</h3>
                    <p><strong>Subscription ID:</strong> {$subscriptionId}</p>
                    <p><strong>Plan:</strong> AI Features ({$billingText})</p>
                    <p><strong>Amount:</strong> \${$amount} CAD/{$billingText}</p>
                    <p><strong>Next Renewal:</strong> {$renewalDate}</p>
                </div>

                <h3>What's Included:</h3>
                <ul class='feature-list'>
                    <li>AI-powered receipt scanning</li>
                    <li>Predictive sales analysis</li>
                    <li>AI business insights</li>
                    <li>Natural language AI search</li>
                </ul>

                <p>To activate AI features in the app, go to <strong>Settings > AI Features</strong> and enter your subscription ID.</p>

                <p>If you have any questions, feel free to <a href='https://argorobots.com/contact-us/'>contact our support team</a>.</p>

                <div class='footer'>
                    <p>Thank you for choosing Argo Sales Tracker!</p>
                    <p><a href='https://argorobots.com'>argorobots.com</a></p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    // Use existing email function if available, otherwise use mail()
    if (function_exists('sendEmail')) {
        sendEmail($email, $subject, $body);
    } else {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Argo Sales Tracker <noreply@argorobots.com>\r\n";
        mail($email, $subject, $body, $headers);
    }
}
