<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AI Subscription Checkout - Argo Books">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="../../../images/argo-logo/A-logo.ico">
    <title>AI Subscription Checkout - Argo Books</title>

    <?php
    session_start();
    require_once '../../../db_connect.php';
    require_once '../../../community/users/user_functions.php';

    // Require login to checkout
    require_login('/upgrade/ai/');

    $user_id = $_SESSION['user_id'];
    $user_email = $_SESSION['email'] ?? '';

    // Check if user already has an active subscription
    $existing_subscription = get_user_ai_subscription($user_id);
    $is_changing_method = isset($_GET['change_method']) && $_GET['change_method'] === '1';

    if ($existing_subscription && in_array($existing_subscription['status'], ['active', 'cancelled', 'payment_failed'])) {
        // User already has a subscription
        $has_valid_subscription = $existing_subscription['status'] === 'active' ||
            (in_array($existing_subscription['status'], ['cancelled', 'payment_failed']) && strtotime($existing_subscription['end_date']) > time());

        if ($has_valid_subscription && !$is_changing_method) {
            // Redirect to subscription page unless they're changing payment method
            header('Location: ../../../community/users/ai-subscription.php');
            exit;
        }
    }

    // Get environment-based keys
    $is_production = $_ENV['APP_ENV'] === 'production';

    $stripe_publishable_key = $is_production
        ? $_ENV['STRIPE_LIVE_PUBLISHABLE_KEY']
        : $_ENV['STRIPE_SANDBOX_PUBLISHABLE_KEY'];

    $paypal_client_id = $is_production
        ? $_ENV['PAYPAL_LIVE_CLIENT_ID']
        : $_ENV['PAYPAL_SANDBOX_CLIENT_ID'];

    // PayPal subscription plan IDs (create these in PayPal dashboard)
    $paypal_monthly_plan_id = $is_production
        ? ($_ENV['PAYPAL_LIVE_MONTHLY_PLAN_ID'] ?? '')
        : ($_ENV['PAYPAL_SANDBOX_MONTHLY_PLAN_ID'] ?? '');

    $paypal_yearly_plan_id = $is_production
        ? ($_ENV['PAYPAL_LIVE_YEARLY_PLAN_ID'] ?? '')
        : ($_ENV['PAYPAL_SANDBOX_YEARLY_PLAN_ID'] ?? '');

    $square_app_id = $is_production
        ? $_ENV['SQUARE_LIVE_APP_ID']
        : $_ENV['SQUARE_SANDBOX_APP_ID'];

    $square_location_id = $is_production
        ? $_ENV['SQUARE_LIVE_LOCATION_ID']
        : $_ENV['SQUARE_SANDBOX_LOCATION_ID'];

    // Get URL parameters
    $billing = isset($_GET['billing']) ? $_GET['billing'] : 'monthly';

    // Auto-detect license from database (never passed via URL for security)
    $hasDiscount = false;
    $licenseKey = '';

    if ($pdo !== null) {
        // Get user email - try session first, then fetch from database
        $lookup_email = $user_email;
        if (empty($lookup_email) && !empty($user_id)) {
            $user_data = get_user($user_id);
            if ($user_data && !empty($user_data['email'])) {
                $lookup_email = $user_data['email'];
            }
        }

        if (!empty($lookup_email)) {
            try {
                // Use LOWER() for case-insensitive email comparison
                $stmt = $pdo->prepare("SELECT license_key FROM license_keys WHERE LOWER(email) = LOWER(?) AND activated = 1 LIMIT 1");
                $stmt->execute([$lookup_email]);
                $userLicense = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($userLicense) {
                    $licenseKey = $userLicense['license_key'];
                    $hasDiscount = true;
                }
            } catch (PDOException $e) {
                // Silently fail - user just won't get auto-discount
                error_log("Auto-detect license error: " . $e->getMessage());
            }
        }
    }

    // Calculate prices
    $monthlyPrice = 5.00;
    $yearlyPrice = 50.00;
    $discount = 20.00;

    if ($billing === 'yearly') {
        $basePrice = $yearlyPrice;
        $finalPrice = $hasDiscount ? ($yearlyPrice - $discount) : $yearlyPrice;
        $billingPeriod = 'year';
    } else {
        $basePrice = $monthlyPrice;
        $finalPrice = $monthlyPrice;
        $billingPeriod = 'month';
    }

    // Verify license if discount is applied
    $discountVerified = false;
    if ($hasDiscount && !empty($licenseKey)) {
        try {
            $stmt = $pdo->prepare("SELECT id, activated FROM license_keys WHERE license_key = ? AND activated = 1");
            $stmt->execute([$licenseKey]);
            $discountVerified = $stmt->fetch() !== false;
        } catch (PDOException $e) {
            $discountVerified = false;
        }
    }

    // If discount claimed but not verified, remove discount
    if ($hasDiscount && !$discountVerified && $billing === 'yearly') {
        $hasDiscount = false;
        $finalPrice = $yearlyPrice;
    }
    ?>

    <!-- Payment processor keys -->
    <script>
        window.PAYMENT_CONFIG = {
            stripe: {
                publishableKey: '<?php echo $stripe_publishable_key; ?>'
            },
            paypal: {
                clientId: '<?php echo $paypal_client_id; ?>',
                monthlyPlanId: '<?php echo $paypal_monthly_plan_id; ?>',
                yearlyPlanId: '<?php echo $paypal_yearly_plan_id; ?>'
            },
            square: {
                appId: '<?php echo $square_app_id; ?>',
                locationId: '<?php echo $square_location_id; ?>'
            }
        };

        window.AI_SUBSCRIPTION = {
            billing: '<?php echo $billing; ?>',
            basePrice: <?php echo $basePrice; ?>,
            finalPrice: <?php echo $finalPrice; ?>,
            hasDiscount: <?php echo $hasDiscount ? 'true' : 'false'; ?>,
            discountAmount: <?php echo $discount; ?>,
            licenseKey: '<?php echo htmlspecialchars($licenseKey); ?>',
            userId: <?php echo $user_id; ?>,
            userEmail: '<?php echo htmlspecialchars($user_email); ?>',
            isUpdatingPaymentMethod: <?php echo $is_changing_method ? 'true' : 'false'; ?>,
            isMonthlyWithCredit: <?php echo ($hasDiscount && $billing === 'monthly') ? 'true' : 'false'; ?>,
            creditAmount: <?php echo ($hasDiscount && $billing === 'monthly') ? $discount : 0; ?>
        };
    </script>

    <script src="main.js"></script>
    <script src="../../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../premium/checkout/style.css">
    <link rel="stylesheet" href="../../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../../resources/styles/link.css">
    <link rel="stylesheet" href="../../../resources/header/style.css">
    <link rel="stylesheet" href="../../../resources/header/dark.css">
    <link rel="stylesheet" href="../../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <section class="checkout-container">
        <h1>Complete Your AI Subscription</h1>

        <div class="checkout-form">
            <h2>Payment Details</h2>

            <div class="order-summary ai-order-summary">
                <h3>Order Summary</h3>
                <div class="order-item">
                    <span>Argo AI Subscription (<?php echo ucfirst($billing); ?>)</span>
                    <span>$<?php echo number_format($basePrice, 2); ?> CAD</span>
                </div>
                <?php if ($hasDiscount && $billing === 'yearly'): ?>
                <div class="order-item discount-item">
                    <span>Premium User Discount</span>
                    <span class="discount-amount">-$<?php echo number_format($discount, 2); ?> CAD</span>
                </div>
                <?php endif; ?>
                <div class="order-total">
                    <span>Total</span>
                    <span>$<?php echo number_format($finalPrice, 2); ?> CAD/<?php echo $billingPeriod; ?></span>
                </div>
                <?php if ($hasDiscount && $billing === 'monthly'): ?>
                <div class="credit-notice">
                    <p><strong>$20 Credit Applied!</strong></p>
                    <p>Your first 4 months are covered by this credit. You won't be charged today - your card will be saved for when the credit is depleted.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="subscription-notice">
                <?php if ($hasDiscount && $billing === 'monthly'): ?>
                <p>This is a recurring subscription. Your $20 credit covers your first 4 months. You will be charged $<?php echo number_format($monthlyPrice, 2); ?> CAD/month starting month 5.</p>
                <?php elseif ($hasDiscount && $billing === 'yearly'): ?>
                <p>You will be charged $<?php echo number_format($finalPrice, 2); ?> CAD today (discounted), then $<?php echo number_format($yearlyPrice, 2); ?> CAD/year on each renewal.</p>
                <?php else: ?>
                <p>You will be charged $<?php echo number_format($finalPrice, 2); ?> CAD today, then $<?php echo number_format($finalPrice, 2); ?> CAD/<?php echo $billingPeriod; ?> on each renewal.</p>
                <?php endif; ?>
                <p>Cancel anytime from your account settings.</p>
            </div>

            <div id="stripe-container" style="display: none;">
                <form id="stripe-payment-form">
                    <div class="form-group">
                        <label for="card-holder">Cardholder Name</label>
                        <input type="text" id="card-holder" name="card-holder" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="card-element">Card Details</label>
                        <div id="card-element" class="form-control">
                            <!-- Stripe Elements Placeholder -->
                        </div>
                        <div id="card-errors" role="alert" class="stripe-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <button type="submit" id="stripe-submit-btn" class="checkout-btn ai-checkout-btn">
                        <?php if ($hasDiscount && $billing === 'monthly'): ?>
                        Subscribe - $0.00 Today (Credit Applied)
                        <?php else: ?>
                        Subscribe - $<?php echo number_format($finalPrice, 2); ?> CAD/<?php echo $billingPeriod; ?>
                        <?php endif; ?>
                    </button>
                </form>
            </div>

            <div id="square-container" style="display: none;">
                <!-- Square payment form will be inserted here by JavaScript -->
            </div>
        </div>
    </section>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>
