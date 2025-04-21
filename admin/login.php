<?php
session_start();
require_once '../db_connect.php';
require_once '2fa.php';

// Check if user is already logged in
if ($_SESSION['role'] === 'admin') {
    header('Location: index.php');
    exit;
}

$error = '';
$show_2fa_form = false;

// Process 2FA verification
if (isset($_SESSION['awaiting_2fa']) && $_SESSION['awaiting_2fa'] === true) {
    $show_2fa_form = true;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
        $verification_code = $_POST['verification_code'] ?? '';

        if (empty($verification_code)) {
            $error = 'Please enter the verification code.';
        } else {
            $username = $_SESSION['temp_username'];
            $secret = get_2fa_secret($username);

            if (empty($secret)) {
                $error = "Authentication error: Unable to retrieve your 2FA secret.";
            } else if (verify_2fa_code($secret, $verification_code)) {
                // Code is valid, complete login
                $_SESSION['awaiting_2fa'] = false;
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                unset($_SESSION['temp_username']);

                // Update last login time
                $db = get_db_connection();
                $stmt = $db->prepare('UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE username = :username');
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $stmt->execute();

                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid verification code. Please try again.';
            }
        }
    }
}
// Process login form submission
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT * FROM admin_users WHERE LOWER(username) = LOWER(:username)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result && password_verify($password, $result['password_hash'])) {
            $actual_username = $result['username']; // Get actual username with correct case

            if (is_2fa_enabled($actual_username)) {
                // 2FA is enabled, show the verification form
                $_SESSION['awaiting_2fa'] = true;
                $_SESSION['temp_username'] = $actual_username;
                $show_2fa_form = true;
            } else {
                // No 2FA, complete login
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $actual_username;

                // Update last login time
                $stmt = $db->prepare('UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE username = :username');
                $stmt->bindValue(':username', $actual_username, SQLITE3_TEXT);
                $stmt->execute();

                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'Invalid username or password.';
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
    <title>Admin Login - Argo Sales Tracker</title>
    <link rel="stylesheet" href="login-style.css">
    <link rel="stylesheet" href="2fa-styles.css">
</head>

<body>
    <div class="login-container">
        <?php if ($show_2fa_form): ?>
            <div class="login-header">
                <h1>Two-Factor Authentication</h1>
                <p>Please enter the verification code from your authenticator app</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" id="verification-form">
                <div class="form-group">
                    <label for="verification_code">Verification Code</label>
                    <input type="number" id="verification_code" name="verification_code" class="verification-code" required autofocus placeholder="000000" min="0" max="999999">
                </div>

                <input type="hidden" name="verify_code" value="1">
                <button type="button" onclick="submitVerificationForm()" id="submit-button" class="btn">Verify</button>

                <div class="back-to-login">
                    <a href="users/users/logout.php">Cancel and return to login</a>
                </div>
            </form>
        <?php else: ?>
            <div class="login-header">
                <h1>Admin Login</h1>
                <p>Enter your credentials to access the license management system</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn">Login</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($show_2fa_form): ?>
        <script>
            // Simple function to submit the verification form
            function submitVerificationForm() {
                document.getElementById('verification-form').submit();
            }

            document.addEventListener('DOMContentLoaded', function() {
                var codeInput = document.getElementById('verification_code');

                if (codeInput) {
                    codeInput.addEventListener('input', function() {
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
                    codeInput.addEventListener('keydown', function(e) {
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
    <?php endif; ?>
</body>

</html>