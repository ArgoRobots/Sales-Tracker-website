<?php
/**
 * PayPal Subscription Plans Setup Script
 *
 * Run this script ONCE to create the subscription plans in PayPal.
 * After running, copy the Plan IDs to your .env file.
 *
 * Usage:
 *   CLI: php setup-paypal-plans.php
 *   Web: setup-paypal-plans.php?key=YOUR_CRON_SECRET
 *
 * This will create:
 *   1. A Product (Argo AI Subscription)
 *   2. A Monthly Plan ($5 CAD/month)
 *   3. A Yearly Plan ($50 CAD/year)
 */

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Security check
$isCli = php_sapi_name() === 'cli';
$isAuthenticated = isset($_GET['key']) && $_GET['key'] === ($_ENV['CRON_SECRET'] ?? '');

if (!$isCli && !$isAuthenticated) {
    http_response_code(403);
    die('Access denied. Use CLI or add ?key=YOUR_CRON_SECRET');
}

// Output as text
header('Content-Type: text/plain');

echo "=== PayPal Subscription Plans Setup ===\n\n";

// Configuration
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';
$environment = $isProduction ? 'PRODUCTION' : 'SANDBOX';

echo "Environment: $environment\n\n";

$clientId = $isProduction
    ? $_ENV['PAYPAL_LIVE_CLIENT_ID']
    : $_ENV['PAYPAL_SANDBOX_CLIENT_ID'];

$clientSecret = $isProduction
    ? $_ENV['PAYPAL_LIVE_CLIENT_SECRET']
    : $_ENV['PAYPAL_SANDBOX_CLIENT_SECRET'];

$baseUrl = $isProduction
    ? 'https://api-m.paypal.com'
    : 'https://api-m.sandbox.paypal.com';

if (empty($clientId) || empty($clientSecret)) {
    die("ERROR: PayPal credentials not configured in .env file\n");
}

// Get access token
echo "Getting access token...\n";
$accessToken = getAccessToken($baseUrl, $clientId, $clientSecret);
if (!$accessToken) {
    die("ERROR: Failed to get access token\n");
}
echo "Access token obtained.\n\n";

// Step 1: Create Product
echo "Step 1: Creating Product...\n";
$product = createProduct($baseUrl, $accessToken);
if (!$product) {
    die("ERROR: Failed to create product\n");
}
$productId = $product['id'];
echo "Product created: $productId\n\n";

// Step 2: Create Monthly Plan
echo "Step 2: Creating Monthly Plan ($5 CAD/month)...\n";
$monthlyPlan = createPlan($baseUrl, $accessToken, $productId, 'monthly', 5.00);
if (!$monthlyPlan) {
    die("ERROR: Failed to create monthly plan\n");
}
$monthlyPlanId = $monthlyPlan['id'];
echo "Monthly Plan created: $monthlyPlanId\n\n";

// Step 3: Create Yearly Plan
echo "Step 3: Creating Yearly Plan ($50 CAD/year)...\n";
$yearlyPlan = createPlan($baseUrl, $accessToken, $productId, 'yearly', 50.00);
if (!$yearlyPlan) {
    die("ERROR: Failed to create yearly plan\n");
}
$yearlyPlanId = $yearlyPlan['id'];
echo "Yearly Plan created: $yearlyPlanId\n\n";

// Output results
echo "===========================================\n";
echo "SUCCESS! Add these to your .env file:\n";
echo "===========================================\n\n";

if ($isProduction) {
    echo "PAYPAL_LIVE_MONTHLY_PLAN_ID=$monthlyPlanId\n";
    echo "PAYPAL_LIVE_YEARLY_PLAN_ID=$yearlyPlanId\n";
} else {
    echo "PAYPAL_SANDBOX_MONTHLY_PLAN_ID=$monthlyPlanId\n";
    echo "PAYPAL_SANDBOX_YEARLY_PLAN_ID=$yearlyPlanId\n";
}

echo "\n===========================================\n";
echo "Next Steps:\n";
echo "===========================================\n";
echo "1. Copy the Plan IDs above to your .env file\n";
echo "2. Set up the webhook in PayPal Developer Dashboard:\n";
echo "   - Go to: https://developer.paypal.com/dashboard/applications\n";
echo "   - Click your app\n";
echo "   - Scroll to 'Webhooks' and click 'Add Webhook'\n";
echo "   - URL: https://yourdomain.com/webhooks/paypal-subscription.php\n";
echo "   - Events: All BILLING.SUBSCRIPTION.* and PAYMENT.SALE.* events\n";
echo "3. Copy the Webhook ID to your .env as:\n";
if ($isProduction) {
    echo "   PAYPAL_LIVE_WEBHOOK_ID=your_webhook_id\n";
} else {
    echo "   PAYPAL_SANDBOX_WEBHOOK_ID=your_webhook_id\n";
}
echo "\n";

// === Helper Functions ===

function getAccessToken($baseUrl, $clientId, $clientSecret) {
    $ch = curl_init("$baseUrl/v1/oauth2/token");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_USERPWD => "$clientId:$clientSecret"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) {
        echo "Token Error: HTTP $httpCode\n$response\n";
        return null;
    }

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

function createProduct($baseUrl, $accessToken) {
    $productData = [
        'name' => 'Argo AI Subscription',
        'description' => 'Access to AI-powered features including receipt scanning, predictive analysis, and natural language search.',
        'type' => 'SERVICE',
        'category' => 'SOFTWARE',
        'home_url' => 'https://argorobots.com/upgrade/ai/'
    ];

    $ch = curl_init("$baseUrl/v1/catalogs/products");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($productData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Authorization: Bearer $accessToken",
            'PayPal-Request-Id: ' . uniqid('product_', true)
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 201) {
        echo "Product Error: HTTP $httpCode\n$response\n";
        return null;
    }

    return json_decode($response, true);
}

function createPlan($baseUrl, $accessToken, $productId, $interval, $price) {
    $intervalUnit = $interval === 'yearly' ? 'YEAR' : 'MONTH';
    $planName = $interval === 'yearly' ? 'Yearly' : 'Monthly';

    $planData = [
        'product_id' => $productId,
        'name' => "Argo AI - $planName",
        'description' => "Argo AI Subscription - $planName billing",
        'status' => 'ACTIVE',
        'billing_cycles' => [
            [
                'frequency' => [
                    'interval_unit' => $intervalUnit,
                    'interval_count' => 1
                ],
                'tenure_type' => 'REGULAR',
                'sequence' => 1,
                'total_cycles' => 0, // 0 = infinite
                'pricing_scheme' => [
                    'fixed_price' => [
                        'value' => number_format($price, 2, '.', ''),
                        'currency_code' => 'CAD'
                    ]
                ]
            ]
        ],
        'payment_preferences' => [
            'auto_bill_outstanding' => true,
            'setup_fee_failure_action' => 'CONTINUE',
            'payment_failure_threshold' => 3
        ]
    ];

    $ch = curl_init("$baseUrl/v1/billing/plans");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($planData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Authorization: Bearer $accessToken",
            'PayPal-Request-Id: ' . uniqid('plan_', true)
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 201) {
        echo "Plan Error: HTTP $httpCode\n$response\n";
        return null;
    }

    return json_decode($response, true);
}
