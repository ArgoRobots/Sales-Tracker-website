<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AI Subscription Confirmed - Argo Sales Tracker">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>AI Subscription Confirmed - Argo Sales Tracker</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/link.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
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
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
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

        .subscription-details {
            background: white;
            border: 2px solid #8b5cf6;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: left;
        }

        .subscription-details h2 {
            color: #8b5cf6;
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

        .subscription-id-box {
            background: #f3e8ff;
            border: 1px solid #c4b5fd;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            text-align: center;
        }

        .subscription-id-box label {
            display: block;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .subscription-id {
            font-family: monospace;
            font-size: 20px;
            color: #7c3aed;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .copy-btn {
            background: #8b5cf6;
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
            background: #7c3aed;
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
            stroke: #8b5cf6;
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
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #8b5cf6;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid #8b5cf6;
            transition: background 0.3s ease;
        }

        .btn-secondary:hover {
            background: #f3e8ff;
        }
    </style>
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <?php
    $subscriptionId = isset($_GET['subscription_id']) ? htmlspecialchars($_GET['subscription_id']) : 'N/A';
    $email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
    ?>

    <div class="thank-you-container">
        <div class="success-icon">
            <svg viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1>You're All Set!</h1>
        <p class="subtitle">Your AI subscription is now active. Welcome to the future of sales tracking!</p>

        <div class="subscription-details">
            <h2>Subscription Details</h2>

            <div class="subscription-id-box">
                <label>Your Subscription ID</label>
                <div class="subscription-id" id="subscription-id"><?php echo $subscriptionId; ?></div>
                <button class="copy-btn" onclick="copySubscriptionId()">Copy to Clipboard</button>
            </div>

            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?php echo $email; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value" style="color: #059669;">Active</span>
            </div>
        </div>

        <div class="features-list">
            <div class="feature-item">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                </svg>
                <span>AI Receipt Scanning</span>
            </div>
            <div class="feature-item">
                <svg viewBox="0 0 24 24">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
                <span>Predictive Analysis</span>
            </div>
            <div class="feature-item">
                <svg viewBox="0 0 24 24">
                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707"></path>
                </svg>
                <span>AI Business Insights</span>
            </div>
            <div class="feature-item">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <span>Natural Language Search</span>
            </div>
        </div>

        <div class="activation-steps">
            <h3>How to Activate AI Features</h3>
            <ol>
                <li>Open <strong>Argo Sales Tracker</strong> on your computer</li>
                <li>Go to <strong>Settings > AI Features</strong></li>
                <li>Enter your <strong>Subscription ID</strong> shown above</li>
                <li>Click <strong>Activate</strong> and start using AI features!</li>
            </ol>
        </div>

        <div class="cta-buttons">
            <a href="../../download/index.php" class="btn-primary">Download Argo</a>
            <a href="../../documentation/index.php" class="btn-secondary">View Documentation</a>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        function copySubscriptionId() {
            const subscriptionId = document.getElementById('subscription-id').textContent;
            navigator.clipboard.writeText(subscriptionId).then(() => {
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
