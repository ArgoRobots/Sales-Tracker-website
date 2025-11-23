<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Thank You - Argo Sales Tracker">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Purchase Confirmed - Argo Sales Tracker</title>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17210317271"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'AW-17210317271');
    </script>

    <!-- Event snippet for Purchase conversion page -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const transactionID = urlParams.get('transaction_id') || urlParams.get('license') || '';

            // Only fire conversion once per session to prevent duplicates on refresh
            const trackingKey = 'conversion_tracked_' + transactionID;
            if (transactionID && !sessionStorage.getItem(trackingKey)) {
                gtag('event', 'conversion', {
                    'send_to': 'AW-17210317271/u-kiCL2u0_oaENezwo5A',
                    'value': 20.00,
                    'currency': 'CAD',
                    'transaction_id': transactionID
                });

                // Mark as tracked for this browser session
                sessionStorage.setItem(trackingKey, 'true');
                console.log('Purchase conversion tracked:', transactionID);
            } else if (!transactionID) {
                console.warn('No transaction ID found in URL');
            } else {
                console.log('Conversion already tracked for this session');
            }
        });
    </script>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/link.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">

    <style>
        .thank-you-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 60px 20px;
            min-height: 80vh;
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }

        .success-icon svg {
            width: 50px;
            height: 50px;
            stroke: white;
            stroke-width: 3;
            fill: none;
        }

        h1 {
            color: #1f2937;
            margin-bottom: 16px;
            font-size: 32px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 18px;
            margin-bottom: 40px;
        }

        .purchase-details {
            background: white;
            border: 2px solid #3b82f6;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: left;
        }

        .purchase-details h2 {
            color: #3b82f6;
            font-size: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #6b7280;
        }

        .detail-value {
            color: #1f2937;
            font-weight: 600;
        }

        .license-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            text-align: center;
        }

        .license-box label {
            display: block;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .license-key {
            font-family: monospace;
            font-size: 20px;
            color: #1d4ed8;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .copy-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 12px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .copy-btn:hover {
            background: #1d4ed8;
        }

        .copy-btn.copied {
            background: #059669;
        }

        .activation-steps {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            text-align: left;
            margin-bottom: 30px;
        }

        .activation-steps h3 {
            color: #1f2937;
            margin-bottom: 16px;
        }

        .activation-steps ol {
            margin: 0;
            padding-left: 20px;
        }

        .activation-steps li {
            color: #4b5563;
            margin-bottom: 12px;
            line-height: 1.6;
        }

        .features-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 30px;
        }

        @media (max-width: 576px) {
            .features-list {
                grid-template-columns: 1fr;
            }
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .feature-item svg {
            width: 24px;
            height: 24px;
            stroke: #3b82f6;
            stroke-width: 2;
            fill: none;
            flex-shrink: 0;
        }

        .feature-item span {
            color: #374151;
            font-size: 14px;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #3b82f6;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid #3b82f6;
            transition: background 0.3s ease;
        }

        .btn-secondary:hover {
            background: #eff6ff;
        }
    </style>
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <?php
    $licenseKey = isset($_GET['license']) ? htmlspecialchars($_GET['license']) : 'N/A';
    $email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
    $transactionId = isset($_GET['transaction_id']) ? htmlspecialchars($_GET['transaction_id']) : '';
    ?>

    <div class="thank-you-container">
        <div class="success-icon">
            <svg viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1>You're All Set!</h1>
        <p class="subtitle">Your purchase is complete. Welcome to Argo Sales Tracker Premium!</p>

        <div class="purchase-details">
            <h2>Purchase Details</h2>

            <div class="license-box">
                <label>Your License Key</label>
                <div class="license-key" id="license-key"><?php echo $licenseKey; ?></div>
                <button class="copy-btn" onclick="copyLicenseKey()">Copy to Clipboard</button>
            </div>

            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?php echo $email; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Product</span>
                <span class="detail-value">Argo Sales Tracker Premium</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">License Type</span>
                <span class="detail-value">Lifetime Access</span>
            </div>
        </div>

        <div class="features-list">
            <div class="feature-item">
                <svg viewBox="0 0 24 24">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
                <span>Unlimited Products</span>
            </div>
            <div class="feature-item">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0110 0v4"></path>
                </svg>
                <span>Windows Hello Security</span>
            </div>
            <div class="feature-item">
                <svg viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <span>Lifetime Updates</span>
            </div>
            <div class="feature-item">
                <svg viewBox="0 0 24 24">
                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path>
                </svg>
                <span>Priority Support</span>
            </div>
        </div>

        <div class="activation-steps">
            <h3>How to Activate Premium</h3>
            <ol>
                <li>Open <strong>Argo Sales Tracker</strong> on your computer</li>
                <li>Click the blue <strong>Upgrade</strong> button in the top right corner</li>
                <li>Enter your <strong>License Key</strong> shown above</li>
                <li>Click <strong>Activate</strong> and enjoy all premium features!</li>
            </ol>
        </div>

        <div class="cta-buttons">
            <a href="../../download/index.php" class="btn-primary">Download Argo Sales Tracker</a>
            <a href="../../documentation/index.php" class="btn-secondary">View Documentation</a>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        function copyLicenseKey() {
            const licenseKey = document.getElementById('license-key').textContent;
            navigator.clipboard.writeText(licenseKey).then(() => {
                const btn = document.querySelector('.copy-btn');
                btn.textContent = 'Copied!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = 'Copy to Clipboard';
                    btn.classList.remove('copied');
                }, 2000);
            });
        }
    </script>
</body>

</html>
