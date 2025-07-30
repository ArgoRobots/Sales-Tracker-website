<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Thank You - Argo Sales Tracker">
    <meta name="keywords" content="sales tracker, business software, analytics">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Thank You - Argo Sales Tracker</title>

    <?php include 'resources/head/google-analytics.php'; ?>

    <!-- Event snippet for Purchase conversion page -->
    <script>
        // Wait for DOM to load, then get transaction ID from URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const transactionID = urlParams.get('license') || '';

            gtag('event', 'conversion', {
                'send_to': 'AW-17210317271/u-kiCL2u0_oaENezwo5A',
                'transaction_id': transactionID
            });
        });
    </script>

    <script src="main.js"></script>
    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="../../resources/scripts/ScrollToCenter.js"></script>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <section class="thank-you-container">
        <h1>Thank You for Your Purchase!</h1>
        <div class="thank-you-card">
            <div class="check-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <h2>Payment Successful</h2>
            <p>Your payment has been processed successfully. You now have full access to Argo Sales Tracker!</p>

            <div class="license-container">
                <h3>Your License Key</h3>
                <div class="license-key" id="license-key">
                    XXXX-XXXX-XXXX-XXXX
                    <button class="copy-btn" onclick="copyLicenseKey()">Copy</button>
                </div>
            </div>

            <p>We've also sent this license key to your email address.</p>

            <div class="next-steps">
                <h3>Next Steps</h3>

                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Open Argo Sales Tracker</h4>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Click the blue upgrade button on the top right</h4>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Enter your license key</h4>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Enjoy unlimited access to all premium features!</h4>
                    </div>
                </div>
            </div>
        </div>

        <a href="../../index.php" class="btn btn-blue">Return to Home</a>
    </section>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>