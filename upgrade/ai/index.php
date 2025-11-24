<?php
session_start();
require_once '../../community/users/user_functions.php';

// Require login to access AI subscription page
require_login('/upgrade/ai/');

$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Check if user already has an active subscription
$existing_subscription = get_user_ai_subscription($user_id);
if ($existing_subscription && in_array($existing_subscription['status'], ['active', 'cancelled'])) {
    // User already has a subscription (active or cancelled but not expired)
    if ($existing_subscription['status'] === 'active' ||
        ($existing_subscription['status'] === 'cancelled' && strtotime($existing_subscription['end_date']) > time())) {
        header('Location: ../../community/users/ai-subscription.php');
        exit;
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
        content="Subscribe to Argo Books AI features. Get AI-powered receipt scanning, predictive sales analysis, and natural language search. $5/month or $50/year. Premium users save $20!">
    <meta name="keywords"
        content="argo ai features, ai receipt scanning, predictive sales analysis, ai business insights, sales tracker subscription">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="AI Subscription - Argo Books">
    <meta property="og:description"
        content="Subscribe to Argo Books AI features. Get AI-powered receipt scanning, predictive sales analysis, and natural language search. $5/month or $50/year.">
    <meta property="og:url" content="https://argorobots.com/upgrade/ai-subscription.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Argo Books">

    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>AI Subscription - Argo Books</title>

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

    <section class="ai-hero">
        <div class="container">
            <div class="ai-badge-large">AI-Powered Features</div>
            <h1>Unlock AI for Your Business</h1>
            <p>Transform your sales tracking with artificial intelligence. Get intelligent insights, automated receipt
                scanning, and predictive analytics.</p>
        </div>
    </section>

    <section class="ai-features-showcase">
        <div class="container">
            <h2>What's Included</h2>
            <div class="ai-features-grid">
                <div class="ai-feature-card">
                    <div class="ai-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"></path>
                        </svg>
                    </div>
                    <h3>AI Receipt Scanning</h3>
                    <p>Automatically extract data from receipts using advanced image recognition. Save hours of manual
                        data entry.</p>
                </div>
                <div class="ai-feature-card">
                    <div class="ai-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="7.5 4.21 12 6.81 16.5 4.21"></polyline>
                            <polyline points="7.5 19.79 7.5 14.6 3 12"></polyline>
                            <polyline points="21 12 16.5 14.6 16.5 19.79"></polyline>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <h3>Predictive Sales Analysis</h3>
                    <p>Forecast future trends based on your historical data. Make informed decisions with AI-powered
                        predictions.</p>
                </div>
                <div class="ai-feature-card">
                    <div class="ai-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3>AI Business Insights</h3>
                    <p>Receive intelligent recommendations and insights tailored to your business patterns and goals.</p>
                </div>
                <div class="ai-feature-card">
                    <div class="ai-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            <path d="M11 8v6M8 11h6"></path>
                        </svg>
                    </div>
                    <h3>Natural Language AI Search</h3>
                    <p>Ask questions in plain English like "Show me my best selling products last quarter" and get
                        instant answers.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="ai-pricing-section">
        <div class="container">
            <h2>Choose Your Plan</h2>
            <p class="pricing-subtitle">Select billing frequency and enter your license key if you're a Premium user</p>

            <div class="license-check-box">
                <h3>Premium User? Get $20 Off!</h3>
                <p>If you've purchased the $20 Premium version, enter your license key to receive a $20 discount.</p>
                <div class="license-input-group">
                    <input type="text" id="license-key" placeholder="Enter your license key">
                    <button type="button" id="verify-license" class="btn-verify">Verify License</button>
                </div>
                <div id="license-status" class="license-status"></div>
            </div>

            <div class="billing-toggle">
                <button type="button" class="billing-option active" data-billing="monthly">Monthly</button>
                <button type="button" class="billing-option" data-billing="yearly">Yearly (Save $10)</button>
            </div>

            <div class="pricing-display">
                <div class="price-box" id="price-display">
                    <div class="original-price" id="original-price" style="display: none;">
                        <span class="strikethrough">$50</span>
                    </div>
                    <div class="current-price">
                        <span class="currency">$</span>
                        <span class="amount" id="price-amount">5</span>
                        <span class="period" id="price-period">CAD/month</span>
                    </div>
                    <div class="discount-badge" id="discount-badge" style="display: none;">
                        $20 Premium Discount Applied!
                    </div>
                </div>
            </div>

            <div class="payment-methods">
                <h3>Select Payment Method</h3>
                <div class="payment-grid">
                    <button class="payment-btn" id="pay-paypal">
                        <img src="../../images/PayPal-logo.svg" alt="PayPal">
                        <span>Pay with PayPal</span>
                    </button>
                    <button class="payment-btn" id="pay-stripe">
                        <img class="Stripe" src="../../images/Stripe-logo.svg" alt="Stripe">
                        <span>Pay with Stripe</span>
                    </button>
                    <button class="payment-btn" id="pay-square">
                        <img class="Square" src="../../images/Square-logo.svg" alt="Square">
                        <span>Pay with Square</span>
                    </button>
                </div>
            </div>

            <div class="subscription-info">
                <p><strong>Subscription Terms:</strong></p>
                <ul>
                    <li>Cancel anytime - no long-term commitment</li>
                    <li>Premium discount applies to first year only</li>
                    <li>Automatic renewal unless cancelled</li>
                    <li>7-day free trial for new subscribers</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="ai-faq">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-grid">
                <details class="faq-card">
                    <summary>
                        <h3>Do I need the Premium version to use AI features?</h3>
                    </summary>
                    <p>No, the AI subscription is available to all users. However, Premium users receive a $20 discount
                        on their first year as a thank you for their support.</p>
                </details>
                <details class="faq-card">
                    <summary>
                        <h3>How does the $20 discount work?</h3>
                    </summary>
                    <p>If you've purchased the $20 Premium version, enter your license key when subscribing. The $20
                        discount will be applied to your first yearly subscription ($50 - $20 = $30 for the first year)
                        or as credit toward monthly payments.</p>
                </details>
                <details class="faq-card">
                    <summary>
                        <h3>Can I cancel my subscription?</h3>
                    </summary>
                    <p>Yes, you can cancel your subscription at any time. Your AI features will remain active until the
                        end of your current billing period.</p>
                </details>
                <details class="faq-card">
                    <summary>
                        <h3>Is there a free trial?</h3>
                    </summary>
                    <p>Yes! New subscribers get a 7-day free trial to experience all AI features before being charged.</p>
                </details>
                <details class="faq-card">
                    <summary>
                        <h3>What happens to my data if I cancel?</h3>
                    </summary>
                    <p>Your data remains safe in Argo Books. You'll just lose access to AI-specific features
                        until you resubscribe.</p>
                </details>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentBilling = 'monthly';
            let hasDiscount = false;
            let verifiedLicenseKey = null;

            const monthlyPrice = 5;
            const yearlyPrice = 50;
            const discount = 20;

            // Billing toggle
            document.querySelectorAll('.billing-option').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.billing-option').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentBilling = this.dataset.billing;
                    updatePriceDisplay();
                });
            });

            // License verification with rate limiting
            let lastVerifyAttempt = 0;
            let verifyAttempts = 0;
            const RATE_LIMIT_WINDOW = 60000; // 1 minute
            const MAX_ATTEMPTS = 5;
            const COOLDOWN_TIME = 3000; // 3 seconds between attempts

            document.getElementById('verify-license').addEventListener('click', async function() {
                const licenseKey = document.getElementById('license-key').value.trim();
                const statusEl = document.getElementById('license-status');
                const verifyBtn = this;
                const now = Date.now();

                // Reset attempt counter if window has passed
                if (now - lastVerifyAttempt > RATE_LIMIT_WINDOW) {
                    verifyAttempts = 0;
                }

                // Check rate limit
                if (verifyAttempts >= MAX_ATTEMPTS) {
                    const waitTime = Math.ceil((RATE_LIMIT_WINDOW - (now - lastVerifyAttempt)) / 1000);
                    statusEl.innerHTML = `<span class="error">Too many attempts. Please wait ${waitTime} seconds.</span>`;
                    return;
                }

                // Cooldown between attempts
                if (now - lastVerifyAttempt < COOLDOWN_TIME && lastVerifyAttempt > 0) {
                    statusEl.innerHTML = '<span class="error">Please wait a moment before trying again.</span>';
                    return;
                }

                if (!licenseKey) {
                    statusEl.innerHTML = '<span class="error">Please enter a license key</span>';
                    return;
                }

                // Update rate limit tracking
                lastVerifyAttempt = now;
                verifyAttempts++;

                // Disable button during verification
                verifyBtn.disabled = true;
                statusEl.innerHTML = '<span class="loading">Verifying...</span>';

                try {
                    const response = await fetch('../../check_license.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ license_key: licenseKey })
                    });

                    const data = await response.json();

                    if (data.valid) {
                        hasDiscount = true;
                        verifiedLicenseKey = licenseKey;
                        statusEl.innerHTML = '<span class="success">License verified! $20 discount applied.</span>';
                        updatePriceDisplay();
                        // Reset attempts on success
                        verifyAttempts = 0;
                    } else {
                        hasDiscount = false;
                        verifiedLicenseKey = null;
                        statusEl.innerHTML = '<span class="error">Invalid or unactivated license key</span>';
                        updatePriceDisplay();
                    }
                } catch (error) {
                    statusEl.innerHTML = '<span class="error">Error verifying license. Please try again.</span>';
                } finally {
                    verifyBtn.disabled = false;
                }
            });

            function updatePriceDisplay() {
                const priceAmount = document.getElementById('price-amount');
                const pricePeriod = document.getElementById('price-period');
                const originalPrice = document.getElementById('original-price');
                const discountBadge = document.getElementById('discount-badge');

                if (currentBilling === 'monthly') {
                    priceAmount.textContent = monthlyPrice;
                    pricePeriod.textContent = 'CAD/month';
                    originalPrice.style.display = 'none';

                    if (hasDiscount) {
                        discountBadge.style.display = 'block';
                        discountBadge.textContent = '$20 credit will be applied to your account!';
                    } else {
                        discountBadge.style.display = 'none';
                    }
                } else {
                    if (hasDiscount) {
                        priceAmount.textContent = yearlyPrice - discount;
                        originalPrice.style.display = 'block';
                        originalPrice.querySelector('.strikethrough').textContent = '$' + yearlyPrice;
                        discountBadge.style.display = 'block';
                        discountBadge.textContent = '$20 Premium Discount Applied!';
                    } else {
                        priceAmount.textContent = yearlyPrice;
                        originalPrice.style.display = 'none';
                        discountBadge.style.display = 'none';
                    }
                    pricePeriod.textContent = 'CAD/year';
                }
            }

            // Payment button handlers
            function getCheckoutUrl(method) {
                const params = new URLSearchParams({
                    method: method,
                    billing: currentBilling
                });

                // License/discount auto-detected on checkout page from database
                return 'checkout/?' + params.toString();
            }

            document.getElementById('pay-paypal').addEventListener('click', function() {
                window.location.href = getCheckoutUrl('paypal');
            });

            document.getElementById('pay-stripe').addEventListener('click', function() {
                window.location.href = getCheckoutUrl('stripe');
            });

            document.getElementById('pay-square').addEventListener('click', function() {
                window.location.href = getCheckoutUrl('square');
            });
        });
    </script>
</body>

</html>
