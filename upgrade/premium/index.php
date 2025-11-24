<?php
session_start();
require_once '../../db_connect.php';

// Check if logged-in user already has a license key
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_email = $_SESSION['email'] ?? '';

    if ($pdo !== null) {
        try {
            // Check by user_id or email
            $stmt = $pdo->prepare("SELECT license_key FROM license_keys WHERE user_id = ? OR LOWER(email) = LOWER(?) LIMIT 1");
            $stmt->execute([$user_id, $user_email]);
            if ($stmt->fetch()) {
                // User already has a license, redirect to profile
                header('Location: ../../community/users/profile.php');
                exit;
            }
        } catch (PDOException $e) {
            // Silently continue to premium page
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Argo">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="Get Argo Books Premium for $20 CAD. Lifetime access to unlimited products, Windows Hello security, and priority support. Choose your payment method.">
    <meta name="keywords"
        content="argo books premium, lifetime access, unlimited products, one time payment, business software">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Get Premium - Argo Books | $20 CAD">
    <meta property="og:description"
        content="Get Argo Books Premium for $20 CAD. Lifetime access to unlimited products, Windows Hello security, and priority support.">
    <meta property="og:url" content="https://argorobots.com/upgrade/premium.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Argo Books">

    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Get Premium - Argo Books | $20 CAD Lifetime Access</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/link.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <section class="premium-hero">
        <div class="container">
            <h1>Get Premium</h1>
            <p class="hero-subtitle">Lifetime access to all premium features</p>
        </div>
    </section>

    <section class="premium-content">
        <div class="container">
            <div class="premium-layout">
                <div class="premium-summary">
                    <div class="summary-card">
                        <h2>Order Summary</h2>
                        <div class="summary-item">
                            <span>Argo Books Premium</span>
                            <span class="item-price">$20.00 CAD</span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span>$20.00 CAD</span>
                        </div>
                        <div class="summary-note">
                            One-time payment. No recurring charges.
                        </div>
                    </div>

                    <div class="features-included">
                        <h3>What's Included</h3>
                        <ul>
                            <li>
                                <svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                                <span><strong>Unlimited products</strong> - No restrictions</span>
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                                <span>Windows Hello security</span>
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                                <span>Lifetime updates included</span>
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                                <span>Priority customer support</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="payment-selection">
                    <h2>Select Payment Method</h2>
                    <p>Choose how you'd like to pay</p>

                    <div class="payment-options">
                        <button class="payment-option" onclick="window.location.href='checkout/index.php?method=paypal'">
                            <img src="../../images/PayPal-logo.svg" alt="PayPal">
                            <span class="payment-desc">PayPal balance or linked card</span>
                        </button>

                        <button class="payment-option" onclick="window.location.href='checkout/index.php?method=stripe'">
                            <img src="../../images/Stripe-logo.svg" alt="Stripe">
                            <span class="payment-desc">Visa, Mastercard, Amex via Stripe</span>
                        </button>

                        <button class="payment-option" onclick="window.location.href='checkout/index.php?method=square'">
                            <img src="../../images/Square-logo.svg" alt="Square">
                            <span class="payment-desc">Visa, Mastercard, Amex via Square</span>
                        </button>
                    </div>

                    <div class="security-note">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0110 0v4"></path>
                        </svg>
                        <span>Secure payment processing. We never store your payment details.</span>
                    </div>
                </div>
            </div>

            <div class="guarantee-section">
                <div class="guarantee-badge">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <path d="M9 12l2 2 4-4"></path>
                    </svg>
                    <div>
                        <strong>30-Day Money Back Guarantee</strong>
                        <p>Not satisfied? Get a full refund within 30 days, no questions asked.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>
