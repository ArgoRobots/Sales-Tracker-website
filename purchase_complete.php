<?php
// This is a placeholder for after payment processing
// In production, this should only be accessible after successful payment

require_once 'license_functions.php';
require_once 'email_sender.php';

$license_key = '';
$error_message = '';
$email_status = null;

// Check if form was submitted (this is just for demo - in production would come from payment processor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    
    if ($email) {
        try {
            // Generate license key
            $license_key = create_license_key($email);
            
            // Send email with license key
            $email_status = send_license_email($email, $license_key);
            
            if (!$email_status) {
                $error_message = 'License key was generated but there was an issue sending the email. Please contact support.';
            }
        } catch (Exception $e) {
            $error_message = 'An error occurred: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Invalid email address.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Thank you for your purchase - Argo Sales Tracker">
    <meta name="author" content="Argo">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Purchase Complete - Argo Sales Tracker</title>
    
    <script src="../resources/scripts/main.js"></script>
    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    
    <link rel="stylesheet" href="../upgrade/style.css">
    <link rel="stylesheet" href="../resources/styles/customColors.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/footer/style.css">
    
    <style>
        .license-section {
            padding: 60px 0;
            text-align: center;
        }
        
        .license-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .license-key {
            font-family: monospace;
            font-size: 24px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px;
            margin: 20px 0;
            letter-spacing: 2px;
        }
        
        .copy-button {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .copy-button:hover {
            background: #1d4ed8;
        }
        
        .success-icon {
            color: #10b981;
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .email-sent {
            margin-top: 30px;
            color: #4b5563;
        }
        
        .email-error {
            margin-top: 20px;
            color: #ef4444;
            background: #fee2e2;
            border: 1px solid #ef4444;
            border-radius: 6px;
            padding: 10px;
        }
        
        .resend-form {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <script>
            $(function () {
                $("#includeHeader").load("../resources/header/index.html", function () {
                    adjustLinksAndImages("#includeHeader");
                });
            });
        </script>
        <div id="includeHeader"></div>
    </header>

    <section class="gradient-bg license-section">
        <div class="container">
            <div class="license-container">
                <?php if ($license_key): ?>
                    <div class="success-icon">✓</div>
                    <h1>Thank You for Your Purchase!</h1>
                    <p>Your Argo Sales Tracker license key has been generated:</p>
                    
                    <div class="license-key" id="license-key"><?php echo htmlspecialchars($license_key); ?></div>
                    
                    <button class="copy-button" onclick="copyLicenseKey()">Copy License Key</button>
                    
                    <?php if ($email_status): ?>
                        <p class="email-sent">We've also sent this license key to your email address.</p>
                    <?php else: ?>
                        <p class="email-error"><?php echo htmlspecialchars($error_message); ?></p>
                        <form class="resend-form" method="post" action="resend_email.php">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <input type="hidden" name="license_key" value="<?php echo htmlspecialchars($license_key); ?>">
                            <button type="submit" class="copy-button">Resend Email</button>
                        </form>
                    <?php endif; ?>
                    
                    <h3>What's Next?</h3>
                    <ol style="text-align: left; max-width: 400px; margin: 0 auto;">
                        <li>Open Argo Sales Tracker on your computer</li>
                        <li>Go to Settings > License</li>
                        <li>Enter your license key</li>
                        <li>Enjoy all premium features!</li>
                    </ol>
                <?php else: ?>
                    <?php if ($error_message): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
                    <?php endif; ?>
                    
                    <h2>Demo License Key Generation</h2>
                    <p>Please enter your email to generate a license key:</p>
                    
                    <form method="post" action="purchase_complete.php" style="max-width: 300px; margin: 0 auto;">
                        <div style="margin-bottom: 20px;">
                            <input type="email" name="email" required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                        <button type="submit" class="btn btn-blue">Generate License Key</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
    function copyLicenseKey() {
        const licenseKey = document.getElementById('license-key').innerText;
        navigator.clipboard.writeText(licenseKey).then(function() {
            alert('License key copied to clipboard!');
        }, function() {
            alert('Failed to copy license key. Please select and copy manually.');
        });
    }
    </script>

    <footer class="footer">
        <script>
            $(function () {
                $("#includeFooter").load("../resources/footer/index.html", function () {
                    adjustLinksAndImages("#includeFooter");
                });
            });
        </script>
        <div id="includeFooter"></div>
    </footer>
</body>
</html>