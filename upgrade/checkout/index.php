<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Checkout - Argo Sales Tracker">
    <meta name="keywords" content="sales tracker, business software, analytics">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Checkout - Argo Sales Tracker</title>

    <?php
    // Load environment variables
    require_once '../../db_connect.php';

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

    $square_environment = $is_production ? 'production' : 'sandbox';
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
    </script>

    <script src="main.js"></script>
    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="../../resources/scripts/ScrollToCenter.js"></script>
    <!-- All payment SDKs (PayPal, Stripe, and Square) are loaded conditionally in main.js to ensure better performance -->

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/link.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <section class="checkout-container">
        <h1>Complete Your Purchase For Argo Sales Tracker</h1>

        <div class="checkout-form">
            <h2>Payment Details</h2>

            <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="order-item">
                    <span>Argo Sales Tracker Premium</span>
                    <span>$20.00 CAD</span>
                </div>
                <div class="order-total">
                    <span>Total</span>
                    <span>$20.00 CAD</span>
                </div>
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
                            <div id="card-element"></div>
                        </div>
                        <div id="card-errors" role="alert" class="stripe-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <button type="submit" id="stripe-submit-btn" class="checkout-btn">
                        Pay $20.00 CAD
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