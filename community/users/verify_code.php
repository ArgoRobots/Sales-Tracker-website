<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';

$error = '';
$success = false;

// Check if temp_user_id is set
if (!isset($_SESSION['temp_user_id'])) {
    // Redirect to registration page if no temp user ID exists
    header('Location: register.php');
    exit;
}

// Store temp_user_id in a variable for easy access
$user_id = $_SESSION['temp_user_id'];

// Check if the user exists in the database
$db = get_db_connection();
$stmt = $db->prepare('SELECT id, verification_code FROM community_users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: register.php?error=user_not_found');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Combine the 6 digits into a single verification code
    $digits = [];
    for ($i = 1; $i <= 6; $i++) {
        $digits[] = isset($_POST["digit$i"]) ? trim($_POST["digit$i"]) : '';
    }
    $verification_code = implode('', $digits);

    // Basic validation
    if (strlen($verification_code) !== 6 || !ctype_digit($verification_code)) {
        $error = 'Please enter a valid 6-digit verification code';
    } else {
        // Get the current verification code from database
        $stmt = $db->prepare('SELECT verification_code FROM community_users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $db_verification = $result->fetch_assoc();
        $stmt->close();

        if (!$db_verification) {
            $error = 'User not found. Please register again.';
        } else {
            // Compare the codes directly
            if ($db_verification['verification_code'] === $verification_code) {
                // Update user as verified and clear verification code
                $stmt = $db->prepare('UPDATE community_users SET email_verified = 1, verification_code = NULL WHERE id = ?');
                $stmt->bind_param('i', $user_id);
                $update_result = $stmt->execute();
                $stmt->close();

                if ($update_result) {
                    // Get the full user details to populate the session
                    $stmt = $db->prepare('SELECT id, username, email, role, avatar FROM community_users WHERE id = ?');
                    $stmt->bind_param('i', $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $verified_user = $result->fetch_assoc();
                    $stmt->close();

                    if ($verified_user) {
                        // Update session to mark user as logged in
                        $_SESSION['user_id'] = $verified_user['id'];
                        $_SESSION['username'] = $verified_user['username'];
                        $_SESSION['email'] = $verified_user['email'];
                        $_SESSION['role'] = $verified_user['role'] ?? 'user';
                        $_SESSION['avatar'] = $verified_user['avatar'] ?? '';

                        // Add this line to set a flag for showing success message
                        $_SESSION['just_verified'] = true;

                        // Remove temporary user ID
                        unset($_SESSION['temp_user_id']);

                        // Generate and send license key
                        if (function_exists('generate_license_key') && function_exists('send_license_email')) {
                            $license_key = generate_license_key($verified_user['email']);
                            send_license_email($verified_user['email'], $license_key);
                        }

                        $success = true;
                    }
                } else {
                    $error = 'Failed to verify email. Please try again.';
                }
            } else {
                $error = 'Invalid verification code. Please try again.';
            }
        }
    }
}

// If verification is successful, redirect to profile
if ($success) {
    header('Location: profile.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Verify Your Email - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">

    <style>
        /* Verification code input styling */
        .verification-code-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 20px 0;
        }

        .verification-digit {
            width: 50px;
            height: 60px;
            font-size: 24px;
            text-align: center;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .verification-digit:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            outline: none;
        }

        .verification-hint {
            text-align: center;
            margin-top: 10px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h1>Verify Your Email</h1>
            <p class="auth-subtitle">Please enter the 6-digit verification code sent to your email</p>

            <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="verification-code-container">
                    <input type="text" maxlength="1" class="verification-digit" name="digit1" id="digit1"
                        pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="verification-digit" name="digit2" id="digit2"
                        pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="verification-digit" name="digit3" id="digit3"
                        pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="verification-digit" name="digit4" id="digit4"
                        pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="verification-digit" name="digit5" id="digit5"
                        pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="verification-digit" name="digit6" id="digit6"
                        pattern="[0-9]" inputmode="numeric" required>
                </div>

                <p class="verification-hint">Check your spam folder if you don't see it</p>

                <div class="form-actions">
                    <button type="submit" class="btn btn-blue btn-block">Verify Email</button>
                </div>

                <div class="auth-links">
                    <p>Didn't receive the code? <a href="resend_license.php">Resend code</a></p>
                    <p>Back to <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        // Handle verification code input behavior
        document.addEventListener('DOMContentLoaded', function () {
            const digitInputs = document.querySelectorAll('.verification-digit');

            // Focus the first input on page load
            digitInputs[0].focus();

            // Set up input behavior
            digitInputs.forEach((input, index) => {
                // Auto-focus next input on entry
                input.addEventListener('input', function (e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');

                    if (this.value.length === 1) {
                        // Move to next input if available
                        if (index < digitInputs.length - 1) {
                            digitInputs[index + 1].focus();
                        } else {
                            // If this is the last digit, submit the form
                            document.querySelector('.auth-form button[type="submit"]').focus();
                        }
                    }
                });

                // Handle backspace to go to previous input
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        digitInputs[index - 1].focus();
                    }
                });

                // Handle paste event for entire code
                input.addEventListener('paste', function (e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');

                    // If pasted content is 6 digits, fill all inputs
                    if (/^\d{6}$/.test(paste)) {
                        digitInputs.forEach((input, i) => {
                            input.value = paste[i];
                        });
                        // Focus the last input
                        digitInputs[5].focus();
                    }
                });
            });
        });
    </script>
</body>

</html>