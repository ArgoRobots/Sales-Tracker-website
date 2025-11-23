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
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
    <link rel="stylesheet" href="ai-thank-you.css">
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
                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
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
            <a href="../../download/index.php" class="btn-primary">Download Argo Sales Tracker</a>
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
