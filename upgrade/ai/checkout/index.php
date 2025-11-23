<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AI Subscription Checkout - Argo Sales Tracker">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="../../../images/argo-logo/A-logo.ico">
    <title>AI Subscription Checkout - Argo Sales Tracker</title>

    <?php
    session_start();
    require_once '../../../db_connect.php';
    require_once '../../../community/users/user_functions.php';

    // Require login to checkout
    require_login('/upgrade/ai/');

    $user_id = $_SESSION['user_id'];
    $user_email = $_SESSION['email'] ?? '';

    // Get environment-based keys
    $is_production = $_ENV['APP_ENV'] === 'production';

    $stripe_publishable_key = $is_production
        ? $_ENV['STRIPE_LIVE_PUBLISHABLE_KEY']
        : $_ENV['STRIPE_SANDBOX_PUBLISHABLE_KEY'];

    $paypal_client_id = $is_production
        ? $_ENV['PAYPAL_LIVE_CLIENT_ID']
        : $_ENV['PAYPAL_SANDBOX_CLIENT_ID'];

    $square_app_id = $is_production
        ? $_ENV['SQUARE_LIVE_APP_ID']
        : $_ENV['SQUARE_SANDBOX_APP_ID'];

    $square_location_id = $is_production
        ? $_ENV['SQUARE_LIVE_LOCATION_ID']
        : $_ENV['SQUARE_SANDBOX_LOCATION_ID'];

    // Get URL parameters
    $billing = isset($_GET['billing']) ? $_GET['billing'] : 'monthly';
    $hasDiscount = isset($_GET['discount']) && $_GET['discount'] === '1';
    $licenseKey = isset($_GET['license']) ? $_GET['license'] : '';

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
                clientId: '<?php echo $paypal_client_id; ?>'
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
            userEmail: '<?php echo htmlspecialchars($user_email); ?>'
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
                    <p>Your $20 credit will be applied to your account and used for future billing.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="subscription-notice">
                <p>This is a recurring subscription. You will be charged $<?php echo number_format($billing === 'yearly' ? $yearlyPrice : $monthlyPrice, 2); ?> CAD/<?php echo $billingPeriod; ?> after the <?php echo $hasDiscount && $billing === 'yearly' ? 'discounted ' : ''; ?>first <?php echo $billingPeriod; ?>.</p>
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
                        Subscribe - $<?php echo number_format($finalPrice, 2); ?> CAD/<?php echo $billingPeriod; ?>
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
