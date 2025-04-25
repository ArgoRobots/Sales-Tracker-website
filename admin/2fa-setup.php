<?php
session_start();
require_once '../db_connect.php';
require_once '2fa.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['admin_username'];
$error = '';
$success = '';
$is_enabled = is_2fa_enabled($username);
$new_secret = '';
$qr_code_data = '';

// Check if 2FA is already enabled or we're setting it up
if (!$is_enabled && isset($_GET['setup'])) {
    // Only generate a new secret if one doesn't already exist in session
    if (!isset($_SESSION['temp_2fa_secret'])) {
        $new_secret = generate_2fa_secret();
        $_SESSION['temp_2fa_secret'] = $new_secret;
    } else {
        // Use existing secret from session
        $new_secret = $_SESSION['temp_2fa_secret'];
    }

    $qr_code_data = get_qr_code_url($username, $new_secret, 'Argo Sales Tracker Admin');
}

// Handle disabling of 2FA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['disable_2fa'])) {
    if (disable_2fa($username)) {
        $success = 'Two-factor authentication has been disabled.';
        $is_enabled = false;
    } else {
        $error = 'Failed to disable two-factor authentication.';
    }
}

// Handle activation of 2FA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enable_2fa'])) {
    $verification_code = $_POST['verification_code'] ?? '';
    $secret = $_SESSION['temp_2fa_secret'] ?? '';

    if (empty($secret)) {
        $error = 'Session expired or invalid. Please try again.';
    } else {
        // Check verification code
        if (verify_2fa_code($secret, $verification_code)) {
            if (save_2fa_secret($username, $secret)) {
                $success = 'Two-factor authentication successfully enabled!';
                $is_enabled = true;
                unset($_SESSION['temp_2fa_secret']);
            } else {
                $error = 'Failed to save authentication settings.';
            }
        } else {
            $error = 'Invalid verification code. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Argo Sales Tracker - 2FA</title>

    <script src="../resources/notifications/notifications.js" defer></script>

    <link rel="stylesheet" href="index-style.css">
    <link rel="stylesheet" href="2fa-setup-style.css">
    <link rel="stylesheet" href="2fa-styles.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/notifications/notifications.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Two-Factor Authentication Setup</h1>
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="setup-container">
            <?php if ($is_enabled): ?>
                <h2>Two-Factor Authentication is Enabled</h2>
                <p>Your account is currently protected with two-factor authentication.</p>

                <form method="post" onsubmit="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.');">
                    <button type="submit" name="disable_2fa" class="btn btn-red">Disable Two-Factor Authentication</button>
                </form>
            <?php elseif (isset($_GET['setup'])): ?>
                <h2>Set Up Two-Factor Authentication</h2>

                <ol class="steps">
                    <li>Download and install Google Authenticator app on your mobile device
                        <ul>
                            <li><a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Android - Google Play Store</a></li>
                            <li><a href="https://apps.apple.com/us/app/google-authenticator/id388497605" target="_blank">iOS - App Store</a></li>
                        </ul>
                    </li>
                    <li>Scan the QR code below with the app</li>
                    <li>Enter the 6-digit verification code from the app</li>
                </ol>

                <div class="qr-container">
                    <div id="qr-code-container" style="margin: 0 auto; width: 200px; height: 200px;"></div>

                    <div class="manual-entry">
                        <h3>Manual Entry</h3>
                        <p>If you can't scan the QR code, enter this key manually in your authenticator app:</p>
                        <div class="secret-key"><?php echo htmlspecialchars($new_secret); ?></div>
                        <p><small>In Google Authenticator: tap + button → Enter a setup key → Enter the key above and set "Argo Sales Tracker Admin" as the account name</small></p>
                    </div>
                </div>

                <form method="post" class="verification-form" id="verification-form">
                    <div class="verification-heading">Enter the 6-digit code from your authenticator app</div>

                    <input type="number" id="verification_code" name="verification_code" class="verification-input" required autofocus placeholder="000000" min="0" max="999999">

                    <div class="nav-buttons">
                        <a href="2fa-setup.php" class="btn">Cancel</a>
                        <button type="button" onclick="submitVerificationForm()" id="verify-button" class="btn btn-green">Verify and Enable</button>
                        <input type="hidden" name="enable_2fa" value="1">
                    </div>
                </form>
            <?php else: ?>
                <h2>Enhance Your Account Security</h2>
                <p>Two-factor authentication adds an extra layer of security to your account. After enabling, you'll need both your password and a verification code from your mobile device to sign in.</p>

                <a href="2fa-setup.php?setup=1" class="btn">Set Up Two-Factor Authentication</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function submitVerificationForm() {
            document.getElementById('verification-form').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // QR Code generation
            const qrContainer = document.getElementById('qr-code-container');
            const otpauthUrl = "";

            <?php if (!empty($qr_code_data)): ?>
                otpauthUrl = <?php echo json_encode($qr_code_data); ?>;
            <?php endif; ?>

            if (qrContainer && otpauthUrl) {
                try {
                    new QRCode(qrContainer, {
                        text: otpauthUrl,
                        width: 200,
                        height: 200,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                } catch (e) {
                    qrContainer.innerHTML = "<p>QR code generation failed. Please use manual entry.</p>";
                }
            }

            // Auto-submit when 6 digits are entered
            const verificationInput = document.getElementById('verification_code');
            if (verificationInput) {
                verificationInput.addEventListener('input', function() {
                    // Force numeric only
                    this.value = this.value.replace(/[^0-9]/g, '');

                    // Auto-submit on 6 digits
                    if (this.value.length === 6) {
                        // Add a small delay so user sees the 6th digit
                        setTimeout(function() {
                            submitVerificationForm();
                        }, 300);
                    }
                });

                // Prevent arrow keys
                verificationInput.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                        e.preventDefault();
                    }
                    // Also submit on Enter key when 6 digits entered
                    if (e.key === 'Enter' && this.value.length === 6) {
                        e.preventDefault();
                        submitVerificationForm();
                    }
                });
            }
        });
    </script>
</body>

</html>